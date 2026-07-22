# Vökuró ADR

[![Latest Version][packagist-version-badge]][packagist-version-link]
[![PHP Version][php-version-badge]][packagist-version-link]
[![Total Downloads][packagist-downloads-badge]][packagist-downloads-link]
[![License][license-badge]][license-link]

[![Vokuro ADR CI][vokuro-ci-badge]][vokuro-ci-link]
[![Quality Gate Status][sonar-quality-badge]][sonar-link]
[![Coverage][sonar-coverage-badge]][sonar-link]
[![PDS Skeleton][pds-skeleton-badge]][pds-skeleton-link]

[![Discord][discord-badge]][discord-link]
[![Contributors][contributors-badge]][contributors-link]
[![OpenCollective Backers][oc-backers-badge]][oc-backers-link]
[![OpenCollective Sponsors][oc-sponsors-badge]][oc-sponsors-link]

Vökuró ADR is the [Action-Domain-Responder](https://pmjones.io/adr/) port of the Vökuró sample application for the [Phalcon Framework](https://github.com/phalcon/cphalcon). It showcases authentication, ACL-based permissions, user and profile management, forms, and mailing - rebuilt as ports and adapters, with no `Phalcon\Di\DiInterface` in the delivery ring.

It runs on **Phalcon v6** (the `phalcon/phalcon` package). Phalcon v5 (the C extension) has no ADR stack yet, so a v5 build waits on a cphalcon release that ships it.

## How it is structured

A request flows **Action → Domain → Responder**:

* **Action** (`src/Action`) - one class per route. It reads the request, calls a domain, and hands the resulting payload to a responder. The responder it type-hints selects the layout.
* **Domain** (`src/Domain`) - the use cases (sign in, register, save a profile) with their entities, value objects, and collections. A domain returns a `Phalcon\ADR` payload and knows nothing about HTTP.
* **Responder** (`src/Responder`) - turns a payload into a response: HTML in a layout, a redirect, or JSON for an error.

Everything the actions and domains depend on is a **port** (`src/Contracts`) - repositories, mailer, cookies, authorization, CSRF - with the technology-bound adapters split between `src/Application` (policy, e.g. the ACL) and `src/Infrastructure` (repositories over `Phalcon\DataMapper`, the mailer, the views). `src/AppFront.php` is the composition root that wires them together.

## Documentation

The `docs/` folder covers the port in depth:

* [Installation](docs/installation.md) - Docker and local setup, environment variables, and choosing the PHP version.
* [Architecture](docs/architecture.md) - the ADR layers, ports and adapters, middleware, and the composition root.
* [Payloads](docs/payload.md) - the payload contract between domains and responders, and how it maps to HTTP.
* [Testing](docs/testing.md) - the unit and integration suites, the fake pattern, and the coverage merge.

## Requirements

* PHP 8.1 - 8.5
* MySQL 8.0 (provided by the Docker stack)
* Docker + Docker Compose (recommended), or a local PHP with the `phalcon/phalcon` package installed via Composer

## Quick start (Docker)

```bash
cp resources/.env.example .env
docker compose up -d --build

# Create and seed the database (migrations are not run on boot)
docker compose exec app composer migrate
docker compose exec app composer seed
```

> **Note:** `app` is the Compose *service* name, used as-is by `docker compose exec` above. The running container, however, is named `${PROJECT_PREFIX}-app` - `vokuro-adr-app` by default, set via `PROJECT_PREFIX` in `.env`. If you address it with plain `docker exec`, use your container name instead, e.g. `docker exec vokuro-adr-app composer migrate` (substitute your own prefix).

Then open:

* Application: <http://localhost:8081>
* Mailpit (captured e-mails): <http://localhost:8026>

The container waits for MySQL and serves the app; migrations are decoupled from boot - apply them with the commands above. Log in with a seeded account, e.g. `sarah.connor@skynet.dev` / `password1`.

### Choosing the PHP version

The image is built for one PHP version at a time, selected with the `PHP_VERSION` build arg (default `8.5`; supported `8.1`-`8.5`):

```bash
docker compose up -d --build                  # PHP 8.5 (default)
PHP_VERSION=8.1 docker compose up -d --build  # PHP 8.1
```

The container keeps the same name, so each rebuild **replaces** the previous one. To run several versions side by side, give each its own Compose project and prefix:

```bash
PHP_VERSION=8.1 PROJECT_PREFIX=vokuro81 docker compose -p vokuro81 up -d --build
# then: docker exec -w /srv vokuro81-app composer test-all
```

## Quick start (Composer)

Prefer a local PHP over Docker? Bootstrap a fresh copy straight from Packagist:

```bash
composer create-project phalcon/vokuro-adr vokuro-adr
cd vokuro-adr
```

The post-create hook copies `resources/.env.example` to `.env` and prints the next steps. The app runs on the bundled `phalcon/phalcon` (v6) package - no C extension needed. You still need a MySQL database; point `.env` at it and run `composer migrate && composer seed`.

## Composer scripts

Run them inside the container, e.g. `docker compose exec app composer cs`:

| Script | Description |
| --- | --- |
| `composer cs` | PHP_CodeSniffer (PSR-12) |
| `composer cs-fix` | Auto-fix coding standard issues (phpcbf) |
| `composer cs-fixer` / `composer cs-fixer-fix` | PHP CS Fixer (dry-run / apply) |
| `composer analyze` | PHPStan static analysis |
| `composer test-unit` | Unit suite (fast, no database) |
| `composer test-integration` | Integration suite (creates + migrates `vokuro_adr_test`, then runs it) |
| `composer test-all` | Both suites |
| `composer test-unit-coverage` | Unit suite + Clover coverage |
| `composer test-all-coverage` | Both suites, merged into one Clover report (`tests/_output/coverage.xml`) via `phpcov` |
| `composer migrate` | Run database migrations (Phinx) |
| `composer seed` | Seed the database |

## Running the tests

The suite is split into two PHPUnit testsuites, both driven by
[`phalcon/talon`](https://github.com/phalcon/talon):

* **unit** (`tests/Unit`) - the domains, actions, middleware, responders, forms, and infrastructure, driven by in-memory fakes (`tests/Support/Fake`). Fast, no database.
* **integration** (`tests/Integration`) - the eight repositories against a real, dedicated `vokuro_adr_test` database (truncated and re-seeded per test, no transactions), plus a functional boot of `AppFront`.

```bash
docker compose up -d --build
docker compose exec app composer migrate          # once - create the schema
docker compose exec app composer seed             # once - load fixtures

docker compose exec app composer test-all           # both suites
docker compose exec app composer test-all-coverage  # + merged Clover coverage
```

The Docker stack provides everything the integration suite needs: MySQL and a [Mailpit](https://mailpit.axllent.org/) SMTP catcher, so no e-mail ever leaves the host.

### Test secrets

The test configuration lives in `tests/.env.test` and is loaded automatically by `tests/bootstrap.php` - you do not need to supply anything by hand:

| Variable | Value | Purpose |
| --- | --- | --- |
| `APP_CRYPT_SALT` | *(preset)* | crypt key for the session / security services |
| `DB_USERNAME` / `DB_PASSWORD` | `root` / `secret` | matches the MySQL container's root password |
| `DB_NAME` | `vokuro_adr` | the migrated and seeded database (the integration suite uses a dedicated `vokuro_adr_test`) |
| `MAIL_SMTP_SERVER` / `MAIL_SMTP_PORT` | `127.0.0.1` / `1025` | Mailpit catcher - tests never reach a real SMTP server |

Real OS / CI environment variables take precedence over `tests/.env.test`, so the same suite runs unchanged inside Docker (service-name hosts `mysql` / `mailpit`) and on a native host or in CI (loopback `127.0.0.1`). The only secret that is **not** local is `SONAR_TOKEN`, a GitHub Actions secret used solely by the coverage job's SonarQube scan.

## Updating Phalcon

`docker compose exec app composer update phalcon/phalcon` (no rebuild). Dependabot opens the bump PR automatically.

## Project layout

Follows the [PDS skeleton](https://github.com/php-pds/skeleton):

```
public/     web server root
resources/  views, tooling configs, docker, phinx, migrations, seeds
src/        application source (Action, Domain, Responder, Contracts, Application, Infrastructure, ...)
tests/      PHPUnit suites (Unit, Integration) and Support/Fake
var/        runtime cache and logs
```

## License

Vökuró is open-sourced software licensed under the New BSD License. See [LICENSE](LICENSE).

<!-- Badges -->
[packagist-version-badge]:   https://img.shields.io/packagist/v/phalcon/vokuro-adr?include_prereleases&style=flat-square&logo=packagist&logoColor=white
[packagist-version-link]:    https://packagist.org/packages/phalcon/vokuro-adr
[packagist-downloads-badge]: https://img.shields.io/packagist/dt/phalcon/vokuro-adr?style=flat-square&logo=packagist&logoColor=white
[packagist-downloads-link]:  https://packagist.org/packages/phalcon/vokuro-adr/stats
[php-version-badge]:         https://img.shields.io/packagist/php-v/phalcon/vokuro-adr?style=flat-square&logo=php&logoColor=white
[license-badge]:             https://img.shields.io/github/license/phalcon/vokuro-adr?style=flat-square&logo=opensourceinitiative&logoColor=white
[license-link]:              https://github.com/phalcon/vokuro-adr/blob/master/LICENSE
[vokuro-ci-badge]:           https://github.com/phalcon/vokuro-adr/actions/workflows/main.yml/badge.svg?branch=master
[vokuro-ci-link]:            https://github.com/phalcon/vokuro-adr/actions/workflows/main.yml
[sonar-quality-badge]:       https://sonarcloud.io/api/project_badges/measure?project=phalcon_vokuro-adr&metric=alert_status
[sonar-coverage-badge]:      https://sonarcloud.io/api/project_badges/measure?project=phalcon_vokuro-adr&metric=coverage
[sonar-link]:                https://sonarcloud.io/summary/new_code?id=phalcon_vokuro-adr
[pds-skeleton-badge]:        https://img.shields.io/badge/pds-skeleton-blue.svg?style=flat-square
[pds-skeleton-link]:         https://github.com/php-pds/skeleton
[discord-badge]:             https://img.shields.io/discord/310910488152375297?label=Discord&logo=discord&style=flat-square
[discord-link]:              https://phalcon.io/discord
[contributors-badge]:        https://img.shields.io/github/contributors/phalcon/vokuro-adr?style=flat-square&logo=github&logoColor=white
[contributors-link]:         https://github.com/phalcon/vokuro-adr/graphs/contributors
[oc-backers-badge]:          https://img.shields.io/opencollective/backers/phalcon?style=flat-square&logo=opencollective&logoColor=white
[oc-backers-link]:           https://opencollective.com/phalcon
[oc-sponsors-badge]:         https://img.shields.io/opencollective/sponsors/phalcon?style=flat-square&logo=opencollective&logoColor=white
[oc-sponsors-link]:          https://opencollective.com/phalcon
