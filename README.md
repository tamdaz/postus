# Postus

Postus is a social network that enables to post the contents, discuss with one or more users.
This application is made with Symfony 6.3.

> Postus is in the development phase, it will be finished soon...

## Installation
To install the project, do:
```bash
git clone https://github.com/tamdaz/postus.git
```

## Requirements
To use this project, you must have:
- PHP 8.1 or higher
- PHP plugins : php-xml, php-curl and php-sqlite3
- composer (to install PHP packages)
- npm (to install Node packages)
- Symfony CLI
- Docker

## Install packages
To install packages, do:
```bash
make install-pkgs
# or
composer update && npm update
```

## Create database
Then, create the database with the command:
```bash
# Create the database
bin/console doctrine:schema:create
# Migrate to create tables in the db
bin/console doctrine:migrations:migrate
```

## Launch Mercure
Before starting the server, make sure Docker Engine and Docker Compose is installed.
```bash
cd docker/
docker compose up
```

## Start Server
To start the server, there's two possibilities:
1) Use the Symfony CLI:
```bash
symfony server:start --no-tls
```
In this command, the `--no-tls` is present to allows to start the server without TLS layer security.
2) Use the `make` command
```bash
make start
```

## Tests
To execute the test, you must have `google-chrome-stable` from your machine.
Then, execute the command:
```bash
make test
# or
composer test
```
### Troubleshooting
If the tests has failed due to chrome drivers, try to install `chrome-drivers` from the repo.

## Contributions
Any contributions are welcome ;)