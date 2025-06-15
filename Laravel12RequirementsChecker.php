<?php
/**
 * Laravel 12 System Requirements Checker Class
 * Memeriksa semua requirement sistem untuk Laravel 12 dan mengembalikan hasil sebagai array/object
 */

class Laravel12RequirementsChecker
{
    private $results = [];
    private $phpVersion;
    private $loadedExtensions;

    public function __construct()
    {
        $this->phpVersion = phpversion();
        $this->loadedExtensions = get_loaded_extensions();
        sort($this->loadedExtensions);
    }

    /**
     * Jalankan semua pemeriksaan dan kembalikan hasil lengkap
     * 
     * @param bool $asObject Kembalikan sebagai object jika true, array jika false
     * @return array|object
     */
    public function checkAll($asObject = false)
    {
        $this->results = [
            'timestamp' => date('Y-m-d H:i:s'),
            'system_info' => $this->getSystemInfo(),
            'laravel_requirements' => $this->checkLaravelRequirements(),
            'database_support' => $this->checkDatabaseSupport(),
            'cache_extensions' => $this->checkCacheExtensions(),
            'additional_extensions' => $this->checkAdditionalExtensions(),
            'loaded_extensions' => $this->getLoadedExtensions(),
            'php_settings' => $this->getPhpSettings(),
            'summary' => $this->generateSummary(),
            'recommendations' => $this->getRecommendations()
        ];

        return $asObject ? (object) $this->results : $this->results;
    }

    /**
     * Dapatkan informasi sistem dasar
     */
    public function getSystemInfo()
    {
        return [
            'os' => php_uname('s') . ' ' . php_uname('r'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'php_version' => $this->phpVersion,
            'php_sapi' => php_sapi_name(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => (int) ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
            'current_directory' => getcwd(),
            'temp_directory' => sys_get_temp_dir()
        ];
    }

    /**
     * Periksa Laravel 12 requirements wajib
     */
    public function checkLaravelRequirements()
    {
        $requirements = [
            'php_version' => [
                'name' => 'PHP Version >= 8.2',
                'required_version' => '8.2.0',
                'current_version' => $this->phpVersion,
                'status' => version_compare($this->phpVersion, '8.2.0', '>='),
                'critical' => true
            ]
        ];

        $extensions = [
            'bcmath' => 'BCMath Extension',
            'ctype' => 'Ctype Extension',
            'curl' => 'cURL Extension',
            'dom' => 'DOM Extension',
            'fileinfo' => 'Fileinfo Extension',
            'filter' => 'Filter Extension',
            'hash' => 'Hash Extension',
            'mbstring' => 'Mbstring Extension',
            'openssl' => 'OpenSSL Extension',
            'pcre' => 'PCRE Extension',
            'pdo' => 'PDO Extension',
            'session' => 'Session Extension',
            'tokenizer' => 'Tokenizer Extension',
            'xml' => 'XML Extension',
            'xmlwriter' => 'XMLWriter Extension',
            'json' => 'JSON Extension',
            'iconv' => 'Iconv Extension'
        ];

        foreach ($extensions as $extension => $name) {
            $requirements[$extension] = [
                'name' => $name,
                'status' => extension_loaded($extension),
                'critical' => true
            ];
        }

        return $requirements;
    }

    /**
     * Periksa dukungan database
     */
    public function checkDatabaseSupport()
    {
        $databases = [
            'mysql' => [
                'name' => 'MySQL/MariaDB (PDO_MYSQL)',
                'extension' => 'pdo_mysql',
                'status' => extension_loaded('pdo_mysql')
            ],
            'postgresql' => [
                'name' => 'PostgreSQL (PDO_PGSQL)',
                'extension' => 'pdo_pgsql',
                'status' => extension_loaded('pdo_pgsql')
            ],
            'sqlite' => [
                'name' => 'SQLite (PDO_SQLITE)',
                'extension' => 'pdo_sqlite',
                'status' => extension_loaded('pdo_sqlite')
            ],
            'sqlserver' => [
                'name' => 'SQL Server (PDO_SQLSRV)',
                'extension' => 'pdo_sqlsrv',
                'status' => extension_loaded('pdo_sqlsrv')
            ]
        ];

        return $databases;
    }

    /**
     * Periksa cache dan performance extensions
     */
    public function checkCacheExtensions()
    {
        $extensions = [
            'redis' => [
                'name' => 'Redis',
                'status' => extension_loaded('redis'),
                'description' => 'In-memory data structure store'
            ],
            'memcached' => [
                'name' => 'Memcached',
                'status' => extension_loaded('memcached'),
                'description' => 'High-performance distributed memory caching'
            ],
            'apcu' => [
                'name' => 'APCu',
                'status' => extension_loaded('apcu'),
                'description' => 'User cache for PHP'
            ],
            'opcache' => [
                'name' => 'OPcache',
                'status' => extension_loaded('opcache'),
                'description' => 'PHP bytecode caching',
                'config' => extension_loaded('opcache') ? $this->getOpcacheConfig() : null
            ],
            'igbinary' => [
                'name' => 'Igbinary',
                'status' => extension_loaded('igbinary'),
                'description' => 'Binary serialization extension'
            ]
        ];

        return $extensions;
    }

    /**
     * Dapatkan konfigurasi OPcache
     */
    private function getOpcacheConfig()
    {
        return [
            'opcache_enable' => ini_get('opcache.enable'),
            'memory_consumption' => ini_get('opcache.memory_consumption') . 'MB',
            'max_accelerated_files' => ini_get('opcache.max_accelerated_files'),
            'validate_timestamps' => ini_get('opcache.validate_timestamps'),
            'revalidate_freq' => ini_get('opcache.revalidate_freq'),
            'save_comments' => ini_get('opcache.save_comments')
        ];
    }

    /**
     * Periksa extensions tambahan yang berguna
     */
    public function checkAdditionalExtensions()
    {
        $extensions = [
            'gd' => [
                'name' => 'GD Library',
                'status' => extension_loaded('gd'),
                'description' => 'Image processing'
            ],
            'imagick' => [
                'name' => 'ImageMagick',
                'status' => extension_loaded('imagick'),
                'description' => 'Advanced image manipulation'
            ],
            'exif' => [
                'name' => 'Exif',
                'status' => extension_loaded('exif'),
                'description' => 'Read EXIF data from images'
            ],
            'zip' => [
                'name' => 'Zip',
                'status' => extension_loaded('zip'),
                'description' => 'Archive compression'
            ],
            'ftp' => [
                'name' => 'FTP',
                'status' => extension_loaded('ftp'),
                'description' => 'File transfer protocol'
            ],
            'ldap' => [
                'name' => 'LDAP',
                'status' => extension_loaded('ldap'),
                'description' => 'Directory access protocol'
            ],
            'soap' => [
                'name' => 'SOAP',
                'status' => extension_loaded('soap'),
                'description' => 'Web services protocol'
            ],
            'ssh2' => [
                'name' => 'SSH2',
                'status' => extension_loaded('ssh2'),
                'description' => 'Secure shell connections'
            ],
            'xdebug' => [
                'name' => 'Xdebug',
                'status' => extension_loaded('xdebug'),
                'description' => 'Debugging and profiling'
            ],
            'pcntl' => [
                'name' => 'PCNTL',
                'status' => extension_loaded('pcntl'),
                'description' => 'Process control (Unix only)'
            ],
            'posix' => [
                'name' => 'POSIX',
                'status' => extension_loaded('posix'),
                'description' => 'POSIX functions (Unix only)'
            ],
            'sockets' => [
                'name' => 'Sockets',
                'status' => extension_loaded('sockets'),
                'description' => 'Low-level socket communication'
            ],
            'intl' => [
                'name' => 'Intl',
                'status' => extension_loaded('intl'),
                'description' => 'Internationalization functions'
            ],
            'gmp' => [
                'name' => 'GMP',
                'status' => extension_loaded('gmp'),
                'description' => 'GNU Multiple Precision arithmetic'
            ],
            'sodium' => [
                'name' => 'Sodium',
                'status' => extension_loaded('sodium'),
                'description' => 'Modern cryptography library'
            ]
        ];

        return $extensions;
    }

    /**
     * Dapatkan semua extensions yang terload
     */
    public function getLoadedExtensions()
    {
        return [
            'total_count' => count($this->loadedExtensions),
            'extensions' => $this->loadedExtensions
        ];
    }

    /**
     * Dapatkan PHP settings penting
     */
    public function getPhpSettings()
    {
        return [
            'allow_url_fopen' => ini_get('allow_url_fopen') ? true : false,
            'display_errors' => ini_get('display_errors') ? true : false,
            'log_errors' => ini_get('log_errors') ? true : false,
            'error_log' => ini_get('error_log') ?: null,
            'default_timezone' => ini_get('date.timezone') ?: 'UTC',
            'session_save_handler' => ini_get('session.save_handler'),
            'session_save_path' => ini_get('session.save_path'),
            'upload_tmp_dir' => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
            'open_basedir' => ini_get('open_basedir') ?: null,
            'disable_functions' => ini_get('disable_functions') ?: null,
            'max_input_vars' => (int) ini_get('max_input_vars'),
            'max_file_uploads' => (int) ini_get('max_file_uploads')
        ];
    }

    /**
     * Generate ringkasan hasil pemeriksaan
     */
    public function generateSummary()
    {
        $laravelReq = $this->checkLaravelRequirements();
        $cacheExt = $this->checkCacheExtensions();
        $additionalExt = $this->checkAdditionalExtensions();

        $totalRequired = count($laravelReq);
        $passedRequired = 0;
        foreach ($laravelReq as $req) {
            if ($req['status']) $passedRequired++;
        }

        $totalOptional = count($cacheExt) + count($additionalExt);
        $passedOptional = 0;
        foreach ($cacheExt as $ext) {
            if ($ext['status']) $passedOptional++;
        }
        foreach ($additionalExt as $ext) {
            if ($ext['status']) $passedOptional++;
        }

        $isReady = ($passedRequired == $totalRequired);

        return [
            'laravel_ready' => $isReady,
            'required_extensions' => [
                'total' => $totalRequired,
                'passed' => $passedRequired,
                'percentage' => round(($passedRequired / $totalRequired) * 100, 1)
            ],
            'optional_extensions' => [
                'total' => $totalOptional,
                'passed' => $passedOptional,
                'percentage' => round(($passedOptional / $totalOptional) * 100, 1)
            ],
            'total_loaded_extensions' => count($this->loadedExtensions),
            'php_version_ok' => version_compare($this->phpVersion, '8.2.0', '>='),
            'missing_required' => $this->getMissingRequired($laravelReq)
        ];
    }

    /**
     * Dapatkan daftar extensions wajib yang hilang
     */
    private function getMissingRequired($requirements)
    {
        $missing = [];
        foreach ($requirements as $key => $req) {
            if (!$req['status'] && ($req['critical'] ?? false)) {
                $missing[] = $req['name'];
            }
        }
        return $missing;
    }

    /**
     * Dapatkan rekomendasi
     */
    public function getRecommendations()
    {
        $recommendations = [
            'performance' => [
                'Install OPcache untuk bytecode caching',
                'Install Redis atau Memcached untuk session dan cache storage',
                'Pastikan memory_limit minimal 256M untuk aplikasi besar',
                'Aktifkan compression di web server',
                'Gunakan PHP 8.3+ untuk performa terbaik'
            ],
            'development' => [
                'Install Xdebug untuk debugging',
                'Set display_errors = On untuk development environment',
                'Install Composer untuk dependency management',
                'Gunakan environment-specific configuration'
            ],
            'security' => [
                'Disable expose_php untuk security',
                'Set proper session configuration',
                'Install Sodium extension untuk modern cryptography',
                'Configure proper error logging'
            ]
        ];

        // Tambahkan rekomendasi spesifik berdasarkan hasil check
        $summary = $this->generateSummary();
        if (!empty($summary['missing_required'])) {
            $recommendations['critical'] = [
                'Install extensions yang hilang: ' . implode(', ', $summary['missing_required'])
            ];
        }

        return $recommendations;
    }

    /**
     * Periksa hanya requirements wajib Laravel
     */
    public function checkRequiredOnly()
    {
        return $this->checkLaravelRequirements();
    }

    /**
     * Periksa apakah sistem siap untuk Laravel 12
     */
    public function isLaravelReady()
    {
        $summary = $this->generateSummary();
        return $summary['laravel_ready'];
    }

    /**
     * Export hasil ke JSON
     */
    public function toJson($prettyPrint = true)
    {
        $results = $this->checkAll();
        return json_encode($results, $prettyPrint ? JSON_PRETTY_PRINT : 0);
    }

    /**
     * Generate simple text report
     */
    public function generateTextReport()
    {
        $results = $this->checkAll();
        $summary = $results['summary'];
        
        $report = "=== LARAVEL 12 SYSTEM REQUIREMENTS REPORT ===\n";
        $report .= "Generated: {$results['timestamp']}\n";
        $report .= "Laravel 12 Ready: " . ($summary['laravel_ready'] ? 'YES' : 'NO') . "\n";
        $report .= "Required Extensions: {$summary['required_extensions']['passed']}/{$summary['required_extensions']['total']} ({$summary['required_extensions']['percentage']}%)\n";
        $report .= "Optional Extensions: {$summary['optional_extensions']['passed']}/{$summary['optional_extensions']['total']} ({$summary['optional_extensions']['percentage']}%)\n\n";
        
        if (!empty($summary['missing_required'])) {
            $report .= "MISSING REQUIRED EXTENSIONS:\n";
            foreach ($summary['missing_required'] as $missing) {
                $report .= "- {$missing}\n";
            }
            $report .= "\n";
        }
        
        return $report;
    }
}

// Contoh penggunaan:
/*
// Instantiate checker
$checker = new Laravel12RequirementsChecker();

// Check semua requirements
$results = $checker->checkAll(); // Return array
$resultsObj = $checker->checkAll(true); // Return object

// Check hanya requirements wajib
$required = $checker->checkRequiredOnly();

// Check apakah siap untuk Laravel
$isReady = $checker->isLaravelReady();

// Export ke JSON
$json = $checker->toJson();

// Generate text report
$textReport = $checker->generateTextReport();

// Print hasil
echo "Laravel 12 Ready: " . ($isReady ? 'YES' : 'NO') . "\n";
echo "Text Report:\n" . $textReport . "\n";
*/
?>
