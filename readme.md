# PHP Symfony Environment for JSON REST APIs

This repository contains a fully dockerised Symfony 7 environment that showcases common patterns for building JSON REST APIs. It provides a batteries-included development experience with PHP 8.4 FPM, Nginx, MySQL, RabbitMQ, Elasticsearch, Redis, Kibana, and Mailpit configured to work together through Docker Compose.

The setup is opinionated around local development but includes staging and production presets so the same stack can be reproduced in higher environments. Use it as a starting point for new projects or as a learning resource for running Symfony applications in containers.

---

## Table of Contents

1. [Features](#features)
2. [Requirements](#requirements)
3. [Architecture Overview](#architecture-overview)
4. [Getting Started](#getting-started)
   * [Install Docker](#install-docker)
   * [Clone or Scaffold the Project](#clone-or-scaffold-the-project)
   * [Configure Environment Secrets](#configure-environment-secrets)
   * [Start the Development Stack](#start-the-development-stack)
   * [Run Database and Messaging Setup](#run-database-and-messaging-setup)
5. [Available Services](#available-services)
6. [Managing Environments](#managing-environments)
7. [Working with Containers](#working-with-containers)
8. [Key Make Commands](#key-make-commands)
9. [Testing and Quality Tools](#testing-and-quality-tools)
10. [Troubleshooting](#troubleshooting)
11. [Useful Packages](#useful-packages)
12. [License](#license)

---

## Features

* **Docker-first workflow** – reproducible environments for development, staging, and production using Docker Compose.
* **API ready** – Symfony 7 configured for JSON REST APIs with JWT authentication, API documentation, and background processing.
* **Observability stack** – Elasticsearch, Kibana, and RabbitMQ management console available out of the box.
* **Developer tooling** – preconfigured PHPStan, PHP CS Fixer (via ECS), PHP Insights, PHPUnit, and more accessible through `make` targets.

## Requirements

* Docker Engine **23.0** or later
* Docker Compose **v2**
* A terminal with GNU Make
* An editor/IDE (PHPStorm recommended)
* Optional client tools such as MySQL Workbench for inspecting databases

> **Tip**: Linux (Ubuntu or Debian based) offers the best experience, but macOS and Windows (WSL2) are supported as long as Docker Desktop meets the requirements.

## Architecture Overview

| Component | Version | Purpose |
|-----------|---------|---------|
| Nginx | 1.27 | Serves the Symfony application and static assets |
| PHP-FPM | 8.4 | Runs the Symfony codebase |
| MySQL | 8 | Primary relational database |
| RabbitMQ | 4 | Message broker for async tasks |
| Elasticsearch | 7 | Full-text search and analytics |
| Kibana | 7 | Observability UI for Elasticsearch |
| Redis | 7 | Cache and queue backend |
| Mailpit | latest | Development SMTP server (DEV only) |

The services are declared in `compose.yaml` and environment-specific overrides such as `compose-staging.yaml`, `compose-prod.yaml`, and `compose.override.yaml`.

## Getting Started

### Install Docker

Follow the official Docker Engine installation guide for your operating system: <https://docs.docker.com/engine/install/>.

After installation on Linux, add your user to the Docker group so you can run Docker without `sudo`:

```bash
sudo usermod -aG docker "$USER"
```

If you are using Docker Desktop on macOS 12.2 or later, enable [virtiofs file sharing](https://www.docker.com/blog/speed-boost-achievement-unlocked-on-docker-desktop-4-6-for-mac/) (enabled by default since Docker Desktop v4.22) for improved performance.

### Clone or Scaffold the Project

Clone the repository:

```bash
git clone https://github.com/systemsdk/docker-symfony-api.git
cd docker-symfony-api
```

Alternatively, create a fresh project using Composer:

```bash
composer create-project systemsdk/docker-symfony-api api-example-app
cd api-example-app
```

### Configure Environment Secrets

1. Duplicate `.env` into `.env.local` (optional) to override values locally.
2. Update `APP_SECRET` in `.env`, `.env.staging`, and `.env.prod` with unique values. Generate secrets from <http://nux.net/secret> or using `openssl rand -hex 16`.
3. Remove `var/mysql-data` if it exists to avoid permission issues.
4. Update `compose-prod.yaml` and `.env.prod` with secure credentials for MySQL and RabbitMQ before deploying to staging/production.

> **Note**: Do not commit `.env.local.php` for development or test environments. Delete it if generated.

Add the default host entry so local DNS resolves:

```text
127.0.0.1    localhost
```

### Start the Development Stack

```bash
make build
make start
make composer-install
make generate-jwt-keys
```

These commands build the images, start the containers, install Composer dependencies, and generate the JWT key pair used by the authentication layer.

### Run Database and Messaging Setup

After the containers are running, initialise the database, background workers, and search templates:

```bash
make migrate
make create-roles-groups
make migrate-cron-jobs
make messenger-setup-transports
make elastic-create-or-update-template
```

## Available Services

Once `make start` completes, the following URLs are available:

* API documentation – <http://localhost/api/doc>
* RabbitMQ management – <http://localhost:15672>
* Kibana – <http://localhost:5601>
* Mailpit (development email inbox) – <http://localhost:8025>

Use the default Elasticsearch bootstrap credentials to access Kibana:

```text
Username: elastic
Password: changeme
```

Replace these credentials for staging and production deployments.

## Managing Environments

### Staging Profile

Use the staging Compose file and tailored make targets:

```bash
make build-staging
make start-staging
make generate-jwt-keys
make migrate-no-test
make create-roles-groups
make migrate-cron-jobs
make messenger-setup-transports
make elastic-create-or-update-template
```

### Production Profile

Before starting the production stack, ensure that `.env.prod` and `compose-prod.yaml` include strong credentials. Then run:

```bash
make build-prod
make start-prod
make generate-jwt-keys
make migrate-no-test
make create-roles-groups
make migrate-cron-jobs
make messenger-setup-transports
make elastic-create-or-update-template
```

## Working with Containers

Open a shell inside the PHP container:

```bash
make ssh
```

Other helpful targets include `make ssh-nginx`, `make ssh-supervisord`, `make ssh-mysql`, `make ssh-rabbitmq`, `make ssh-elasticsearch`, and `make ssh-kibana`. Use `exit` to leave the container shell.

Rebuild containers after changing Dockerfiles or configuration:

```bash
make down
make build
make start
```

To stop or remove environments:

```bash
make stop        # stop dev containers
make stop-staging
make stop-prod

make down        # stop and remove dev containers
make down-staging
make down-prod
```

## Key Make Commands

The project uses GNU Make to wrap everyday workflows. Explore all tasks with `make help`. Highlights include:

```bash
# Environment lifecycle
make start            # start development stack
make restart          # restart containers
make info             # show container status

# Composer shortcuts
make composer-install
make composer-install-no-dev
make composer-update
make composer-audit

# Logs
make logs             # aggregate application logs
make logs-nginx
make logs-mysql
make logs-rabbitmq
make logs-elasticsearch
make logs-kibana

# Database and fixtures
make migrate
make migrate-no-test
make drop-migrate
make fixtures

# Messaging & search
make messenger-setup-transports
make elastic-create-or-update-template

# Shell helpers
make ssh
make ssh-root
make fish
```

Consult the `Makefile` for additional commands such as `make env-staging`, `make env-prod`, and specialised log/ssh targets for each service.

## Testing and Quality Tools

Quality assurance tasks are available via make:

```bash
make phpunit                 # run the test suite
make report-code-coverage    # generate coverage report
make phpcs                   # coding standards check
make ecs                     # easy coding standard
make ecs-fix                 # auto-fix coding style issues
make phpmetrics              # collect complexity metrics
make phpcpd                  # detect copy/paste
make phpcpd-html-report      # HTML report for copy/paste detector
make phpmd                   # mess detector
make phpstan                 # static analysis
make phpinsights             # comprehensive project insights
```

## Troubleshooting

* **Slow file sync on macOS** – ensure virtiofs is enabled in Docker Desktop.
* **Permission errors on MySQL data** – remove `var/mysql-data` before the first start so Docker can create the directory with correct permissions.
* **Xdebug configuration** – toggle request triggering in `/docker/dev/xdebug-main.ini` (Linux/Windows) or `/docker/dev/xdebug-osx.ini` (macOS):
  * Set `xdebug.start_with_request = no` to debug only when your browser extension (e.g., Xdebug Helper) is active with IDE key `PHPSTORM`.
  * Set `xdebug.start_with_request = yes` to debug every incoming request automatically.
* **Enabling Elasticsearch paid features** – change `xpack.license.self_generated.type` from `basic` to `trial` in `/docker/elasticsearch/config/elasticsearch.yml`.

## Useful Packages

The project bundles several Symfony bundles and PHP tools that demonstrate best practices:

* [Symfony 7](https://symfony.com)
* [Doctrine Migrations Bundle](https://github.com/doctrine/DoctrineMigrationsBundle)
* [Doctrine Fixtures Bundle](https://github.com/doctrine/DoctrineFixturesBundle)
* [DukeCity Command Scheduler Bundle](https://packagist.org/packages/dukecity/command-scheduler-bundle)
* [PHPUnit](https://github.com/sebastianbergmann/phpunit)
* [dama/doctrine-test-bundle](https://packagist.org/packages/dama/doctrine-test-bundle)
* [symfony/phpunit-bridge](https://github.com/symfony/phpunit-bridge)
* [BrowserKit](https://github.com/symfony/browser-kit)
* [CSS Selector](https://github.com/symfony/css-selector)
* [local-php-security-checker](https://github.com/fabpot/local-php-security-checker)
* [Symfony Messenger](https://symfony.com/doc/current/messenger.html)
* [composer-bin-plugin](https://github.com/bamarni/composer-bin-plugin)
* [composer-normalize](https://github.com/ergebnis/composer-normalize)
* [composer-unused](https://packagist.org/packages/icanhazstring/composer-unused)
* [composer-require-checker](https://packagist.org/packages/maglnet/composer-require-checker)
* [Symfony Requirements Checker](https://github.com/symfony/requirements-checker)
* [Roave Security Advisories](https://github.com/Roave/SecurityAdvisories)
* [Lexik JWT Authentication Bundle](https://packagist.org/packages/lexik/jwt-authentication-bundle)
* [Automapper Plus Bundle](https://packagist.org/packages/mark-gerarts/automapper-plus-bundle)
* [Symfony Console Form](https://packagist.org/packages/matthiasnoback/symfony-console-form)
* [Nelmio API Doc Bundle](https://packagist.org/packages/nelmio/api-doc-bundle)

### Additional Tooling

Complementary packages used throughout the project include:

* [Nelmio CORS Bundle](https://packagist.org/packages/nelmio/cors-bundle)
* [Matomo Device Detector](https://packagist.org/packages/matomo/device-detector)
* [ramsey/uuid-doctrine](https://packagist.org/packages/ramsey/uuid-doctrine)
* [Gedmo Doctrine Extensions](https://packagist.org/packages/gedmo/doctrine-extensions)
* [systems dk/easy-log-bundle](https://packagist.org/packages/systemsdk/easy-log-bundle)
* [php-coveralls](https://github.com/php-coveralls/php-coveralls)
* [Symplify Easy Coding Standard](https://github.com/Symplify/EasyCodingStandard)
* [PhpMetrics](https://github.com/phpmetrics/PhpMetrics)
* [systems dk/phpcpd](https://packagist.org/packages/systemsdk/phpcpd)
* [PHP Mess Detector](https://packagist.org/packages/phpmd/phpmd)
* [PHPStan](https://packagist.org/packages/phpstan/phpstan)
* [PHP Insights](https://packagist.org/packages/nunomaduro/phpinsights)
* [beberlei/DoctrineExtensions](https://github.com/beberlei/DoctrineExtensions)
* [elasticsearch-php](https://github.com/elastic/elasticsearch-php)
* [Rector](https://packagist.org/packages/rector/rector)

## Additional Resources

* [Symfony Flex REST API](https://github.com/tarlepp/symfony-flex-backend.git) – original project that inspired the contents of the `src/` directory.
* Project documentation stored in the [`docs/`](docs) folder:
  * [Commands overview](docs/commands.md)
  * [API key management](docs/api-key.md)
  * [Development workflow](docs/development.md)
  * [Testing guide](docs/testing.md)
  * [PhpStorm configuration](docs/phpstorm.md)
  * [Xdebug configuration](docs/xdebug.md)
  * [Swagger usage](docs/swagger.md)
  * [Postman collection](docs/postman.md)
  * [Redis Desktop Manager](docs/rdm.md)
  * [Messenger component](docs/messenger.md)

## Contribution Guidelines

1. Branch from `develop` using the pattern `feature/{ticketNo}`.
2. Commit early and often with descriptive messages to make reviews easier.
3. Open a pull request targeting `develop` titled `feature/{ticketNo} – <short description>`.
4. Iterate on feedback until all automated checks (CircleCI or equivalent) pass.
5. Approved pull requests are squashed into `develop` and later merged into `release/{No}` for deployment.

> Learn more about the branching strategy in the [git-flow cheat sheet](https://danielkummer.github.io/git-flow-cheatsheet).

## License

Distributed under the [MIT License](LICENSE).
