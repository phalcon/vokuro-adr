# Testing

Two PHPUnit suites, both run through [`phalcon/talon`](https://github.com/phalcon/talon). `tests/bootstrap.php` loads `tests/.env.test` and boots Talon; real environment variables win over the file, so the same suites run in Docker and in CI unchanged.

| Suite | Directory | Config | Needs a database |
| --- | --- | --- | --- |
| `unit` | `tests/Unit` | `resources/phpunit.xml.dist` | no |
| `integration` | `tests/Integration` | `resources/phpunit.integration.xml` | yes |

```bash
docker compose exec app composer test-unit          # fast, no database
docker compose exec app composer test-integration   # creates + migrates vokuro_adr_test, then runs
docker compose exec app composer test-all           # both
```

## Unit suite - fakes

The domains, actions, middleware, responders, forms, and the non-database infrastructure are tested in isolation. Every port has a hand-written in-memory **fake** under `tests/Support/Fake` - never a mock or an anonymous double. A fake holds real state, offers a `seed()` for preconditions, and records writes so a test can assert on them:

```php
$users = (new FakeUserRepository())->seed(
    new User(7, 'Sarah', 's@x.dev', 'hash', 2, 'Users', true, false, false, false)
);

$payload = (new Login($users, new FakeSuccessLoginRepository(), $failed, new Security()))(
    new Input(['email' => 's@x.dev', 'password' => 'secret'])
);

$this->assertSame(Status::AUTHENTICATED, $payload->getStatus());
```

Actions are constructed with the fakes plus a shared `FakeRenderer` inside real responders, and invoked with a real `Phalcon\Http\Request` (superglobals and route attributes set) through `AbstractActionTestCase`. A test asserts on the response status, the redirect, or the template and data recorded by the renderer. CSRF is a `FakeCsrf` whose verdict the test sets.

Framework types too big to fake (`AttributeRequest` extends the whole request contract) use the real object rather than a double.

## Integration suite - a real database

The eight repositories run against a real, **dedicated** `vokuro_adr_test` database - never the development database. `AbstractIntegrationTestCase` opens a `Phalcon\DataMapper` connection to it (the name is hardcoded, so the suite can never touch the dev data) and offers two helpers:

* `clean(string ...$tables)` - `SET FOREIGN_KEY_CHECKS=0`, `TRUNCATE` each table, then back on. TRUNCATE resets `AUTO_INCREMENT`, so inserted rows get predictable ids. No transactions.
* `insert(string $table, array $row)` - seed a row and return its id.

Each test truncates the tables it uses and seeds exactly the rows it needs, so tests never pollute one another:

```php
protected function setUp(): void
{
    parent::setUp();
    $this->clean('profiles');
    $this->repository = new ProfileRepository($this->connection, $this->queryFactory);
}
```

`AppFront` is covered here too, by a process-isolated functional boot that resolves every provider and dispatches the home route.

`composer test-integration` creates `vokuro_adr_test` if absent, migrates it with the existing Phinx migrations, then runs the suite.

## Coverage

Each suite emits a Clover report and a serialized `.cov`; `phpcov` merges the two into one report the coverage gate reads:

```bash
docker compose exec app composer test-all-coverage
# -> tests/_output/coverage.xml (both suites, merged)
```

The unit suite covers everything except the repositories and `AppFront`; the integration suite covers those. Merged, the project sits at full coverage. [octocov](https://github.com/k1LoW/octocov) gates on the merged report in CI, and SonarQube reads the same file.

> octocov resolves the paths in its config relative to the config file's own directory. Because the configs live in `resources/`, the paths in `resources/.octocov.yml` are prefixed with `../`.

## Test environment

`tests/.env.test` supplies the database and mail settings. The database host and credentials are read `getenv()` first, then `$_ENV`, so:

* **In the container**, the Compose `env_file` puts `DB_HOST=mysql` in the process environment and `getenv()` answers with it.
* **In CI**, nothing is exported to the shell, so the values come from `tests/.env.test` (`DB_HOST=127.0.0.1`) via `$_ENV`.

The only non-local secret is `SONAR_TOKEN`, a GitHub Actions secret used by the coverage job's SonarQube scan.
