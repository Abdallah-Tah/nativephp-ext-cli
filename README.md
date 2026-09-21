# NativePHP Extension CLI (`nativephp-ext-cli`)

Lightweight NativePHP PHP binary manager for resolving, verifying, caching, and installing PHP binaries.

## Install

```bash
composer require amohamed/nativephp-ext-cli --dev
```

## Quick start

```bash
php artisan nativephp:php:list
php artisan nativephp:php:install --php=8.5 --profile=mysql
```

Backward compatibility is preserved:

```bash
php artisan php-ext:install --php-version=8.5 --driver=mysql
```

## Commands

- `php artisan php-ext:install` (legacy/backward compatible)
- `php artisan nativephp:php:list [--json]`
- `php artisan nativephp:php:install --php=8.5 --profile=mysql [--driver=mysql] [--dry-run] [--json]`
- `php artisan nativephp:php:doctor [--json]`

## Supported PHP versions

- 8.1 (`8.1.31`)
- 8.2 (`8.2.29`)
- 8.3 (`8.3.15`)
- 8.4 (`8.4.13`)
- 8.5 (`8.5.3`)

Exact patch input is supported (for example `8.5.3`). Minor input resolves to the latest known patch, with network lookup fallback.

## Profiles

- `nativephp`
- `mysql`
- `postgres`
- `sqlserver`
- `mysql-postgres`
- `databases`
- `custom`

`nativephp` includes the NativePHP base extension profile (including `sodium`).

## Drivers

- `sqlite`
- `mysql` (`mysqli`, `pdo_mysql`)
- `postgres` (`pgsql`, `pdo_pgsql`)
- `sqlserver` (`sqlsrv`, `pdo_sqlsrv`)

Aliases are normalized (`pgsql`/`postgresql` → `postgres`, `sqlsrv`/`mssql` → `sqlserver`).

## Resolve → verify → install flow

Current command behavior is resolver-first (`--dry-run` is fully supported) with local SPC build fallback retained in `php-ext:install`.
The extracted services implement the target flow interfaces:

1. Build specification is normalized and hashed deterministically.
2. Resolver checks local cache.
3. Resolver optionally checks a binary manifest registry (`NATIVEPHP_EXT_BINARY_REGISTRY`).
4. Downloader verifies HTTPS payload size/checksum before cache promotion.
5. If no prebuilt/cached binary exists, local SPC build flow is required.

## Cache location

- Linux/macOS: `~/.nativephp-ext/`
- Windows: `%LOCALAPPDATA%\nativephp-ext\`

Structure:

- `binaries/`
- `manifests/`
- `builds/`

## Manifest schema (consumer)

```json
{
  "schema": 1,
  "generated_at": "2026-09-21T12:00:00Z",
  "artifacts": [
    {
      "php": "8.5.3",
      "php_minor": "8.5",
      "platform": "win-x64",
      "profile": "mysql",
      "extensions": ["mysqli", "pdo_mysql"],
      "url": "https://example.com/php.zip",
      "sha256": "abc...",
      "size": 12345678
    }
  ]
}
```

## Security guarantees

- HTTPS-only downloads by default.
- SHA-256 verification using constant-time comparison.
- Temporary download files are cleaned on failure.
- Atomic move into cache after verification.
- No execution before integrity checks pass.

## Windows prerequisites (local build only)

These are only needed when prebuilt/cached binaries are unavailable and local compilation is required:

- Visual Studio C++ build tools
- Strawberry Perl
- CMake
- Python
- php-sdk-binary-tools

## Notes

This package does **not** include cloud build infrastructure, SaaS backends, payments, auth, or GUI implementation. It exposes clean interfaces so those can be added later.
