<?php

namespace Amohamed\NativePhpCustomPhp\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use ZipArchive;

class InstallPhpExtensions extends Command
{
    protected $signature = 'php-ext:install {--php-version= : PHP version to build (8.1, 8.2, 8.3)} {--extensions= : Comma-separated list of extensions (or use interactive mode)}';

    protected $description = 'Build a custom PHP binary with database and extension support for NativePHP';

    protected array $availableExtensions = [
        // Database extensions
        'sqlite3' => [
            'name' => 'SQLite',
            'description' => 'SQLite database support (included by default)',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => ['sqlite']
        ],
        'pdo' => [
            'name' => 'PDO',
            'description' => 'PHP Data Objects (core extension)',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => []
        ],
        'pdo_sqlite' => [
            'name' => 'PDO SQLite',
            'description' => 'PDO driver for SQLite',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => ['sqlite']
        ],
        'mysqli' => [
            'name' => 'MySQLi',
            'description' => 'MySQL Improved extension',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => []
        ],
        'pdo_mysql' => [
            'name' => 'PDO MySQL',
            'description' => 'PDO driver for MySQL',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => []
        ],
        'pgsql' => [
            'name' => 'PostgreSQL',
            'description' => 'PostgreSQL database support',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => ['postgresql-win']
        ],
        'pdo_pgsql' => [
            'name' => 'PDO PostgreSQL',
            'description' => 'PDO driver for PostgreSQL',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => ['postgresql-win']
        ],
        'sqlsrv' => [
            'name' => 'SQL Server',
            'description' => 'Microsoft SQL Server support (PHP 8.3 and below)',
            'php_versions' => ['8.1', '8.2', '8.3'],
            'libraries' => []
        ],
        'pdo_sqlsrv' => [
            'name' => 'PDO SQL Server',
            'description' => 'PDO driver for Microsoft SQL Server (PHP 8.3 and below)',
            'php_versions' => ['8.1', '8.2', '8.3'],
            'libraries' => []
        ],

        // Default/Core extensions
        'bcmath' => [
            'name' => 'BCMath',
            'description' => 'Arbitrary precision mathematics',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => []
        ],
        'bz2' => [
            'name' => 'Bzip2',
            'description' => 'Bzip2 compression',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => ['bzip2']
        ],
        'ctype' => [
            'name' => 'Ctype',
            'description' => 'Character type functions',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => []
        ],
        'curl' => [
            'name' => 'cURL',
            'description' => 'HTTP client library',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => ['curl', 'libcurl', 'nghttp2', 'libssh2', 'openssl']
        ],
        'dom' => [
            'name' => 'DOM',
            'description' => 'Document Object Model',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => ['libxml2']
        ],
        'fileinfo' => [
            'name' => 'Fileinfo',
            'description' => 'File information functions',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => []
        ],
        'filter' => [
            'name' => 'Filter',
            'description' => 'Data filtering',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => []
        ],
        'gd' => [
            'name' => 'GD',
            'description' => 'Image processing library',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => ['libpng', 'libjpeg', 'freetype']
        ],
        'iconv' => [
            'name' => 'Iconv',
            'description' => 'Character encoding conversion',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => []
        ],
        'mbstring' => [
            'name' => 'Multibyte String',
            'description' => 'Multibyte string functions',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => []
        ],
        'opcache' => [
            'name' => 'OPcache',
            'description' => 'PHP opcode caching',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => []
        ],
        'openssl' => [
            'name' => 'OpenSSL',
            'description' => 'OpenSSL cryptographic functions',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => ['openssl']
        ],
        'phar' => [
            'name' => 'Phar',
            'description' => 'PHP Archive format',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => []
        ],
        'session' => [
            'name' => 'Session',
            'description' => 'Session handling',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => []
        ],
        'simplexml' => [
            'name' => 'SimpleXML',
            'description' => 'Simple XML parser',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => ['libxml2']
        ],
        'sockets' => [
            'name' => 'Sockets',
            'description' => 'Socket communication',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => []
        ],
        'tokenizer' => [
            'name' => 'Tokenizer',
            'description' => 'PHP tokenizer',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => []
        ],
        'xml' => [
            'name' => 'XML',
            'description' => 'XML parser',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => ['libxml2']
        ],
        'zip' => [
            'name' => 'ZIP',
            'description' => 'ZIP archive support',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => ['libzip', 'zlib']
        ],
        'zlib' => [
            'name' => 'Zlib',
            'description' => 'Compression library',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => ['zlib']
        ],

        // Additional optional extensions
        'soap' => [
            'name' => 'SOAP',
            'description' => 'SOAP protocol support',
            'php_versions' => ['8.1', '8.2', '8.3', '8.4'],
            'libraries' => ['libxml2']
        ]
    ];

    protected string $selectedPhpVersion = '8.3';
    protected array $selectedExtensions = [];
    protected array $requiredLibraries = [];

    // Default extensions that are always included in every build
    // Reduced to essential extensions for reliability
    protected array $defaultExtensions = [
        'pdo',           // Database abstraction layer
        'pdo_sqlite',    // SQLite PDO driver
        'sqlite3',       // SQLite3 extension
        'mbstring',      // Multibyte string functions
        'fileinfo',      // File information functions
        'tokenizer',     // PHP tokenizer
        'openssl',       // SSL/TLS cryptographic functions
        'curl',          // HTTP client library
        'zip',           // ZIP archive support
        'zlib',          // Compression library
        'session',       // Session handling
        'filter'         // Data filtering
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->validateEnvironment();

        // Get user preferences
        $this->getUserPreferences();

        // Set the path to static-php-cli at the Laravel project root
        $spcPath = base_path('static-php-cli');

        try {
            // STEP 1: Clone and setup static-php-cli
            $this->info('STEP 1: Cloning and setting up static-php-cli...');
            $this->setupStaticPhpCli($spcPath);

            // STEP 1.5: Setup PHP SDK Binary Tools
            $this->info('STEP 1.5: Setting up PHP SDK Binary Tools...');
            $this->setupPhpSdkBinaryTools($spcPath);

            // STEP 2: Run environment check with auto-fix
            $this->info('STEP 2: Running environment check...');
            $this->runDoctorCheck($spcPath);

            // STEP 3: Download PHP source and required libraries
            $this->info('STEP 3: Downloading required components...');
            $this->downloadComponents($spcPath);

            // STEP 4: Clean previous build artifacts
            $this->info('STEP 4: Cleaning previous build artifacts...');
            $this->cleanBuildArtifacts($spcPath);

            // STEP 4.5: Verify downloaded sources
            $this->info('STEP 4.5: Verifying downloaded sources...');
            $this->verifyDownloadedSources($spcPath);

            // STEP 5: Build PHP with extensions
            $this->info('STEP 5: Building PHP with extensions...');
            $buildResult = $this->buildPhpWithExtensions($spcPath);

            if ($buildResult) {
                $this->info('✅ Build completed successfully!');
                $this->displayBuildSummary($spcPath);
                return self::SUCCESS;
            }

            $this->error('❌ Build failed!');
            return self::FAILURE;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    protected function setupStaticPhpCli(string $spcPath): void
    {
        if (!file_exists($spcPath)) {
            $this->info('Cloning static-php-cli repository...');
            $cloneResult = Process::run("git clone https://github.com/crazywhalecc/static-php-cli.git \"{$spcPath}\"");

            if (!$cloneResult->successful()) {
                throw new RuntimeException('Failed to clone static-php-cli repository: ' . $cloneResult->errorOutput());
            }
        }

        // Update composer.json PHP version requirement
        $composerJsonPath = $spcPath . '/composer.json';
        if (file_exists($composerJsonPath)) {
            $this->info('Updating composer.json PHP version requirement...');
            $composerJson = json_decode(file_get_contents($composerJsonPath), true);
            $composerJson['require']['php'] = '>=8.3.0';

            // Remove composer.lock to force fresh install
            if (file_exists($spcPath . '/composer.lock')) {
                unlink($spcPath . '/composer.lock');
            }

            file_put_contents($composerJsonPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        // Install composer dependencies
        $this->info('Installing composer dependencies...');
        Process::path($spcPath)->run('composer update --ignore-platform-reqs');
        $composerResult = Process::path($spcPath)->run('composer install --ignore-platform-reqs');

        if (!$composerResult->successful()) {
            throw new RuntimeException('Failed to install composer dependencies: ' . $composerResult->errorOutput());
        }
    }

    protected function setupPhpSdkBinaryTools(string $spcPath): void
    {
        $phpSdkPath = $spcPath . '/php-sdk-binary-tools';

        if (!file_exists($phpSdkPath)) {
            $this->info('Cloning php-sdk-binary-tools repository...');
            $cloneResult = Process::run("git clone https://github.com/microsoft/php-sdk-binary-tools.git \"{$phpSdkPath}\"");

            if (!$cloneResult->successful()) {
                throw new RuntimeException('Failed to clone php-sdk-binary-tools repository: ' . $cloneResult->errorOutput());
            }
        } else {
            $this->info('php-sdk-binary-tools already exists, updating...');
            Process::path($phpSdkPath)->run('git pull');
        }

        // Create a symbolic link or copy to make it accessible from the expected path
        $sourcePath = $spcPath . '/source';
        if (!file_exists($sourcePath)) {
            mkdir($sourcePath, 0755, true);
        }

        $linkPath = $sourcePath . '/php-sdk-binary-tools';
        if (!file_exists($linkPath)) {
            // Create junction on Windows
            $linkResult = Process::run("mklink /J \"{$linkPath}\" \"{$phpSdkPath}\"");
            if (!$linkResult->successful()) {
                $this->warn('Failed to create junction, copying directory instead...');
                Process::run("xcopy \"{$phpSdkPath}\" \"{$linkPath}\" /E /I /Q");
            }
        }
    }

    protected function runDoctorCheck(string $spcPath): void
    {
        $doctorResult = Process::path($spcPath)
            ->timeout(300)
            ->env($this->getSpcEnvironment())
            ->run('php bin/spc doctor --auto-fix');

        if (!$doctorResult->successful()) {
            $this->warn('Doctor check failed. Attempting to continue anyway...');
        }
    }

    protected function downloadComponents(string $spcPath): void
    {
        // Download PHP source for the specified version - force the exact version
        $this->info("Downloading PHP {$this->selectedPhpVersion} source...");

        // First try with specific version
        $downloadResult = Process::path($spcPath)
            ->env($this->getSpcEnvironment())
            ->run("php bin/spc download php-src --with-php={$this->selectedPhpVersion}");

        if (!$downloadResult->successful()) {
            $this->warn("Specific PHP {$this->selectedPhpVersion} download failed. Checking available versions...");

            // List available PHP versions
            $listResult = Process::path($spcPath)->run('php bin/spc list php-src');
            if ($listResult->successful()) {
                $this->info("Available PHP versions:");
                $this->line($listResult->output());
            }

            throw new RuntimeException("Failed to download PHP {$this->selectedPhpVersion} source. Please check available versions.");
        }

        // Verify the correct PHP version was downloaded
        $this->info('Verifying PHP version...');
        $versionCheck = Process::path($spcPath)->run('php bin/spc list sources | findstr php-src');
        if ($versionCheck->successful()) {
            $this->line("PHP source info: " . trim($versionCheck->output()));
        }

        // CRITICAL: Pre-extract PHP source using Windows-compatible method
        $this->info('Pre-extracting PHP source to avoid Windows tar issues...');
        $this->extractPhpSourceWindows($spcPath);

        // CRITICAL: Pre-extract PECL extensions (sqlsrv, pdo_sqlsrv, etc.) to avoid Windows tar issues
        $this->info('Pre-extracting PECL extensions...');
        $this->extractPeclExtensionsWindows($spcPath, $this->selectedExtensions);

        // Download micro SAPI (required for building)
        $this->info('Downloading micro SAPI...');
        $microResult = Process::path($spcPath)
            ->env($this->getSpcEnvironment())
            ->run('php bin/spc download micro');

        if (!$microResult->successful()) {
            throw new RuntimeException('Failed to download micro SAPI');
        }

        // CRITICAL: Handle git-based dependencies before downloading other components
        $this->info('Ensuring git-based dependencies are available...');
        $this->ensureGitDependencies($spcPath);

        // Download required extension sources (only for extensions that need separate source downloads)
        $this->info('Downloading extension sources (where required)...');
        $skippedExtensions = [];
        
        foreach ($this->selectedExtensions as $ext) {
            // Skip core/built-in extensions that don't need separate downloads
            // These extensions are either built into PHP core or use system libraries
            if (in_array($ext, [
                // Core PHP extensions (built-in)
                'pdo', 'mbstring', 'fileinfo', 'tokenizer', 'filter', 'session', 
                'ctype', 'openssl', 'curl', 'zip', 'zlib', 'iconv',
                
                // Extensions that use system libraries (libraries downloaded separately)
                'sqlite3', 'pdo_sqlite', 'bcmath', 'dom', 'xml', 'simplexml', 
                'phar', 'sockets', 'bz2', 'opcache', 'gd'
            ])) {
                $skippedExtensions[] = $ext;
                continue;
            }

            $this->info("Downloading {$ext} extension source...");
            $maxRetries = 3;
            $downloaded = false;

            for ($attempt = 1; $attempt <= $maxRetries && !$downloaded; $attempt++) {
                $this->line("Attempt {$attempt} of {$maxRetries}...");

                $downloadResult = Process::path($spcPath)
                    ->timeout(300)
                    ->env($this->getSpcEnvironment())
                    ->run("php bin/spc download {$ext}");

                if ($downloadResult->successful()) {
                    $downloaded = true;
                    $this->info("✅ Successfully downloaded {$ext}");
                    break;
                }

                if ($attempt === $maxRetries) {
                    if (!$this->confirm("Failed to download {$ext}. Continue anyway?")) {
                        throw new RuntimeException("Cannot continue without {$ext}");
                    }
                } else {
                    $this->warn("Download failed, retrying...");
                    sleep(2);
                }
            }
        }

        // Display information about skipped extensions
        if (!empty($skippedExtensions)) {
            $this->info('ℹ️  Skipped extension source downloads (using core/built-in): ' . implode(', ', $skippedExtensions));
            $this->line('These extensions are built into PHP core or will use system libraries downloaded separately.');
        }

        // Download each required library (these provide the underlying functionality for extensions)
        $this->info('Downloading required libraries for extensions...');
        $this->line('Libraries provide the underlying functionality for extensions like openssl, curl, sqlite, etc.');
        $this->line('🔄 Enhanced download with GitHub fallback: If standard download fails, will automatically clone from GitHub.');
        $this->displayLibraryExtensionMapping();

        foreach ($this->requiredLibraries as $lib) {
            $this->info("Downloading library: {$lib}...");
            $downloaded = $this->downloadLibraryWithFallback($spcPath, $lib);

            if (!$downloaded) {
                if (!$this->confirm("Failed to download {$lib} using all methods. Continue anyway?")) {
                    throw new RuntimeException("Cannot continue without {$lib}");
                }
            }
        }

        // CRITICAL: Ensure tar-based extractions are complete AFTER downloading libraries
        $this->info('Verifying tar-based extractions...');
        $this->ensureTarBasedExtractions($spcPath);

        // CRITICAL: Pre-extract problematic libraries using Python (symlink-safe, Windows-compatible)
        $this->extractLibrariesWindows($spcPath);
    }

    protected function cleanBuildArtifacts(string $spcPath): void
    {
        $buildrootPath = $spcPath . '/buildroot';
        if (is_dir($buildrootPath)) {
            $this->info('Cleaning buildroot directory...');
            Process::run('rm -rf "' . $buildrootPath . '"');
        }
    }

    protected function verifyDownloadedSources(string $spcPath): void
    {
        $this->info('Checking downloaded sources...');

        // List all downloaded sources
        $listResult = Process::path($spcPath)->run('php bin/spc list sources');

        if ($listResult->successful()) {
            $this->line('Available sources:');
            $this->line($listResult->output());
        }

        // Check specifically for required sources
        $requiredSources = ['php-src', 'micro'];
        foreach ($requiredSources as $source) {
            $checkResult = Process::path($spcPath)->run("php bin/spc list sources | findstr {$source}");

            if ($checkResult->successful() && !empty(trim($checkResult->output()))) {
                $this->info("✅ {$source} is available");
            } else {
                $this->warn("⚠️ {$source} may not be available");

                // Try to download it again
                $this->info("Attempting to download {$source}...");
                $redownloadResult = Process::path($spcPath)->run("php bin/spc download {$source}");

                if ($redownloadResult->successful()) {
                    $this->info("✅ Successfully downloaded {$source}");
                } else {
                    $this->error("❌ Failed to download {$source}");
                    $this->error($redownloadResult->errorOutput());
                }
            }
        }

        // Verify git dependencies are present
        $this->info('Verifying git dependencies...');
        $this->verifyGitDependencies($spcPath);

        // Verify tar-based extractions are complete
        $this->info('Verifying tar-based extractions...');
        $this->ensureTarBasedExtractions($spcPath);
    }

    protected function verifyGitDependencies(string $spcPath): void
    {
        $sourcePath = $spcPath . '/source';
        $sourceJsonPath = $spcPath . '/config/source.json';

        if (!file_exists($sourceJsonPath)) {
            $this->warn('source.json not found, skipping git dependency verification');
            return;
        }

        $sourceConfig = json_decode(file_get_contents($sourceJsonPath), true);
        if (!$sourceConfig) {
            $this->warn('Failed to parse source.json');
            return;
        }

        // Find all git-type dependencies
        $gitDependencies = [];
        foreach ($sourceConfig as $name => $config) {
            if (isset($config['type']) && $config['type'] === 'git') {
                $gitDependencies[] = $name;
            }
        }

        if (empty($gitDependencies)) {
            $this->info('No git dependencies to verify');
            return;
        }

        foreach ($gitDependencies as $name) {
            $dependencyPath = $sourcePath . '/' . $name;

            if (file_exists($dependencyPath) && file_exists($dependencyPath . '/.git')) {
                $this->info("✅ Git dependency {$name} is present");
            } else {
                $this->warn("⚠️ Git dependency {$name} is missing or invalid");

                // Try to ensure it again
                $this->info("Attempting to fix {$name}...");
                $this->ensureGitDependencies($spcPath);
                break; // Re-run the whole check
            }
        }
    }
    /**
     * Get the standard environment variables for SPC processes
     */
    protected function getSpcEnvironment(): array
    {
        return [
            'PATH' => getenv('PATH') . ';C:\Program Files\Git\usr\bin',
            'SPC_CONCURRENCY' => '1',
            'CMAKE_BUILD_PARALLEL_LEVEL' => '1',
            'VS_PATH' => 'C:\Program Files (x86)\Microsoft Visual Studio\2022\BuildTools\VC\Tools\MSVC\14.41.34120\bin\Hostx64\x64',
            // Completely suppress Perl locale warnings by using Windows locale
            'LC_ALL' => 'English_United States.1252',
            'LC_CTYPE' => 'English_United States.1252',
            'LC_NUMERIC' => 'English_United States.1252',
            'LC_TIME' => 'English_United States.1252',
            'LC_COLLATE' => 'English_United States.1252',
            'LC_MONETARY' => 'English_United States.1252',
            'LC_MESSAGES' => 'English_United States.1252',
            'LANG' => 'English_United States.1252',
            'LANGUAGE' => 'en',
            // Perl-specific environment variables to suppress warnings
            'PERL_BADLANG' => '0',
            'PERL_UNICODE' => '',
            'PERL_USE_UNSAFE_INC' => '0',
            // Leave MSYS path conversion enabled so SPC tar commands handle Windows drive paths
            // Suppress other build warnings
            'CMAKE_GENERATOR_PLATFORM' => 'x64',
            'CMAKE_BUILD_TYPE' => 'Release',
            // Additional environment variables to reduce verbosity
            'CMAKE_VERBOSE_MAKEFILE' => 'OFF',
            'VERBOSE' => '0'
        ];
    }

    protected function buildPhpWithExtensions(string $spcPath): bool
    {
        // Build with selected extensions
        $extensions = implode(',', $this->selectedExtensions);

        $this->info("Building with selected extensions: {$extensions}");

        // Build command without --with-php (that's used in download phase)
        $buildCmd = "php bin/spc build \"{$extensions}\" --build-cli --debug";
        $this->line("Running build command: {$buildCmd}");

        $buildProcess = Process::path($spcPath)
            ->timeout(7200) // 2 hour timeout for full build
            ->env($this->getSpcEnvironment())
            ->run($buildCmd);

        if (!$buildProcess->successful()) {
            $this->error("Build failed. Debug output:");
            $this->error($buildProcess->output());
            $this->error($buildProcess->errorOutput());

            // Check if the built PHP binary exists even if build "failed"
            $phpBinaryPath = $spcPath . '/buildroot/bin/php.exe';
            if (file_exists($phpBinaryPath)) {
                $this->info("PHP binary was created despite error. Testing functionality...");

                $testResult = Process::path($spcPath)
                    ->run('buildroot\bin\php.exe -v');

                if ($testResult->successful()) {
                    $this->info("PHP version test output: " . $testResult->output());

                    // Test essential functions
                    $functionsTest = Process::path($spcPath)
                        ->run('buildroot\bin\php.exe -r "echo \"Basic: \" . (function_exists(\'strlen\') ? \'OK\' : \'FAIL\') . \"\nPDO: \" . (class_exists(\'PDO\') ? \'OK\' : \'FAIL\') . \"\nSQLite: \" . (class_exists(\'PDO\') && in_array(\'sqlite\', PDO::getAvailableDrivers()) ? \'OK\' : \'FAIL\') . \"\\n\";"');

                    if ($functionsTest->successful()) {
                        $this->info("Function test output:");
                        $this->info($functionsTest->output());

                        if (strpos($functionsTest->output(), 'Basic: OK') !== false) {
                            $this->info("✅ Build appears successful!");
                            return true;
                        }
                    }
                }
            }

            return false;
        }

        $this->info("✅ Build succeeded!");
        return true;
    }

    protected function validateEnvironment(): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            throw new RuntimeException('This command is currently only supported on Windows.');
        }

        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            throw new RuntimeException('PHP >= 8.1 required.');
        }

        foreach (['mbstring', 'tokenizer'] as $ext) {
            if (!extension_loaded($ext)) {
                throw new RuntimeException("PHP Extension {$ext} is required.");
            }
        }

        // Check for Visual Studio
        if (!file_exists('C:\\Program Files (x86)\\Microsoft Visual Studio\\Installer\\vswhere.exe')) {
            throw new RuntimeException("Visual Studio 2022 with C++ workload and SDKs must be installed.");
        }
    }

    protected function getUserPreferences(): void
    {
        // Get PHP version from option or prompt user
        $phpVersion = $this->option('php-version');
        if (!$phpVersion) {
            $this->info('Available PHP versions: 8.1, 8.2, 8.3');
            $this->warn('Note: PHP 8.4 does not support SQL Server extensions (sqlsrv, pdo_sqlsrv)');

            $phpVersion = $this->choice(
                'Select PHP version to build:',
                ['8.1', '8.2', '8.3'],
                '8.3'
            );
        }

        if (!in_array($phpVersion, ['8.1', '8.2', '8.3'])) {
            throw new RuntimeException('Unsupported PHP version. Supported versions: 8.1, 8.2, 8.3');
        }

        $this->selectedPhpVersion = $phpVersion;
        $this->info("Selected PHP version: {$phpVersion}");

        // Get extensions from option or prompt user
        $extensionsInput = $this->option('extensions');
        if ($extensionsInput) {
            $requestedExtensions = array_map('trim', explode(',', $extensionsInput));
        } else {
            $this->info('');
            $this->info('Building a custom PHP binary with your selected database and extension support...');
            $requestedExtensions = $this->promptForExtensions();
        }

        // Filter extensions based on PHP version compatibility
        $this->selectedExtensions = $this->filterExtensionsByPhpVersion($requestedExtensions);

        // Always include default extensions (these are core extensions needed for most PHP applications)
        $this->selectedExtensions = array_unique(array_merge($this->defaultExtensions, $this->selectedExtensions));

        $this->info('Including default extensions: ' . implode(', ', $this->defaultExtensions));

        // Calculate required libraries
        $this->calculateRequiredLibraries();

        $this->info('Selected extensions: ' . implode(', ', $this->selectedExtensions));
        $this->info('Required libraries: ' . implode(', ', $this->requiredLibraries));
    }

    protected function promptForExtensions(): array
    {
        $this->info('Select database types to include:');
        $this->line('Note: SQLite is always included by default');

        $databaseTypes = [
            'mysql' => 'MySQL (includes mysqli + pdo_mysql)',
            'postgres' => 'PostgreSQL (includes pgsql + pdo_pgsql)',
        ];

        // Add SQL Server option only for compatible PHP versions
        if (version_compare($this->selectedPhpVersion, '8.4', '<')) {
            $databaseTypes['sqlserver'] = 'SQL Server (includes sqlsrv + pdo_sqlsrv)';
        }

        $selectedDatabases = [];

        foreach ($databaseTypes as $type => $description) {
            $include = $this->confirm("Include {$description}?", false);
            if ($include) {
                $selectedDatabases[] = $type;
            }
        }

        // Convert database types to actual extensions
        $selectedExtensions = $this->mapDatabaseTypesToExtensions($selectedDatabases);

        if (empty($selectedDatabases)) {
            $this->info('No additional database types selected. SQLite will be included by default.');
        }

        $this->info('');
        $this->info('Note: The following extensions are included by default:');
        $this->line('  Core: ' . implode(', ', $this->defaultExtensions));

        // Ask for additional optional extensions
        $this->info('');
        $this->info('Optional additional extensions:');
        $additionalOptions = [
            'none' => 'No additional extensions',
            'web' => 'Web Development Pack (dom, xml, simplexml, gd)',
            'performance' => 'Performance Pack (opcache, phar)',
            'processing' => 'Text Processing Pack (iconv, ctype, bcmath)',
            'compression' => 'Compression Pack (bz2)',
            'network' => 'Network Pack (sockets)',
            'soap' => 'SOAP - Protocol support only',
            'all' => 'All extension packs above',
        ];

        // Display options with numbers
        $this->info('Available extension packs:');
        $optionKeys = array_keys($additionalOptions);
        foreach ($optionKeys as $index => $key) {
            $this->line("  [{$index}] {$key} - {$additionalOptions[$key]}");
        }

        $selectedPacks = $this->promptForMultipleSelections($optionKeys, $additionalOptions);
        
        // Process selected packs
        foreach ($selectedPacks as $pack) {
            if ($pack === 'none') {
                continue;
            } elseif ($pack === 'all') {
                // Add all extension packs except 'none' and 'all'
                $allPacks = array_diff($optionKeys, ['none', 'all']);
                foreach ($allPacks as $allPack) {
                    $packExtensions = $this->getExtensionPack($allPack);
                    $selectedExtensions = array_merge($selectedExtensions, $packExtensions);
                }
                break; // No need to process other selections if 'all' is selected
            } else {
                $packExtensions = $this->getExtensionPack($pack);
                $selectedExtensions = array_merge($selectedExtensions, $packExtensions);
            }
        }

        // Ask if user wants to add individual extensions
        if (!in_array('all', $selectedPacks) && $this->confirm('Would you like to add individual extensions?', false)) {
            $individualExtensions = $this->promptForIndividualExtensions();
            $selectedExtensions = array_merge($selectedExtensions, $individualExtensions);
        }

        return $selectedExtensions;
    }

    protected function mapDatabaseTypesToExtensions(array $databaseTypes): array
    {
        $extensions = [];

        foreach ($databaseTypes as $type) {
            switch ($type) {
                case 'mysql':
                    $extensions = array_merge($extensions, ['mysqli', 'pdo_mysql']);
                    $this->info('Adding MySQL extensions: mysqli, pdo_mysql');
                    break;

                case 'postgres':
                    $extensions = array_merge($extensions, ['pgsql']);
                    // Only add pdo_pgsql if it's available (it's not in static-php-cli by default)
                    if (isset($this->availableExtensions['pdo_pgsql'])) {
                        $extensions[] = 'pdo_pgsql';
                        $this->info('Adding PostgreSQL extensions: pgsql, pdo_pgsql');
                    } else {
                        $this->info('Adding PostgreSQL extensions: pgsql');
                        $this->warn('Note: pdo_pgsql may not be available in static-php-cli');
                    }
                    break;

                case 'sqlserver':
                    if (version_compare($this->selectedPhpVersion, '8.4', '<')) {
                        $extensions = array_merge($extensions, ['sqlsrv', 'pdo_sqlsrv']);
                        $this->info('Adding SQL Server extensions: sqlsrv, pdo_sqlsrv');
                    } else {
                        $this->warn('SQL Server extensions are not supported in PHP 8.4+');
                    }
                    break;
            }
        }

        return $extensions;
    }

    protected function filterExtensionsByPhpVersion(array $requestedExtensions): array
    {
        $validExtensions = [];

        foreach ($requestedExtensions as $ext) {
            if (!isset($this->availableExtensions[$ext])) {
                $this->warn("Extension '{$ext}' is not available. Skipping...");
                continue;
            }

            $extInfo = $this->availableExtensions[$ext];
            if (!in_array($this->selectedPhpVersion, $extInfo['php_versions'])) {
                $this->warn("Extension '{$ext}' is not compatible with PHP {$this->selectedPhpVersion}. Skipping...");
                continue;
            }

            $validExtensions[] = $ext;
        }

        return $validExtensions;
    }

    protected function calculateRequiredLibraries(): void
    {
        // Base libraries required for default extensions
        $this->requiredLibraries = [
            'zlib',           // zlib extension + zip dependency
            'sqlite',         // sqlite3 + pdo_sqlite
            'openssl',        // openssl extension + curl dependency
            'libxml2',        // dom, simplexml, xml extensions
            'libzip',         // zip extension
            'libpng',         // gd extension  
            'libjpeg',        // gd extension
            'freetype',       // gd extension
            'bzip2',          // bz2 extension
            'curl',           // curl extension
            'libcurl',        // curl extension
            'nghttp2',        // curl extension
            'libssh2',        // curl extension
            'xz',             // zip extension + xlswriter dependency
            'libwebp'         // gd extension (optional but recommended)
        ];

        // Add libraries for user-selected extensions
        foreach ($this->selectedExtensions as $ext) {
            if (isset($this->availableExtensions[$ext])) {
                $libraries = $this->availableExtensions[$ext]['libraries'];
                $this->requiredLibraries = array_merge($this->requiredLibraries, $libraries);
            }
        }

        $this->requiredLibraries = array_unique($this->requiredLibraries);
    }

    protected function promptForMultipleSelections(array $options, array $descriptions): array
    {
        $maxRetries = 3;
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            $this->info('');
            $this->info('You can select multiple extension packs by:');
            $this->line('  - Entering numbers separated by commas (e.g., 1,2,3,4,5,6)');
            $this->line('  - Entering individual numbers (e.g., 1)');  
            $this->line('  - Entering "all" to select all packs');
            $this->line('  - Pressing Enter for none (default)');
            
            if ($attempt > 0) {
                $this->warn("Attempt " . ($attempt + 1) . " of {$maxRetries}. Please try again.");
            }
            
            $input = $this->ask('Select extension pack(s)', 'none');
            $input = trim($input);
            
            if (empty($input) || $input === 'none' || $input === '0') {
                return ['none'];
            }
            
            if ($input === 'all' || $input === '7') {
                return ['all'];
            }
            
            // Handle comma-separated values or single values
            $selections = [];
            $invalidSelections = [];
            $inputParts = array_map('trim', explode(',', $input));
            
            foreach ($inputParts as $part) {
                // Check if it's a number
                if (is_numeric($part)) {
                    $index = (int)$part;
                    if (isset($options[$index])) {
                        $selections[] = $options[$index];
                    } else {
                        $invalidSelections[] = $part;
                    }
                } else {
                    // Check if it's a valid option name
                    if (in_array($part, $options)) {
                        $selections[] = $part;
                    } else {
                        $invalidSelections[] = $part;
                    }
                }
            }
            
            if (!empty($invalidSelections)) {
                $this->error("Invalid selections: " . implode(', ', $invalidSelections));
                $this->line("Valid options are: 0-" . (count($options) - 1) . " or their names: " . implode(', ', $options));
                $attempt++;
                continue;
            }
            
            if (empty($selections)) {
                $this->warn('No valid selections made.');
                if ($attempt < $maxRetries - 1) {
                    $attempt++;
                    continue;
                } else {
                    $this->warn('Using default: none');
                    return ['none'];
                }
            }
            
            // Display what was selected
            $selectedNames = array_map(function($key) use ($descriptions) {
                return $descriptions[$key] ?? $key;
            }, $selections);
            
            $this->info('✅ Selected extension packs: ' . implode(', ', $selectedNames));
            
            return array_unique($selections);
        }
        
        $this->warn('Maximum attempts reached. Using default: none');
        return ['none'];
    }

    protected function promptForIndividualExtensions(): array
    {
        // Get additional extensions (not already in default extensions)
        $additionalExtensions = [];
        foreach ($this->availableExtensions as $ext => $info) {
            if (!in_array($ext, $this->defaultExtensions) && 
                in_array($this->selectedPhpVersion, $info['php_versions'])) {
                $additionalExtensions[$ext] = $info['description'];
            }
        }

        if (empty($additionalExtensions)) {
            $this->warn('No additional extensions available for PHP ' . $this->selectedPhpVersion);
            return [];
        }

        $this->info('Available individual extensions (not in default or database packs):');
        $extensionKeys = array_keys($additionalExtensions);
        foreach ($extensionKeys as $index => $ext) {
            $this->line("  [{$index}] {$ext} - {$additionalExtensions[$ext]}");
        }

        $this->info('');
        $this->info('Select individual extensions by numbers separated by commas (e.g., 0,2,5)');
        $input = $this->ask('Select individual extensions (Enter for none)', '');
        
        if (empty(trim($input))) {
            return [];
        }

        $selections = [];
        $inputParts = array_map('trim', explode(',', $input));
        
        foreach ($inputParts as $part) {
            if (is_numeric($part)) {
                $index = (int)$part;
                if (isset($extensionKeys[$index])) {
                    $selections[] = $extensionKeys[$index];
                } else {
                    $this->warn("Invalid selection: {$part}. Skipping...");
                }
            }
        }

        if (!empty($selections)) {
            $this->info('Selected individual extensions: ' . implode(', ', $selections));
        }

        return $selections;
    }

    protected function displayLibraryExtensionMapping(): void
    {
        $this->line('Key library → extension mappings:');
        $this->line('  • openssl library → openssl extension (SSL/TLS support)');
        $this->line('  • curl/libcurl libraries → curl extension (HTTP client)');
        $this->line('  • sqlite library → sqlite3, pdo_sqlite extensions'); 
        $this->line('  • zlib library → zip, zlib extensions (compression)');
        $this->line('  • libxml2 library → dom, xml, simplexml extensions');
        $this->line('  • libpng, libjpeg libraries → gd extension (image processing)');
        $this->info('');
    }

    protected function getExtensionPack(string $pack): array
    {
        switch ($pack) {
            case 'web':
                return ['dom', 'xml', 'simplexml', 'gd'];

            case 'performance':
                return ['opcache', 'phar'];

            case 'processing':
                return ['iconv', 'ctype', 'bcmath'];

            case 'compression':
                return ['bz2'];

            case 'network':
                return ['sockets'];

            case 'soap':
                return ['soap'];

            default:
                return [];
        }
    }

    protected function displayBuildSummary(string $spcPath): void
    {
        $this->info('=== Build Summary ===');
        $this->info("PHP Version: {$this->selectedPhpVersion}");

        // Show default extensions
        $this->info('Default Extensions (always included): ' . implode(', ', $this->defaultExtensions));

        // Group user-selected extensions by type
        $userSelectedExtensions = array_diff($this->selectedExtensions, $this->defaultExtensions);

        if (!empty($userSelectedExtensions)) {
            $databaseExtensions = array_intersect($userSelectedExtensions, [
                'mysqli',
                'pdo_mysql',
                'pgsql',
                'pdo_pgsql',
                'sqlsrv',
                'pdo_sqlsrv'
            ]);
            $otherExtensions = array_diff($userSelectedExtensions, $databaseExtensions);

            if (!empty($databaseExtensions)) {
                $this->info('Additional Database Extensions: ' . implode(', ', $databaseExtensions));
            }
            if (!empty($otherExtensions)) {
                $this->info('Additional Other Extensions: ' . implode(', ', $otherExtensions));
            }
        } else {
            $this->info('Additional Extensions: None');
        }

        $phpBinaryPath = $spcPath . '/buildroot/bin/php.exe';
        if (file_exists($phpBinaryPath)) {
            $this->info("PHP Binary: {$phpBinaryPath}");

            // Test the built PHP
            $versionResult = Process::path($spcPath)->run('buildroot\bin\php.exe -v');
            if ($versionResult->successful()) {
                $this->info('PHP Version Output:');
                $this->line($versionResult->output());
            }

            // Test extensions
            $extensionsResult = Process::path($spcPath)->run('buildroot\bin\php.exe -m');
            if ($extensionsResult->successful()) {
                $this->info('Loaded Extensions:');
                $this->line($extensionsResult->output());
            }
        }
    }
    protected function ensureGitDependencies(string $spcPath): void
    {
        $sourcePath = $spcPath . '/source';
        $sourceJsonPath = $spcPath . '/config/source.json';

        // Read and parse source.json to find git dependencies
        if (!file_exists($sourceJsonPath)) {
            $this->warn('source.json not found, skipping git dependency check');
            return;
        }

        $sourceConfig = json_decode(file_get_contents($sourceJsonPath), true);
        if (!$sourceConfig) {
            $this->warn('Failed to parse source.json');
            return;
        }

        // Find all git-type dependencies
        $gitDependencies = [];
        foreach ($sourceConfig as $name => $config) {
            if (isset($config['type']) && $config['type'] === 'git') {
                $gitDependencies[$name] = $config;
            }
        }

        if (empty($gitDependencies)) {
            $this->info('No git dependencies found');
            return;
        }

        $this->info('Found ' . count($gitDependencies) . ' git dependencies');

        // Ensure source directory exists
        if (!file_exists($sourcePath)) {
            mkdir($sourcePath, 0755, true);
        }

        // Prioritize critical dependencies first
        $criticalDeps = ['libiconv-win', 'freetype'];
        $processedDeps = [];

        // Process critical dependencies first
        foreach ($criticalDeps as $critical) {
            if (isset($gitDependencies[$critical])) {
                $this->info("Processing critical dependency: {$critical}");
                $this->ensureGitDependency($sourcePath, $critical, $gitDependencies[$critical]);
                $processedDeps[] = $critical;
            }
        }

        // Process remaining dependencies (with timeout protection)
        foreach ($gitDependencies as $name => $config) {
            if (in_array($name, $processedDeps)) {
                continue; // Already processed
            }

            // Skip known problematic/large repositories that aren't essential for basic builds
            $skipForBasicBuild = ['grpc', 'protobuf', 'abseil-cpp', 'ext-glfw', 'pthreads4w'];
            if (in_array($name, $skipForBasicBuild)) {
                $this->warn("Skipping non-essential dependency for basic build: {$name}");
                continue;
            }

            try {
                $this->ensureGitDependency($sourcePath, $name, $config);
            } catch (\Exception $e) {
                $this->warn("Failed to process {$name}: " . $e->getMessage());
                $this->warn("Continuing without {$name} as it may not be essential for basic builds");
            }
        }
    }

    protected function ensureGitDependency(string $sourcePath, string $name, array $config): void
    {
        $targetPath = $sourcePath . '/' . $name;
        $url = $config['url'] ?? null;
        $rev = $config['rev'] ?? 'master';

        if (!$url) {
            $this->warn("No URL specified for git dependency: {$name}");
            return;
        }

        $this->info("Ensuring git dependency: {$name}");

        if (file_exists($targetPath)) {
            // Directory exists, check if it's a git repository
            if (file_exists($targetPath . '/.git')) {
                $this->info("  - {$name} already exists, updating...");

                // Try to update the repository
                $updateResult = Process::path($targetPath)->run('git pull origin ' . $rev);

                if ($updateResult->successful()) {
                    $this->info("  ✅ {$name} updated successfully");
                } else {
                    $this->warn("  ⚠️ Failed to update {$name}, but directory exists");
                }
            } else {
                $this->warn("  ⚠️ {$name} directory exists but is not a git repository");
                // Remove and re-clone
                Process::run('rm -rf "' . $targetPath . '"');
                $this->cloneGitDependency($url, $targetPath, $rev, $name);
            }
        } else {
            // Directory doesn't exist, clone it
            $this->info("  - Cloning {$name} from {$url}");
            $this->cloneGitDependency($url, $targetPath, $rev, $name);
        }
    }
    protected function cloneGitDependency(string $url, string $targetPath, string $rev, string $name): void
    {
        // Set timeout based on known problematic repositories
        $timeout = 300; // 5 minutes default
        $problemRepos = ['grpc', 'protobuf', 'abseil-cpp']; // Large repositories that take longer

        foreach ($problemRepos as $problemRepo) {
            if (strpos($name, $problemRepo) !== false || strpos($url, $problemRepo) !== false) {
                $timeout = 900; // 15 minutes for large repos
                $this->warn("  - {$name} is a large repository, using extended timeout ({$timeout}s)");
                break;
            }
        }

        // Clone the repository with specified timeout
        $cloneResult = Process::timeout($timeout)->run("git clone \"{$url}\" \"{$targetPath}\"");

        if (!$cloneResult->successful()) {
            if (strpos($cloneResult->errorOutput(), 'timeout') !== false) {
                $this->warn("  ⚠️ {$name} clone timed out after {$timeout}s, but this may not be critical for the build");
                return; // Don't throw exception for timeout, it might not be critical
            }
            throw new RuntimeException("Failed to clone {$name} from {$url}: " . $cloneResult->errorOutput());
        }

        // Checkout specific revision if not master/main
        if ($rev !== 'master' && $rev !== 'main') {
            $checkoutResult = Process::path($targetPath)->run("git checkout {$rev}");

            if (!$checkoutResult->successful()) {
                $this->warn("Failed to checkout {$rev} for {$name}, using default branch");
            }
        }

        $this->info("  ✅ {$name} cloned successfully");
    }
    protected function ensureTarBasedExtractions(string $spcPath): void
    {
        $sourcePath = $spcPath . '/source';
        $downloadsPath = $spcPath . '/downloads';
        $sourceJsonPath = $spcPath . '/config/source.json';

        if (!file_exists($sourceJsonPath)) {
            $this->warn('source.json not found, skipping tar extraction check');
            return;
        }

        $sourceConfig = json_decode(file_get_contents($sourceJsonPath), true);
        if (!$sourceConfig) {
            $this->warn('Failed to parse source.json');
            return;
        }

        // Find tar-based dependencies that might have failed extraction
        $tarDependencies = [];
        foreach ($sourceConfig as $name => $config) {
            if (isset($config['type']) && in_array($config['type'], ['url', 'ghrel'])) {
                $tarDependencies[$name] = $config;
            }
        }

        // Specifically handle nghttp2 and other critical tar-based dependencies
        $criticalTarDeps = ['nghttp2', 'libssh2', 'openssl', 'zlib', 'sqlite', 'bzip2', 'curl', 'libpng', 'libjpeg', 'libzip', 'xz', 'libwebp', 'libxml2'];

        foreach ($criticalTarDeps as $depName) {
            if (!isset($tarDependencies[$depName])) {
                continue;
            }

            $targetPath = $sourcePath . '/' . $depName;

            // Skip if this dependency was cloned from GitHub/Git (no extraction needed)
            if ($this->isClonedFromGit($targetPath)) {
                $this->info("✅ {$depName} was cloned from repository, skipping tar extraction check");
                continue;
            }

            // Check if extraction is incomplete (only has .spc-hash and build directory)
            if (file_exists($targetPath)) {
                $contents = scandir($targetPath);
                $realContents = array_diff($contents, ['.', '..']);

                $isIncomplete = false;

                // If directory only contains .spc-hash and build/, it's incomplete
                if (
                    count($realContents) <= 2 &&
                    (in_array('.spc-hash', $realContents) || in_array('build', $realContents))
                ) {
                    $isIncomplete = true;
                }

                // Check for essential files based on dependency type
                if (!$isIncomplete) {
                    $expectedFiles = $this->getExpectedFilesForDependency($depName);
                    if (!empty($expectedFiles)) {
                        $missingFiles = [];
                        foreach ($expectedFiles as $expectedFile) {
                            if (!in_array($expectedFile, $realContents) && !file_exists($targetPath . '/' . $expectedFile)) {
                                $missingFiles[] = $expectedFile;
                            }
                        }
                        if (!empty($missingFiles)) {
                            $this->warn("Missing essential files for {$depName}: " . implode(', ', $missingFiles));
                            $isIncomplete = true;
                        }
                    }
                }

                if ($isIncomplete) {
                    $this->warn("Detected incomplete extraction for {$depName}, re-extracting...");
                    $this->reExtractTarDependency($downloadsPath, $sourcePath, $depName);
                }
            } else {
                // Directory doesn't exist, try to extract if archive is available
                $this->info("Missing {$depName} source, attempting extraction...");
                $this->reExtractTarDependency($downloadsPath, $sourcePath, $depName);
            }
        }
    }

    protected function isClonedFromGit(string $targetPath): bool
    {
        // Check for our marker files that indicate the source was cloned
        return file_exists($targetPath . '/.cloned-from-github') ||
               file_exists($targetPath . '/.cloned-from-git') ||
               (file_exists($targetPath . '/.git') && !file_exists($targetPath . '/.spc-hash'));
    }

    protected function reExtractTarDependency(string $downloadsPath, string $sourcePath, string $depName): void
    {
        $targetPath = $sourcePath . '/' . $depName;

        // Remove incomplete directory if it exists
        if (file_exists($targetPath)) {
            $this->info("  - Removing incomplete {$depName} directory...");
            Process::run('rm -rf "' . $targetPath . '"');
        }

        // Look for tar archive in downloads
        $possibleArchives = [
            $depName . '*.tar.xz',
            $depName . '*.tar.gz',
            $depName . '*.tgz'
        ];

        // Special patterns for some dependencies
        if ($depName === 'sqlite') {
            $possibleArchives = array_merge($possibleArchives, [
                'sqlite-autoconf*.tar.gz',
                'sqlite-autoconf*.tar.xz'
            ]);
        } elseif ($depName === 'libjpeg') {
            $possibleArchives = array_merge($possibleArchives, [
                'libjpeg-turbo*.tar.gz',
                'libjpeg-turbo*.tar.xz'
            ]);
        } elseif ($depName === 'libwebp') {
            $possibleArchives = array_merge($possibleArchives, [
                'v*.tar.gz',  // libwebp uses v1.3.2.tar.gz pattern
                'libwebp-*.tar.gz',
                'libwebp-*.tar.xz'
            ]);
        } elseif ($depName === 'libxml2') {
            $possibleArchives = array_merge($possibleArchives, [
                'v*.tar.gz',  // libxml2 uses v2.12.5.tar.gz pattern
                'libxml2-v*.tar.gz',
                'libxml2-*.tar.gz',
                'libxml2-*.tar.xz'
            ]);
        }

        $archiveFile = null;
        foreach ($possibleArchives as $pattern) {
            $matches = glob($downloadsPath . '/' . $pattern);
            if (!empty($matches)) {
                $archiveFile = $matches[0]; // Use the first match
                $this->info("  - Found archive: " . basename($archiveFile) . " using pattern: {$pattern}");
                break;
            }
        }

        // Special handling for dependencies with version-only names
        if (!$archiveFile) {
            // Check for version-only archives (like v1.3.2.tar.gz for libwebp, v2.12.5.tar.gz for libxml2)
            $versionArchives = glob($downloadsPath . '/v*.tar.gz');
            if (!empty($versionArchives)) {
                // Try to match based on common version patterns
                foreach ($versionArchives as $versionArchive) {
                    $basename = basename($versionArchive);
                    if ($depName === 'libwebp' && preg_match('/^v1\.\d+\.\d+\.tar\.gz$/', $basename)) {
                        $archiveFile = $versionArchive;
                        $this->info("  - Found libwebp archive: {$basename}");
                        break;
                    } elseif ($depName === 'libxml2' && preg_match('/^v2\.\d+\.\d+\.tar\.gz$/', $basename)) {
                        $archiveFile = $versionArchive;
                        $this->info("  - Found libxml2 archive: {$basename}");
                        break;
                    }
                }
            }
        }

        if (!$archiveFile || !file_exists($archiveFile)) {
            $this->warn("  - No archive found for {$depName} in downloads directory");
            return;
        }

        $this->info("  - Extracting {$depName} from " . basename($archiveFile));

        // Create target directory first
        if (!file_exists($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        // Convert Windows paths to avoid tar issues with drive letters
        $winArchiveFile = str_replace('\\', '/', $archiveFile);
        $winTargetPath = str_replace('\\', '/', $targetPath);

        // Use appropriate extraction command based on file extension with Windows-compatible paths
        $extractCmd = '';
        if (str_ends_with($archiveFile, '.tar.xz')) {
            $extractCmd = "tar -xf \"{$winArchiveFile}\" -C \"{$winTargetPath}\" --strip-components=1";
        } elseif (str_ends_with($archiveFile, '.tar.gz') || str_ends_with($archiveFile, '.tgz')) {
            $extractCmd = "tar -xzf \"{$winArchiveFile}\" -C \"{$winTargetPath}\" --strip-components=1";
        }

        if (empty($extractCmd)) {
            $this->warn("  - Unknown archive format for {$depName}");
            return;
        }

        // Execute extraction with Windows PATH so tar resolves correctly
        $extractResult = Process::env([
            'PATH' => getenv('PATH')
        ])->run($extractCmd);

        if ($extractResult->successful()) {
            $this->info("  ✅ {$depName} extracted successfully");
            $this->verifyTarExtraction($targetPath, $depName);
        } else {
            $this->warn("  ⚠️ Failed to extract {$depName}: " . $extractResult->errorOutput());

            // Try with improved tar command for Windows
            $this->info("  - Trying Windows-optimized tar extraction...");
            $windowsOptimizedResult = $this->tryWindowsOptimizedExtraction($archiveFile, $targetPath, $depName);
            if (!$windowsOptimizedResult) {
                // Try with 7-zip if available (Windows fallback)
                $this->info("  - Trying 7-zip extraction method...");
                $sevenZipResult = $this->trySevenZipExtraction($archiveFile, $targetPath, $depName);

                if (!$sevenZipResult) {
                    // Try PowerShell extraction for .tar.gz files
                    $powerShellResult = $this->tryPowerShellExtraction($archiveFile, $targetPath, $depName);

                    if (!$powerShellResult) {
                        // Final fallback: try without strip-components
                        $this->info("  - Trying basic tar extraction...");
                        $basicCmd = '';
                        if (str_ends_with($archiveFile, '.tar.xz')) {
                            $basicCmd = "tar -xf \"{$winArchiveFile}\" -C \"{$winTargetPath}\"";
                        } elseif (str_ends_with($archiveFile, '.tar.gz') || str_ends_with($archiveFile, '.tgz')) {
                            $basicCmd = "tar -xzf \"{$winArchiveFile}\" -C \"{$winTargetPath}\"";
                        }

                        if (!empty($basicCmd)) {
                            $basicResult = Process::env([
                                'PATH' => getenv('PATH')
                            ])->run($basicCmd);

                            if ($basicResult->successful()) {
                                $this->info("  ✅ {$depName} extracted with basic method");
                                $this->handleBasicExtractionCleanup($targetPath, $depName);
                                $this->verifyTarExtraction($targetPath, $depName);
                            } else {
                                $this->error("  ❌ All extraction methods failed for {$depName}");
                            }
                        }
                    }
                }
            }
        }
    }

    protected function tryWindowsOptimizedExtraction(string $archiveFile, string $targetPath, string $depName): bool
    {
        // Convert to Unix-style paths to avoid MSYS2 path conversion issues
        $unixArchiveFile = str_replace('\\', '/', $archiveFile);
        $unixTargetPath = str_replace('\\', '/', $targetPath);

        // Convert Windows drive letters to Unix format (C:/ -> /c/)
        if (preg_match('/^([A-Za-z]):\/(.*)/', $unixArchiveFile, $matches)) {
            $unixArchiveFile = '/' . strtolower($matches[1]) . '/' . $matches[2];
        }
        if (preg_match('/^([A-Za-z]):\/(.*)/', $unixTargetPath, $matches)) {
            $unixTargetPath = '/' . strtolower($matches[1]) . '/' . $matches[2];
        }

        $this->info("  - Using Unix-style paths: {$unixArchiveFile} -> {$unixTargetPath}");

        // Use appropriate extraction command with Unix paths
        $extractCmd = '';
        if (str_ends_with($archiveFile, '.tar.xz')) {
            $extractCmd = "tar -xf '{$unixArchiveFile}' -C '{$unixTargetPath}' --strip-components=1";
        } elseif (str_ends_with($archiveFile, '.tar.gz') || str_ends_with($archiveFile, '.tgz')) {
            $extractCmd = "tar -xzf '{$unixArchiveFile}' -C '{$unixTargetPath}' --strip-components=1";
        }

        if (empty($extractCmd)) {
            return false;
        }

        $result = Process::env([
            'PATH' => getenv('PATH')
        ])->run($extractCmd);

        if ($result->successful()) {
            $this->info("  ✅ {$depName} extracted with Windows-optimized method");
            $this->verifyTarExtraction($targetPath, $depName);
            return true;
        } else {
            $this->warn("  ⚠️ Windows-optimized extraction failed: " . $result->errorOutput());
            return false;
        }
    }

    protected function verifyTarExtraction(string $targetPath, string $depName): void
    {
        if (!file_exists($targetPath)) {
            $this->warn("  ⚠️ {$depName} directory not found after extraction");
            return;
        }

        $contents = scandir($targetPath);
        $realContents = array_diff($contents, ['.', '..']);

        // Check for expected files based on dependency type
        $expectedFiles = $this->getExpectedFilesForDependency($depName);

        if (empty($expectedFiles)) {
            // Generic check - should have more than just .spc-hash and build
            if (count($realContents) > 2) {
                $this->info("  ✅ {$depName} appears to be properly extracted");
                return;
            }
        } else {
            $missingFiles = [];
            foreach ($expectedFiles as $expectedFile) {
                if (!in_array($expectedFile, $realContents) && !file_exists($targetPath . '/' . $expectedFile)) {
                    $missingFiles[] = $expectedFile;
                }
            }

            if (empty($missingFiles)) {
                $this->info("  ✅ {$depName} extraction verified - all expected files present");
            } else {
                $this->warn("  ⚠️ {$depName} may be incomplete - missing: " . implode(', ', $missingFiles));
            }
        }
    }

    protected function trySevenZipExtraction(string $archiveFile, string $targetPath, string $depName): bool
    {
        // Check if 7-zip is available
        $sevenZipPaths = [
            'C:\Program Files\7-Zip\7z.exe',
            'C:\Program Files (x86)\7-Zip\7z.exe',
            '7z' // If in PATH
        ];

        $sevenZipPath = null;
        foreach ($sevenZipPaths as $path) {
            if ($path === '7z' || file_exists($path)) {
                $sevenZipPath = $path;
                break;
            }
        }

        if (!$sevenZipPath) {
            $this->warn("  - 7-zip not found, skipping 7-zip extraction");
            return false;
        }

        $this->info("  - Using 7-zip for extraction...");
        $sevenZipResult = Process::run("\"{$sevenZipPath}\" x \"{$archiveFile}\" -o\"{$targetPath}\" -y");

        if ($sevenZipResult->successful()) {
            $this->info("  ✅ {$depName} extracted with 7-zip");
            $this->handleBasicExtractionCleanup($targetPath, $depName);
            $this->verifyTarExtraction($targetPath, $depName);
            return true;
        } else {
            $this->warn("  ⚠️ 7-zip extraction failed: " . $sevenZipResult->errorOutput());
            return false;
        }
    }
    protected function tryPowerShellExtraction(string $archiveFile, string $targetPath, string $depName): bool
    {
        $this->info("  - Trying PowerShell extraction...");

        // PowerShell command to extract archives
        if (str_ends_with($archiveFile, '.tar.gz') || str_ends_with($archiveFile, '.tgz')) {
            // For .tar.gz files, we need to extract the .gz first, then the .tar
            $tempTarFile = $targetPath . '\\' . basename($archiveFile, '.gz');

            $psCmd = "powershell -Command \"" .
                "Add-Type -AssemblyName System.IO.Compression.FileSystem; " .
                "\$gzStream = [System.IO.File]::OpenRead('{$archiveFile}'); " .
                "\$tarStream = [System.IO.File]::Create('{$tempTarFile}'); " .
                "\$gzipStream = New-Object System.IO.Compression.GzipStream(\$gzStream, [System.IO.Compression.CompressionMode]::Decompress); " .
                "\$gzipStream.CopyTo(\$tarStream); " .
                "\$gzipStream.Close(); \$tarStream.Close(); \$gzStream.Close(); " .
                "Write-Host 'Decompressed to {$tempTarFile}'\"";

            $result = Process::run($psCmd);

            if ($result->successful() && file_exists($tempTarFile)) {
                // Now extract the .tar file using tar
                $tarResult = Process::run("tar -xf \"{$tempTarFile}\" -C \"{$targetPath}\" --strip-components=1");

                // Clean up temp file
                unlink($tempTarFile);

                if ($tarResult->successful()) {
                    $this->info("  ✅ {$depName} extracted with PowerShell + tar");
                    return true;
                }
            }
        } elseif (str_ends_with($archiveFile, '.tar.xz')) {
            // For .tar.xz files, try using PowerShell with external tools or fall back to basic methods
            $this->warn("  - PowerShell extraction for .tar.xz not implemented, falling back...");
            return false;
        }

        return false;
    }

    protected function handleBasicExtractionCleanup(string $targetPath, string $depName): void
    {
        // When using basic extraction without --strip-components=1, we might get nested directories
        // Try to flatten the structure if there's only one top-level directory
        $contents = scandir($targetPath);
        $realContents = array_diff($contents, ['.', '..']);

        if (count($realContents) === 1) {
            $singleDir = $targetPath . DIRECTORY_SEPARATOR . reset($realContents);
            if (is_dir($singleDir)) {
                $this->info("  - Flattening directory structure for {$depName}...");

                // Move all contents from the nested directory to the target directory
                $nestedContents = scandir($singleDir);
                foreach ($nestedContents as $item) {
                    if ($item === '.' || $item === '..')
                        continue;

                    $sourcePath = $singleDir . DIRECTORY_SEPARATOR . $item;
                    $destPath = $targetPath . DIRECTORY_SEPARATOR . $item;

                    // Use move command
                    if (PHP_OS_FAMILY === 'Windows') {
                        Process::run("move \"{$sourcePath}\" \"{$destPath}\"");
                    } else {
                        Process::run("mv \"{$sourcePath}\" \"{$destPath}\"");
                    }
                }

                // Remove the empty nested directory
                if (PHP_OS_FAMILY === 'Windows') {
                    Process::run("rmdir \"{$singleDir}\"");
                } else {
                    Process::run("rmdir \"{$singleDir}\"");
                }
            }
        }
    }

    protected function downloadLibraryWithFallback(string $spcPath, string $lib): bool
    {
        $maxRetries = 2; // Reduced retries since we have fallback
        $downloaded = false;

        // STEP 1: Try standard SPC download method (2 attempts)
        for ($attempt = 1; $attempt <= $maxRetries && !$downloaded; $attempt++) {
            $this->line("  Standard download attempt {$attempt} of {$maxRetries}...");

            $downloadResult = Process::path($spcPath)
                ->timeout(300)
                ->env($this->getSpcEnvironment())
                ->run("php bin/spc download {$lib}");

            if ($downloadResult->successful()) {
                $downloaded = true;
                $this->info("  ✅ Successfully downloaded {$lib} via standard method");
                break;
            }

            if ($attempt < $maxRetries) {
                $this->warn("  ⚠️ Download failed, retrying...");
                sleep(2);
            } else {
                $this->warn("  ⚠️ Standard download failed after {$maxRetries} attempts");
            }
        }

        // STEP 2: If standard download failed, try GitHub fallback
        if (!$downloaded) {
            $this->info("  🔄 Attempting GitHub fallback for {$lib}...");
            $downloaded = $this->downloadFromGitHub($spcPath, $lib);
        }

        return $downloaded;
    }

    protected function downloadFromGitHub(string $spcPath, string $lib): bool
    {
        $sourceJsonPath = $spcPath . '/config/source.json';

        if (!file_exists($sourceJsonPath)) {
            $this->warn("  ⚠️ source.json not found, cannot determine GitHub repository for {$lib}");
            return false;
        }

        $sourceConfig = json_decode(file_get_contents($sourceJsonPath), true);
        if (!$sourceConfig || !isset($sourceConfig[$lib])) {
            $this->warn("  ⚠️ Library {$lib} not found in source.json");
            return false;
        }

        $libConfig = $sourceConfig[$lib];
        $libType = $libConfig['type'] ?? '';

        // Handle different GitHub-based source types
        if ($libType === 'ghrel' || $libType === 'ghtagtar' || $libType === 'ghtar') {
            return $this->cloneFromGitHubRepo($spcPath, $lib, $libConfig);
        } elseif ($libType === 'git') {
            return $this->cloneFromGitRepo($spcPath, $lib, $libConfig);
        } elseif ($libType === 'url') {
            // Check if URL is from GitHub and we can extract repo info
            $url = $libConfig['url'] ?? '';
            if ($this->isGitHubUrl($url)) {
                return $this->cloneFromGitHubUrl($spcPath, $lib, $url);
            } elseif (isset($libConfig['alt'])) {
                // Try alternative URL if available
                return $this->downloadFromAlternativeUrl($spcPath, $lib, $libConfig['alt']);
            }
        }

        $this->warn("  ⚠️ No GitHub fallback available for {$lib} (type: {$libType})");
        return false;
    }

    protected function cloneFromGitHubRepo(string $spcPath, string $lib, array $config): bool
    {
        $repo = $config['repo'] ?? '';
        if (empty($repo)) {
            $this->warn("  ⚠️ No repository specified for {$lib}");
            return false;
        }

        $sourcePath = $spcPath . '/source';
        $targetPath = $sourcePath . '/' . $lib;
        $repoUrl = "https://github.com/{$repo}.git";

        $this->info("  📦 Cloning {$lib} from GitHub: {$repo}");

        // Ensure source directory exists
        if (!file_exists($sourcePath)) {
            mkdir($sourcePath, 0755, true);
        }

        // Remove existing directory if it exists
        if (file_exists($targetPath)) {
            $this->info("  🗑️ Removing existing {$lib} directory...");
            Process::run('rm -rf "' . $targetPath . '"');
        }

        // Clone the repository
        $cloneResult = Process::timeout(600) // 10 minutes timeout for large repos
            ->run("git clone \"{$repoUrl}\" \"{$targetPath}\"");

        if (!$cloneResult->successful()) {
            $this->error("  ❌ Failed to clone {$lib} from GitHub: " . $cloneResult->errorOutput());
            return false;
        }

        $this->info("  ✅ Successfully cloned {$lib} from GitHub");

        // For GitHub repos, we don't need tar extraction since we cloned the source directly
        // Create a marker file to indicate this was cloned (not downloaded as tar)
        file_put_contents($targetPath . '/.cloned-from-github', json_encode([
            'library' => $lib,
            'repo' => $repo,
            'cloned_at' => date('Y-m-d H:i:s'),
            'method' => 'github_fallback'
        ]));

        $this->verifyClonedRepo($targetPath, $lib);
        return true;
    }

    protected function cloneFromGitRepo(string $spcPath, string $lib, array $config): bool
    {
        $url = $config['url'] ?? '';
        if (empty($url)) {
            $this->warn("  ⚠️ No URL specified for git repository {$lib}");
            return false;
        }

        $sourcePath = $spcPath . '/source';
        $targetPath = $sourcePath . '/' . $lib;

        $this->info("  📦 Cloning {$lib} from git repository: {$url}");

        // Ensure source directory exists
        if (!file_exists($sourcePath)) {
            mkdir($sourcePath, 0755, true);
        }

        // Remove existing directory if it exists
        if (file_exists($targetPath)) {
            $this->info("  🗑️ Removing existing {$lib} directory...");
            Process::run('rm -rf "' . $targetPath . '"');
        }

        // Clone the repository
        $cloneResult = Process::timeout(600)
            ->run("git clone \"{$url}\" \"{$targetPath}\"");

        if (!$cloneResult->successful()) {
            $this->error("  ❌ Failed to clone {$lib} from git: " . $cloneResult->errorOutput());
            return false;
        }

        // Handle specific revision/branch if specified
        $rev = $config['rev'] ?? '';
        if (!empty($rev) && $rev !== 'master' && $rev !== 'main') {
            $this->info("  🔀 Checking out revision: {$rev}");
            $checkoutResult = Process::path($targetPath)->run("git checkout {$rev}");

            if (!$checkoutResult->successful()) {
                $this->warn("  ⚠️ Failed to checkout {$rev}, using default branch");
            }
        }

        $this->info("  ✅ Successfully cloned {$lib} from git repository");

        // Create marker file for git repos too
        file_put_contents($targetPath . '/.cloned-from-git', json_encode([
            'library' => $lib,
            'url' => $url,
            'rev' => $rev,
            'cloned_at' => date('Y-m-d H:i:s'),
            'method' => 'git_fallback'
        ]));

        $this->verifyClonedRepo($targetPath, $lib);
        return true;
    }

    protected function downloadFromAlternativeUrl(string $spcPath, string $lib, array $altConfig): bool
    {
        if (!isset($altConfig['url'])) {
            $this->warn("  ⚠️ No alternative URL specified for {$lib}");
            return false;
        }

        $this->info("  🔄 Trying alternative URL for {$lib}: " . $altConfig['url']);

        // Try manual download from alternative URL and place in downloads folder
        $downloadsPath = $spcPath . '/downloads';
        if (!file_exists($downloadsPath)) {
            mkdir($downloadsPath, 0755, true);
        }

        $fileName = basename($altConfig['url']);
        $targetFile = $downloadsPath . '/' . $fileName;

        $downloadResult = Process::timeout(300)
            ->run("curl -L -o \"{$targetFile}\" \"{$altConfig['url']}\"");

        if ($downloadResult->successful()) {
            $this->info("  ✅ Successfully downloaded {$lib} from alternative URL");
            return true;
        } else {
            $this->warn("  ⚠️ Alternative URL download failed: " . $downloadResult->errorOutput());
            return false;
        }
    }

    protected function isGitHubUrl(string $url): bool
    {
        return strpos($url, 'github.com') !== false;
    }

    protected function cloneFromGitHubUrl(string $spcPath, string $lib, string $url): bool
    {
        // Extract repository info from GitHub URL
        // Examples:
        // https://github.com/GNOME/libxml2/archive/refs/tags/v2.12.5.tar.gz
        // https://github.com/user/repo.git

        $repoInfo = $this->extractGitHubRepoFromUrl($url);
        if (!$repoInfo) {
            $this->warn("  ⚠️ Could not extract repository info from GitHub URL: {$url}");
            return false;
        }

        // Use Windows-optimized repositories when available
        $originalRepoInfo = $repoInfo; // Save original for marker file
        $optimizedRepo = $this->getWindowsOptimizedRepo($lib, $repoInfo);
        if ($optimizedRepo) {
            $this->info("  🪟 Using Windows-optimized repository: {$optimizedRepo['owner']}/{$optimizedRepo['repo']}");
            $repoInfo = $optimizedRepo;
        }

        $sourcePath = $spcPath . '/source';
        $targetPath = $sourcePath . '/' . $lib;
        $repoUrl = "https://github.com/{$repoInfo['owner']}/{$repoInfo['repo']}.git";

        $this->info("  📦 Cloning {$lib} from GitHub URL: {$repoInfo['owner']}/{$repoInfo['repo']}");

        // Ensure source directory exists
        if (!file_exists($sourcePath)) {
            mkdir($sourcePath, 0755, true);
        }

        // Remove existing directory if it exists
        if (file_exists($targetPath)) {
            $this->info("  🗑️ Removing existing {$lib} directory...");
            Process::run('rm -rf "' . $targetPath . '"');
        }

        // Clone the repository
        $cloneResult = Process::timeout(600)
            ->run("git clone \"{$repoUrl}\" \"{$targetPath}\"");

        if (!$cloneResult->successful()) {
            $this->error("  ❌ Failed to clone {$lib} from GitHub: " . $cloneResult->errorOutput());
            return false;
        }

        // If there's a specific tag/version in the URL, try to checkout that tag
        if (isset($repoInfo['tag'])) {
            $this->info("  🔀 Checking out tag: {$repoInfo['tag']}");
            $checkoutResult = Process::path($targetPath)->run("git checkout {$repoInfo['tag']}");

            if (!$checkoutResult->successful()) {
                $this->warn("  ⚠️ Failed to checkout tag {$repoInfo['tag']}, using default branch");
            }
        }

        $this->info("  ✅ Successfully cloned {$lib} from GitHub URL");

        // Create marker file for GitHub URL repos
        $markerData = [
            'library' => $lib,
            'original_url' => $url,
            'repo_url' => $repoUrl,
            'owner' => $repoInfo['owner'],
            'repo' => $repoInfo['repo'],
            'tag' => $repoInfo['tag'] ?? null,
            'cloned_at' => date('Y-m-d H:i:s'),
            'method' => 'github_url_fallback'
        ];

        // Add Windows optimization info if applicable
        if ($optimizedRepo) {
            $markerData['windows_optimized'] = true;
            $markerData['optimization_reason'] = $optimizedRepo['reason'];
            $markerData['original_owner'] = $originalRepoInfo['owner'] ?? 'unknown';
            $markerData['original_repo'] = $originalRepoInfo['repo'] ?? 'unknown';
        }

        file_put_contents($targetPath . '/.cloned-from-github', json_encode($markerData, JSON_PRETTY_PRINT));

        $this->verifyClonedRepo($targetPath, $lib);
        return true;
    }

    protected function extractGitHubRepoFromUrl(string $url): ?array
    {
        // Pattern 1: https://github.com/owner/repo/archive/refs/tags/v2.12.5.tar.gz
        if (preg_match('#github\.com/([^/]+)/([^/]+)/archive/refs/tags/([^/]+)\.tar\.gz#', $url, $matches)) {
            return [
                'owner' => $matches[1],
                'repo' => $matches[2],
                'tag' => $matches[3]
            ];
        }

        // Pattern 2: https://github.com/owner/repo/archive/v2.12.5.tar.gz
        if (preg_match('#github\.com/([^/]+)/([^/]+)/archive/([^/]+)\.tar\.gz#', $url, $matches)) {
            return [
                'owner' => $matches[1],
                'repo' => $matches[2],
                'tag' => $matches[3]
            ];
        }

        // Pattern 3: https://github.com/owner/repo.git
        if (preg_match('#github\.com/([^/]+)/([^/]+)\.git#', $url, $matches)) {
            return [
                'owner' => $matches[1],
                'repo' => $matches[2]
            ];
        }

        // Pattern 4: Basic GitHub repo URL without .git
        if (preg_match('#github\.com/([^/]+)/([^/]+)/?$#', $url, $matches)) {
            return [
                'owner' => $matches[1],
                'repo' => $matches[2]
            ];
        }

        return null;
    }

    protected function getWindowsOptimizedRepo(string $lib, array $originalRepo): ?array
    {
        // Map libraries to their Windows-optimized alternatives
        $windowsRepos = [
            'libxml2' => [
                'owner' => 'winlibs',
                'repo' => 'libxml2',
                'reason' => 'Windows-optimized build for PHP with VC++ compilers'
            ],
            'libxslt' => [
                'owner' => 'winlibs',
                'repo' => 'libxslt',
                'reason' => 'Windows-optimized XSLT library'
            ],
            'libiconv' => [
                'owner' => 'winlibs',
                'repo' => 'libiconv',
                'reason' => 'Windows character encoding library'
            ],
            'zlib' => [
                'owner' => 'winlibs',
                'repo' => 'zlib',
                'reason' => 'Windows-optimized compression library'
            ]
        ];

        if (isset($windowsRepos[$lib])) {
            $optimized = $windowsRepos[$lib];

            // Preserve tag/version from original if available
            if (isset($originalRepo['tag'])) {
                $optimized['tag'] = $originalRepo['tag'];
            }

            $this->line("  💡 Found Windows-optimized alternative: {$optimized['reason']}");
            return $optimized;
        }

        return null;
    }

    protected function verifyClonedRepo(string $targetPath, string $lib): void
    {
        if (!file_exists($targetPath)) {
            $this->warn("  ⚠️ {$lib} directory not found after cloning");
            return;
        }

        $contents = scandir($targetPath);
        $realContents = array_diff($contents, ['.', '..']);

        // For cloned repos, we expect different files than extracted tars
        $essentialFiles = ['.git']; // Git repos should always have .git directory
        $expectedFiles = $this->getExpectedFilesForDependency($lib);

        if (!empty($expectedFiles)) {
            $essentialFiles = array_merge($essentialFiles, $expectedFiles);
        }

        $missingFiles = [];
        foreach ($essentialFiles as $file) {
            if (!in_array($file, $realContents) && !file_exists($targetPath . '/' . $file)) {
                $missingFiles[] = $file;
            }
        }

        if (empty($missingFiles)) {
            $this->info("  ✅ {$lib} repository verified - all essential files present");
        } else {
            $this->warn("  ⚠️ {$lib} repository may be incomplete - missing: " . implode(', ', $missingFiles));
        }

        // Show a few contents for confirmation
        $this->line("  📁 Repository contents preview: " . implode(', ', array_slice($realContents, 0, 5)) .
                   (count($realContents) > 5 ? '... (' . count($realContents) . ' total)' : ''));
    }

    protected function getExpectedFilesForDependency(string $depName): array
    {
        switch ($depName) {
            case 'sqlite':
                return ['sqlite3.c', 'sqlite3.h', 'Makefile'];
            case 'nghttp2':
                return ['CMakeLists.txt', 'lib', 'configure'];
            case 'openssl':
                return ['Configure', 'config', 'crypto'];
            case 'zlib':
                return ['CMakeLists.txt', 'configure', 'zlib.h'];
            case 'libssh2':
                return ['CMakeLists.txt', 'src', 'include'];
            case 'bzip2':
                return ['Makefile', 'bzlib.h', 'bzip2.c'];
            case 'curl':
                return ['CMakeLists.txt', 'configure', 'lib', 'src'];
            case 'libpng':
                return ['CMakeLists.txt', 'configure', 'png.h'];
            case 'libjpeg':
                return ['CMakeLists.txt', 'configure', 'jpeglib.h'];
            case 'libzip':
                return ['CMakeLists.txt', 'configure', 'lib'];
            case 'xz':
                return ['CMakeLists.txt', 'configure', 'src'];
            case 'libwebp':
                return ['CMakeLists.txt', 'configure', 'src'];
            case 'libxml2':
                return ['CMakeLists.txt', 'configure', 'include'];
            default:
                return [];
        }
    }

    protected function extractPhpSourceWindows(string $spcPath): void
    {
        $downloadsPath = $spcPath . '/downloads';
        $sourcePath = $spcPath . '/source/php-src';

        // Find PHP archive
        $phpArchives = glob($downloadsPath . '/php-*.tar.xz');

        if (empty($phpArchives)) {
            $this->warn('No PHP source archive found, skipping pre-extraction');
            return;
        }

        $phpArchive = $phpArchives[0];
        $this->line("Found PHP archive: " . basename($phpArchive));

        // Check if already extracted
        if (file_exists($sourcePath . '/main/php_version.h')) {
            $this->info('✅ PHP source already extracted properly');
            return;
        }

        // Clean and recreate source directory
        if (file_exists($sourcePath)) {
            $this->line('Cleaning existing PHP source directory...');
            Process::run('rm -rf "' . $sourcePath . '"');
        }

        mkdir($sourcePath, 0755, true);

        // Try Python extraction first (most reliable on Windows)
        $pythonScript = __DIR__ . '/extract_php_source.py';

        if (file_exists($pythonScript)) {
            $this->line('Using Python-based extraction (Windows-compatible)...');

            $extractResult = Process::timeout(300)->run(
                "python \"{$pythonScript}\" \"{$phpArchive}\" \"{$sourcePath}\" 1"
            );

            if ($extractResult->successful() && file_exists($sourcePath . '/main/php_version.h')) {
                $this->info('✅ PHP source extracted successfully using Python');

                // Create hash file to prevent re-extraction by static-php-cli
                $this->createSourceHashFile($spcPath, 'php-src', $phpArchive);
                return;
            } else {
                $this->warn('Python extraction failed, trying alternative method...');
            }
        }

        // Fallback: Two-step extraction using git bash tar
        $this->line('Using two-step extraction method...');

        // Convert to Unix-style paths for git bash
        $unixArchive = str_replace('\\', '/', $phpArchive);
        $unixSource = str_replace('\\', '/', $sourcePath);

        if (preg_match('/^([A-Za-z]):\/(.*)/', $unixArchive, $matches)) {
            $unixArchive = '/' . strtolower($matches[1]) . '/' . $matches[2];
        }
        if (preg_match('/^([A-Za-z]):\/(.*)/', $unixSource, $matches)) {
            $unixSource = '/' . strtolower($matches[1]) . '/' . $matches[2];
        }

        $extractResult = Process::timeout(300)
            ->env([
                'MSYS2_ARG_CONV_EXCL' => '*',
                'MSYS_NO_PATHCONV' => '1',
                'PATH' => getenv('PATH')
            ])
            ->run("tar -xf '{$unixArchive}' -C '{$unixSource}' --strip-components=1");

        if ($extractResult->successful() && file_exists($sourcePath . '/main/php_version.h')) {
            $this->info('✅ PHP source extracted successfully using tar');
            $this->createSourceHashFile($spcPath, 'php-src', $phpArchive);
        } else {
            throw new RuntimeException('Failed to extract PHP source using all available methods');
        }
    }

    protected function extractPeclExtensionsWindows(string $spcPath, array $extensions): void
    {
        // Pre-extract PECL extensions that were downloaded (sqlsrv, pdo_sqlsrv, etc.)
        // This prevents static-php-cli's problematic piped extraction on Windows
        $downloadsPath = $spcPath . '/downloads';
        $phpSourcePath = $spcPath . '/source/php-src';
        $pythonScript = __DIR__ . '/extract_php_source.py';

        $this->line("📦 Checking " . count($extensions) . " extensions for PECL extraction...");

        $extracted = 0;
        $skipped = 0;

        foreach ($extensions as $ext) {
            // Skip core extensions that don't need extraction
            $coreExtensions = ['pdo', 'pdo_sqlite', 'sqlite3', 'mbstring', 'fileinfo', 'tokenizer',
                              'openssl', 'curl', 'zip', 'zlib', 'session', 'filter', 'dom', 'xml',
                              'simplexml', 'gd', 'opcache', 'phar', 'iconv', 'ctype', 'bcmath',
                              'bz2', 'sockets', 'mysqlnd', 'mysqli', 'pdo_mysql'];

            if (in_array($ext, $coreExtensions)) {
                $skipped++;
                continue;
            }

            // Look for downloaded archive (could be .tgz, .tar.gz, or .zip)
            $extArchive = null;
            foreach (['.tgz', '.tar.gz', '.zip'] as $suffix) {
                $archivePath = $downloadsPath . '/' . $ext . $suffix;
                if (file_exists($archivePath)) {
                    $extArchive = $archivePath;
                    break;
                }
            }

            if (!$extArchive) {
                $this->line("  ⏭️  {$ext} - no archive found");
                $skipped++;
                continue;
            }

            $extDestination = $phpSourcePath . '/ext/' . $ext;

            // Check if already extracted
            if (file_exists($extDestination) && is_dir($extDestination) && count(scandir($extDestination)) > 3) {
                $this->line("  ✅ {$ext} already extracted");
                $skipped++;
                continue;
            }

            // Extract using Python script
            if (file_exists($pythonScript) && !str_ends_with($extArchive, '.zip')) {
                $this->line("  📦 Pre-extracting {$ext} from " . basename($extArchive) . "...");

                // Clean destination
                if (file_exists($extDestination)) {
                    Process::run('rm -rf "' . $extDestination . '"');
                }

                $extractResult = Process::timeout(60)->run(
                    "python \"{$pythonScript}\" \"{$extArchive}\" \"{$extDestination}\" 1"
                );

                if ($extractResult->successful()) {
                    $this->info("  ✅ {$ext} extracted successfully");
                    // Create hash file to prevent re-extraction
                    $this->createSourceHashFile($spcPath, 'php-src/ext/' . $ext, $extArchive);
                    $extracted++;
                } else {
                    $this->warn("  ⚠️ Failed to pre-extract {$ext}: " . $extractResult->errorOutput());
                    $this->line("     static-php-cli will attempt extraction");
                }
            }
        }

        if ($extracted > 0) {
            $this->info("✅ Pre-extracted {$extracted} PECL extension(s), skipped {$skipped}");
        } else {
            $this->line("ℹ️  No PECL extensions needed pre-extraction (skipped {$skipped})");
        }
    }

    protected function extractLibrariesWindows(string $spcPath): void
    {
        // Pre-extract libraries that have Windows-specific extraction issues
        // (symlinks, path issues, etc.) using Python script
        $downloadsPath = $spcPath . '/downloads';
        $sourcePath = $spcPath . '/source';
        $pythonScript = __DIR__ . '/extract_php_source.py';

        if (!file_exists($pythonScript)) {
            $this->warn('Python extraction script not found, skipping library pre-extraction');
            return;
        }

        // Libraries known to have Windows extraction issues
        $problemLibraries = [
            'libxml2' => ['pattern' => 'libxml2*.tar.gz', 'check_file' => 'configure'],
            'sqlite' => ['pattern' => 'sqlite*.tar.gz', 'check_file' => 'Makefile'],
            'libzip' => ['pattern' => 'libzip*.tar.xz', 'check_file' => 'configure'],
            'libwebp' => ['pattern' => 'libwebp*.tar.gz', 'check_file' => 'configure'],
        ];

        $this->line("📚 Pre-extracting problematic libraries with Python (symlink-safe)...");

        foreach ($problemLibraries as $libName => $libInfo) {
            $archives = glob($downloadsPath . '/' . $libInfo['pattern']);

            if (empty($archives)) {
                $this->line("  ⏭️  {$libName} - no archive found");
                continue;
            }

            $archive = $archives[0];
            $destination = $sourcePath . '/' . $libName;

            // Check if already properly extracted
            if (file_exists($destination . '/' . $libInfo['check_file'])) {
                $this->line("  ✅ {$libName} already extracted properly");
                continue;
            }

            $this->line("  📦 Pre-extracting {$libName} from " . basename($archive) . "...");

            // Clean destination
            if (file_exists($destination)) {
                Process::run('rm -rf "' . $destination . '"');
            }

            mkdir($destination, 0755, true);

            $extractResult = Process::timeout(120)->run(
                "python \"{$pythonScript}\" \"{$archive}\" \"{$destination}\" 1"
            );

            if ($extractResult->successful() && file_exists($destination . '/' . $libInfo['check_file'])) {
                $this->info("  ✅ {$libName} extracted successfully");
                // Create hash file to prevent re-extraction
                $this->createSourceHashFile($spcPath, $libName, $archive);
            } else {
                // Check if extraction partially succeeded (symlinks skipped is OK)
                $filesCount = count(glob($destination . '/*'));
                if ($filesCount > 10) {
                    $this->warn("  ⚠️  {$libName} extracted but may be incomplete (symlinks skipped)");
                    $this->line("      This is usually OK for Windows builds");
                    // Still create hash file to prevent re-extraction
                    $this->createSourceHashFile($spcPath, $libName, $archive);
                } else {
                    $this->warn("  ❌ {$libName} extraction failed: " . $extractResult->errorOutput());
                    $this->line("     static-php-cli will attempt extraction");
                }
            }
        }
    }

    protected function createSourceHashFile(string $spcPath, string $sourceName, string $archivePath): void
    {
        // Create .spc-hash file to prevent static-php-cli from re-extracting
        // CRITICAL: static-php-cli uses sha1_file() to verify sources (see SourceManager.php:76-79)
        $sourcePath = $spcPath . '/source/' . $sourceName;
        $hashFile = $sourcePath . '/.spc-hash';

        if (file_exists($archivePath)) {
            // MUST use SHA1 to match static-php-cli's hash calculation
            $hash = sha1_file($archivePath);
            file_put_contents($hashFile, $hash);
            $this->info("✅ Created .spc-hash file with SHA1: " . substr($hash, 0, 12) . '... (prevents re-extraction)');
        } else {
            $this->warn("⚠️ Archive not found: {$archivePath}, cannot create hash file");
        }
    }
}
