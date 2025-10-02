# NativePHP Extension CLI (`nativephp-ext-cli`)

A Laravel package that provides a powerful command-line tool to build custom static PHP binaries with selected extensions for NativePHP applications using `static-php-cli`. Optimized for Windows with robust handling of compilation challenges.

## Features

- 🚀 **One-Command Build** - Interactive CLI for building custom PHP binaries
- 🪟 **Windows-Optimized** - Handles symlinks, path conversions, and Windows-specific issues
- 🔧 **Multiple PHP Versions** - Support for PHP 8.1, 8.2, 8.3, 8.4, and custom versions
- 💾 **Database Extensions** - MySQL, PostgreSQL, SQL Server, SQLite
- 📦 **Extension Packs** - Web, Performance, Processing, Compression, Network, SOAP
- 🐍 **Python-Based Extraction** - Windows-safe tar.gz/tar.xz extraction with symlink handling
- 🔄 **GitHub Fallback** - Automatic fallback to GitHub cloning when downloads fail
- ✅ **Smart Verification** - Detects successful extractions even with Python errors
- 📝 **Detailed Logging** - Complete build logs for troubleshooting

## Requirements

### For Development
- **PHP 8.1+** - Required for Laravel and package functionality
- **Laravel Framework 10.0+** - Compatible with Laravel 10, 11, and 12
- **Composer** - PHP dependency management

### For Building Custom PHP Binaries (Windows)
- **Python 3.8+** - Used for reliable tar.gz/tar.xz extraction on Windows
  - Handles archives with symlinks gracefully
  - Install from: https://www.python.org/downloads/
- **CMake 3.15+** - Required for building native Windows libraries (zlib, openssl, etc.)
  - ⚠️ **CRITICAL**: Must add CMake to system PATH during installation
  - Download from: https://cmake.org/download/
  - Verify: `cmake --version`
- **Visual C++ Build Tools** - Microsoft C++ compiler (MSVC) for Windows builds
  - Automatically detected by php-sdk-binary-tools
- **Git for Windows** - Provides bash, tar, and git commands

### PHP Memory Configuration
- **Minimum 512MB** recommended for building PHP binaries
- Default 128MB will cause memory exhaustion errors
- Set via: `php -d memory_limit=512M` or edit `php.ini`

## Installation

```bash
# Install via Composer
composer require amohamed/nativephp-ext-cli

# Verify installation
php artisan list
```

You should see the `php-ext:install` command listed.

## Usage

### Quick Start

```bash
# Interactive build with increased memory (recommended)
php -d memory_limit=512M artisan php-ext:install
```

The tool will:
1. Detect your operating system
2. Prompt you to select PHP version (8.1, 8.2, 8.3, 8.4, or custom)
3. Ask which database drivers you need (MySQL, PostgreSQL, SQL Server)
4. Show available extension packs (Web, Performance, Processing, etc.)
5. Download, extract, and compile PHP with selected extensions
6. Output the binary to `static-php-cli/buildroot/bin/php.exe`

### Command Options

```bash
# Build specific PHP version (with increased memory)
php -d memory_limit=512M artisan php-ext:install --php-version=8.4

# Build specific patch version (with increased memory)
php -d memory_limit=512M artisan php-ext:install --php-version=8.3.13

# Build with specific extensions (non-interactive, with increased memory)
php -d memory_limit=512M artisan php-ext:install --php-version=8.3 --extensions=mysqli,pdo_mysql,soap
```

**Important**: Always use `php -d memory_limit=512M` to avoid memory exhaustion errors during the build process.

### Supported PHP Versions

- **8.1.x** - Full support including SQL Server extensions
- **8.2.x** - Full support including SQL Server extensions
- **8.3.x** - Full support including SQL Server extensions
- **8.4.x** - Supported (SQL Server extensions not available)
- **Custom** - Any specific version (e.g., 8.3.13, 8.4.1)

### Available Extensions

#### Database Extensions
- **MySQL**: `mysqli`, `pdo_mysql`
- **PostgreSQL**: `pgsql`, `pdo_pgsql`
- **SQL Server**: `sqlsrv`, `pdo_sqlsrv` (PHP 8.3 and below)
- **SQLite**: `sqlite3`, `pdo_sqlite` (always included)

#### Extension Packs
- **Web**: `dom`, `xml`, `simplexml`, `gd`
- **Performance**: `opcache`, `phar`
- **Processing**: `iconv`, `ctype`, `bcmath`
- **Compression**: `bz2`
- **Network**: `sockets`
- **SOAP**: `soap`

#### Core Extensions (Always Included)
`pdo`, `mbstring`, `fileinfo`, `tokenizer`, `openssl`, `curl`, `zip`, `zlib`, `session`, `filter`

### Build Output

After successful build:
- **Binary Location**: `static-php-cli/buildroot/bin/php.exe`
- **Build Logs**: `static-php-cli/log/spc.output.log` and `spc.shell.log`

### Verify Built Binary

```bash
# Check PHP version
./static-php-cli/buildroot/bin/php.exe -v

# List loaded extensions
./static-php-cli/buildroot/bin/php.exe -m

# Check specific extension
./static-php-cli/buildroot/bin/php.exe --ri mysqli
```

## How It Works

### Build Process

1. **Setup Phase**: Clones `static-php-cli` and `php-sdk-binary-tools`
2. **Download Phase**: Downloads PHP source, libraries, and extension sources
3. **Pre-extraction Phase**: Uses Python to extract sources (Windows-safe)
4. **Library Extraction**: Handles symlinks and Windows path issues
5. **Build Phase**: Compiles PHP with MSVC and selected extensions
6. **Verification Phase**: Tests the compiled binary

### Windows-Specific Adaptations

- **Symlink Handling**: Python script skips symlinks (not supported on Windows without admin rights)
- **Path Conversion**: Automatic conversion between Windows and Unix-style paths
- **GitHub Fallback**: Auto-clones from GitHub when standard downloads fail (e.g., openssl)
- **Hash File Creation**: Prevents static-php-cli from re-extracting pre-extracted sources
- **Windows-Optimized Repos**: Uses `winlibs` repositories for better Windows compatibility (libxml2, zlib, etc.)

## NativePHP Integration

This package is designed for use with [NativePHP](https://nativephp.com/) applications. Install NativePHP:

```bash
# Add NativePHP to your project
composer require nativephp/electron

# Start native app
php artisan native:serve
```

The custom PHP binary built with this package can be configured in your NativePHP application to include all necessary database extensions.

## Troubleshooting

### Memory Exhaustion Error
```
Allowed memory size of 134217728 bytes exhausted
```
**Solution**:
```bash
php -d memory_limit=512M artisan php-ext:install
```

### CMake Not Found
```
'cmake' is not recognized as an internal or external command
```
**Solution**:
1. Install CMake from https://cmake.org/download/
2. Select "Add CMake to system PATH" during installation
3. Restart terminal
4. Verify: `cmake --version`

### MSVC Compiler Errors (C4146, C4703)
```
error C4146: unary minus operator applied to unsigned type
```
**Cause**: PHP 8.3.26+ has code patterns triggering strict MSVC warnings

**Workaround**:
1. Try building without SQL Server extensions
2. Use PHP 8.3.13 (known stable version)
3. Retry - sometimes transient compiler issues resolve

### Python Unicode Error
```
UnicodeEncodeError: 'charmap' codec can't encode character
```
**Status**: Fixed in latest version - Script uses ASCII-safe output

### Symlink Errors
```
tar: Cannot create symlink
```
**Status**: Fixed - Python script skips symlinks automatically

### Clean Build
If build fails repeatedly:
```bash
rm -rf static-php-cli
php -d memory_limit=512M artisan php-ext:install
```

For additional help, check the build logs in `static-php-cli/log/` or open an issue on the [GitHub repository](https://github.com/Abdallah-Tah/php-extension-builder-nativephp).

## Example Project

See a complete implementation of this package in action:
- [NativePHP Database Driver Switcher](https://github.com/Abdallah-Tah/nativephp-switch-driver-sql)
- Live demo of switching between SQLite, MySQL, PostgreSQL, and SQL Server
- Livewire interface for real-time database operations

## Contributing

Contributions are welcome! Please submit a pull request or open an issue on the [GitHub repository](https://github.com/Abdallah-Tah/php-extension-builder-nativephp).

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Acknowledgments

- [static-php-cli](https://github.com/crazywhalecc/static-php-cli) - PHP static compiler
- [NativePHP](https://nativephp.com/) - Laravel desktop app framework
- [php-sdk-binary-tools](https://github.com/php/php-sdk-binary-tools) - Microsoft's PHP SDK for Windows
