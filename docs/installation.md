# Installation

Vökuró ADR runs on **Phalcon v6** (the `phalcon/phalcon` Composer package). The Docker stack is the quickest way to a working app; a local PHP works too.

## Docker (recommended)

The stack builds a PHP-FPM image, a MySQL 8 database, and a [Mailpit](https://mailpit.axllent.org/) SMTP catcher.

```bash
cp resources/.env.example .env
docker compose up -d --build

# The schema is not created on boot - run these once:
docker compose exec app composer migrate
docker compose exec app composer seed
```

Then open:

* Application: <http://localhost:8081>
* Mailpit (captured e-mails): <http://localhost:8026>

Sign in with a seeded account, for example `sarah.connor@skynet.dev` / `password1`.

`app` is the Compose *service* name. The running container is `${PROJECT_PREFIX}-app` (`vokuro-adr-app` by default), so a plain `docker exec` uses the container name: `docker exec vokuro-adr-app composer migrate`.

### Environment

`resources/.env.example` is the template. Copy it to `.env` and adjust as needed.

| Variable | Default | Purpose |
| --- | --- | --- |
| `PROJECT_PREFIX` | `vokuro-adr` | prefix for the container names |
| `PHP_VERSION` | `8.4` | PHP version the image is built for (`8.1`-`8.5`) |
| `APP_PORT` | `8081` | host port mapped to the app (container listens on `8080`) |
| `MAILPIT_HTTP` | `8026` | host port mapped to the Mailpit UI |
| `UID` / `GID` | `1000` | host user, so mounted files stay writable |
| `APP_ENV` | `development` | environment; the error responder shows exceptions in `development` |
| `APP_CRYPT_SALT` | *(preset)* | key for the session / security services |
| `APP_BASE_URI` | `/` | base URI the URL service builds links from |
| `DB_HOST` | `mysql` | the Compose service name; `127.0.0.1` on a native host |
| `DB_NAME` | `vokuro_adr` | application database |
| `DB_USERNAME` / `DB_PASSWORD` | `root` / `secret` | match the MySQL container |
| `MAIL_SMTP_SERVER` / `MAIL_SMTP_PORT` | `mailpit` / `1025` | the Mailpit catcher |

### Choosing the PHP version

The image builds one PHP version at a time via the `PHP_VERSION` build arg:

```bash
docker compose up -d --build                  # PHP 8.4 (default from .env.example)
PHP_VERSION=8.1 docker compose up -d --build  # PHP 8.1
```

Each rebuild replaces the container. To run several versions at once, give each its own Compose project and prefix:

```bash
PHP_VERSION=8.1 PROJECT_PREFIX=vokuro81 docker compose -p vokuro81 up -d --build
```

## Local PHP (no Docker)

You need PHP 8.1-8.5, Composer, and a MySQL 8 server.

```bash
composer create-project phalcon/vokuro-adr vokuro-adr
cd vokuro-adr
```

The post-create hook copies `resources/.env.example` to `.env`. The app runs on the bundled `phalcon/phalcon` package, so no C extension is required - a plain `composer install` (with dev dependencies) pulls it in.

1. Point `.env` at your database: set `DB_HOST` (usually `127.0.0.1`), `DB_NAME`, `DB_USERNAME`, and `DB_PASSWORD`. Create the database if it does not exist.
2. Create and seed the schema:

   ```bash
   composer migrate
   composer seed
   ```

3. Serve the `public/` directory. The built-in server is enough for a look:

   ```bash
   php -S localhost:8080 -t public
   ```

   Then open <http://localhost:8080>. For real use, point Nginx or Apache at `public/` with `public/index.php` as the front controller.

E-mails (sign-up confirmation, password reset) are sent over SMTP. Point `MAIL_SMTP_SERVER` / `MAIL_SMTP_PORT` at a catcher such as Mailpit, or a real server, before exercising those flows.

## Next steps

* [Architecture](architecture.md) - how the ADR layers and ports fit together.
* [Payloads](payload.md) - the contract between domains and responders.
* [Testing](testing.md) - the unit and integration suites.
