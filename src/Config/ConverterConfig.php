<?php

namespace FragosoSoftware\FileConverter\Config;

final class ConverterConfig
{
    protected array $config = [];
    protected static ?self $instance = null;

    private function __construct()
    {
        $this->loadConfig();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected function loadConfig(): void
    {
        $defaults = [
            'libreoffice' => [
                'mode' => 'http', // http | external | internal
                'base_url' => 'http://127.0.0.1:8000',
                'host' => '127.0.0.1',
                'port' => 2002,
                'timeout' => 60,
                'retry_attempts' => 3,
                'retry_delay' => 1,
                'binary_path' => null,
                'healthcheck_path' => '/docs',
                'convert_path' => '/convert',
            ],
            'fallback' => [
                'enabled' => true,
                'prefer_libreoffice' => true,
            ],
            'temp_dir' => sys_get_temp_dir(),
        ];

        $envConfig = $this->loadFromEnvironment();
        $dotenvConfig = $this->loadFromDotEnv();
        $laravelConfig = $this->loadFromLaravel();

        $this->config = array_replace_recursive(
            $defaults,
            $dotenvConfig,
            $envConfig,
            $laravelConfig
        );

        $baseUrl = (string) ($this->config['libreoffice']['base_url'] ?? '');
        if ($baseUrl !== '') {
            $this->config['libreoffice']['base_url'] = rtrim($baseUrl, '/');
        }
    }

    protected function loadFromEnvironment(): array
    {
        $config = [];

        $mode = getenv('LIBREOFFICE_MODE');
        if ($mode !== false && $mode !== '') {
            $config['libreoffice']['mode'] = $mode;
        }

        $baseUrl = getenv('LIBREOFFICE_BASE_URL');
        if ($baseUrl !== false && $baseUrl !== '') {
            $config['libreoffice']['base_url'] = $baseUrl;
        }

        $host = getenv('LIBREOFFICE_HOST');
        if ($host !== false && $host !== '') {
            $config['libreoffice']['host'] = $host;
        }

        $port = getenv('LIBREOFFICE_PORT');
        if ($port !== false && $port !== '') {
            $config['libreoffice']['port'] = (int) $port;
        }

        $timeout = getenv('LIBREOFFICE_TIMEOUT');
        if ($timeout !== false && $timeout !== '') {
            $config['libreoffice']['timeout'] = (int) $timeout;
        }

        $retryAttempts = getenv('LIBREOFFICE_RETRY_ATTEMPTS');
        if ($retryAttempts !== false && $retryAttempts !== '') {
            $config['libreoffice']['retry_attempts'] = (int) $retryAttempts;
        }

        $retryDelay = getenv('LIBREOFFICE_RETRY_DELAY');
        if ($retryDelay !== false && $retryDelay !== '') {
            $config['libreoffice']['retry_delay'] = (int) $retryDelay;
        }

        $binaryPath = getenv('LIBREOFFICE_BINARY_PATH');
        if ($binaryPath !== false && $binaryPath !== '') {
            $config['libreoffice']['binary_path'] = $binaryPath;
        }

        $healthcheckPath = getenv('LIBREOFFICE_HEALTHCHECK_PATH');
        if ($healthcheckPath !== false && $healthcheckPath !== '') {
            $config['libreoffice']['healthcheck_path'] = $healthcheckPath;
        }

        $convertPath = getenv('LIBREOFFICE_CONVERT_PATH');
        if ($convertPath !== false && $convertPath !== '') {
            $config['libreoffice']['convert_path'] = $convertPath;
        }

        $fallbackEnabled = getenv('LIBREOFFICE_FALLBACK_ENABLED');
        if ($fallbackEnabled !== false && $fallbackEnabled !== '') {
            $config['fallback']['enabled'] = filter_var($fallbackEnabled, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;
        }

        $preferLibreOffice = getenv('LIBREOFFICE_PREFER_LIBREOFFICE');
        if ($preferLibreOffice !== false && $preferLibreOffice !== '') {
            $config['fallback']['prefer_libreoffice'] = filter_var($preferLibreOffice, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;
        }

        $tempDir = getenv('FILE_CONVERTER_TEMP_DIR');
        if ($tempDir !== false && $tempDir !== '') {
            $config['temp_dir'] = $tempDir;
        }

        return $config;
    }

    protected function loadFromDotEnv(): array
    {
        $config = [];

        $possiblePaths = [
            getcwd() . DIRECTORY_SEPARATOR . '.env',
            dirname(getcwd(), 1) . DIRECTORY_SEPARATOR . '.env',
            dirname(getcwd(), 2) . DIRECTORY_SEPARATOR . '.env',
            dirname(getcwd(), 3) . DIRECTORY_SEPARATOR . '.env',
        ];

        foreach ($possiblePaths as $path) {
            if (!is_file($path) || !class_exists(\Dotenv\Dotenv::class)) {
                continue;
            }

            try {
                $dotenv = \Dotenv\Dotenv::createImmutable(dirname($path));
                $dotenv->safeLoad();

                return $this->loadFromEnvironment();
            } catch (\Throwable) {
                return [];
            }
        }

        return $config;
    }

    protected function loadFromLaravel(): array
    {
        if (!function_exists('config')) {
            return [];
        }

        try {
            $config = [];

            $mode = config('file-converter.libreoffice.mode');
            if ($mode !== null) {
                $config['libreoffice']['mode'] = $mode;
            }

            $baseUrl = config('file-converter.libreoffice.base_url');
            if ($baseUrl !== null) {
                $config['libreoffice']['base_url'] = $baseUrl;
            }

            $host = config('file-converter.libreoffice.host');
            if ($host !== null) {
                $config['libreoffice']['host'] = $host;
            }

            $port = config('file-converter.libreoffice.port');
            if ($port !== null) {
                $config['libreoffice']['port'] = (int) $port;
            }

            $timeout = config('file-converter.libreoffice.timeout');
            if ($timeout !== null) {
                $config['libreoffice']['timeout'] = (int) $timeout;
            }

            $retryAttempts = config('file-converter.libreoffice.retry_attempts');
            if ($retryAttempts !== null) {
                $config['libreoffice']['retry_attempts'] = (int) $retryAttempts;
            }

            $retryDelay = config('file-converter.libreoffice.retry_delay');
            if ($retryDelay !== null) {
                $config['libreoffice']['retry_delay'] = (int) $retryDelay;
            }

            $binaryPath = config('file-converter.libreoffice.binary_path');
            if ($binaryPath !== null) {
                $config['libreoffice']['binary_path'] = $binaryPath;
            }

            $healthcheckPath = config('file-converter.libreoffice.healthcheck_path');
            if ($healthcheckPath !== null) {
                $config['libreoffice']['healthcheck_path'] = $healthcheckPath;
            }

            $convertPath = config('file-converter.libreoffice.convert_path');
            if ($convertPath !== null) {
                $config['libreoffice']['convert_path'] = $convertPath;
            }

            $fallbackEnabled = config('file-converter.fallback.enabled');
            if ($fallbackEnabled !== null) {
                $config['fallback']['enabled'] = (bool) $fallbackEnabled;
            }

            $preferLibreOffice = config('file-converter.fallback.prefer_libreoffice');
            if ($preferLibreOffice !== null) {
                $config['fallback']['prefer_libreoffice'] = (bool) $preferLibreOffice;
            }

            $tempDir = config('file-converter.temp_dir');
            if ($tempDir !== null) {
                $config['temp_dir'] = $tempDir;
            }

            return $config;
        } catch (\Throwable) {
            return [];
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $this->config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function getAll(): array
    {
        return $this->config;
    }

    public function getLibreOfficeMode(): string
    {
        return (string) $this->get('libreoffice.mode', 'http');
    }

    public function useHttpLibreOffice(): bool
    {
        return $this->getLibreOfficeMode() === 'http';
    }

    public function useExternalLibreOffice(): bool
    {
        return $this->getLibreOfficeMode() === 'external';
    }

    public function useInternalLibreOffice(): bool
    {
        return $this->getLibreOfficeMode() === 'internal';
    }

    public function isFallbackEnabled(): bool
    {
        return (bool) $this->get('fallback.enabled', true);
    }

    public function preferLibreOffice(): bool
    {
        return (bool) $this->get('fallback.prefer_libreoffice', true);
    }

    public function getLibreOfficeBaseUrl(): string
    {
        return rtrim((string) $this->get('libreoffice.base_url', 'http://127.0.0.1:8000'), '/');
    }

    public function getLibreOfficeHealthcheckUrl(): string
    {
        return $this->getLibreOfficeBaseUrl() . $this->getLibreOfficeHealthcheckPath();
    }

    public function getLibreOfficeConvertUrl(): string
    {
        return $this->getLibreOfficeBaseUrl() . $this->getLibreOfficeConvertPath();
    }

    public function getLibreOfficeHealthcheckPath(): string
    {
        $path = (string) $this->get('libreoffice.healthcheck_path', '/docs');
        return '/' . ltrim($path, '/');
    }

    public function getLibreOfficeConvertPath(): string
    {
        $path = (string) $this->get('libreoffice.convert_path', '/convert');
        return '/' . ltrim($path, '/');
    }

    public function getLibreOfficeHost(): string
    {
        return (string) $this->get('libreoffice.host', '127.0.0.1');
    }

    public function getLibreOfficePort(): int
    {
        return (int) $this->get('libreoffice.port', 2002);
    }

    public function getLibreOfficeTimeout(): int
    {
        return (int) $this->get('libreoffice.timeout', 60);
    }

    public function getLibreOfficeRetryAttempts(): int
    {
        return (int) $this->get('libreoffice.retry_attempts', 3);
    }

    public function getLibreOfficeRetryDelay(): int
    {
        return (int) $this->get('libreoffice.retry_delay', 1);
    }

    public function getLibreOfficeBinaryPath(): ?string
    {
        $value = $this->get('libreoffice.binary_path');
        return is_string($value) && $value !== '' ? $value : null;
    }

    public function getTempDir(): string
    {
        return (string) $this->get('temp_dir', sys_get_temp_dir());
    }
}