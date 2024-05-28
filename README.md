# FaimMedia PHP i18n JSON library

Simple PHP library to validate and match JSON translation files, and also pretty format them.

## Install & usage

### Add composer

Install this library using composer:

```bash
composer require faimmedia/i18n-json
```

### Run validator (using CLI)

Use the `./vendor/bin/i18n-json` command to run the json compare.

## Development

Start up docker containers:

```bash
docker compose up -d
```

Run tests:

```bash
./bin/test
```

Run validator CLI:

```bash
docker compose exec -T test /app/bin/i18n-json --path=/app/test/sql
```
