<?php

namespace FragosoSoftware\FileConverter\Config;

class ConverterConfig
{
    /**
     * @var array Configurações
     */
    protected array $config = [];

    /**
     * @var bool Flag se já inicializou
     */
    protected static bool $initialized = false;

    /**
     * @var self|null Instância singleton
     */
    protected static ?self $instance = null;

    /**
     * Construtor privado (singleton)
     */
    private function __construct()
    {
        $this->loadConfig();
    }

    /**
     * Obtém instância singleton
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Carrega configurações de múltiplas fontes
     */
    protected function loadConfig(): void
    {
        // Configurações padrão
        $defaults = [
            'libreoffice' => [
                'mode' => 'external', // 'external' ou 'internal'
                'host' => '127.0.0.1',
                'port' => 8100,
                'connection_string' => 'socket,host=127.0.0.1,port=8100;urp;',
                'timeout' => 60,
                'retry_attempts' => 3,
                'retry_delay' => 1, // segundos
                'binary_path' => null, // Para modo interno
            ],
            'fallback' => [
                'enabled' => true,
                'prefer_libreoffice' => true,
            ],
            'temp_dir' => sys_get_temp_dir(),
        ];

        // Tenta carregar de variáveis de ambiente
        $envConfig = $this->loadFromEnvironment();
        
        // Tenta carregar de arquivo .env (se existir)
        $dotenvConfig = $this->loadFromDotEnv();
        
        // Tenta carregar de configuração do Laravel (se estiver no Laravel)
        $laravelConfig = $this->loadFromLaravel();

        // Merge das configurações (último tem precedência)
        $this->config = array_merge_recursive($defaults, $dotenvConfig, $envConfig, $laravelConfig);
        
        // Ajusta a connection string baseada em host/port
        if ($this->config['libreoffice']['mode'] === 'external') {
            $host = $this->config['libreoffice']['host'];
            $port = $this->config['libreoffice']['port'];
            $this->config['libreoffice']['connection_string'] = "socket,host={$host},port={$port};urp;";
        }
    }

    /**
     * Carrega configurações de variáveis de ambiente
     */
    protected function loadFromEnvironment(): array
    {
        $config = [];
        
        // Modo do LibreOffice
        if (getenv('LIBREOFFICE_MODE')) {
            $config['libreoffice']['mode'] = getenv('LIBREOFFICE_MODE');
        }
        
        // Host e Porta
        if (getenv('LIBREOFFICE_HOST')) {
            $config['libreoffice']['host'] = getenv('LIBREOFFICE_HOST');
        }
        
        if (getenv('LIBREOFFICE_PORT')) {
            $config['libreoffice']['port'] = (int) getenv('LIBREOFFICE_PORT');
        }
        
        // Timeout
        if (getenv('LIBREOFFICE_TIMEOUT')) {
            $config['libreoffice']['timeout'] = (int) getenv('LIBREOFFICE_TIMEOUT');
        }
        
        // Binary path (modo interno)
        if (getenv('LIBREOFFICE_BINARY_PATH')) {
            $config['libreoffice']['binary_path'] = getenv('LIBREOFFICE_BINARY_PATH');
        }
        
        // Fallback
        if (getenv('LIBREOFFICE_FALLBACK_ENABLED') !== false) {
            $config['fallback']['enabled'] = filter_var(getenv('LIBREOFFICE_FALLBACK_ENABLED'), FILTER_VALIDATE_BOOLEAN);
        }
        
        return $config;
    }

    /**
     * Carrega configurações de arquivo .env
     */
    protected function loadFromDotEnv(): array
    {
        $config = [];
        
        // Procura por .env no projeto
        $possiblePaths = [
            getcwd() . '/.env',
            dirname(getcwd(), 2) . '/.env', // Para projetos Laravel
            dirname(getcwd(), 3) . '/.env',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                try {
                    if (class_exists('\Dotenv\Dotenv')) {
                        $dotenv = \Dotenv\Dotenv::createImmutable(dirname($path));
                        $dotenv->load();
                        
                        // Lê as variáveis
                        if (getenv('LIBREOFFICE_MODE')) {
                            $config['libreoffice']['mode'] = getenv('LIBREOFFICE_MODE');
                        }
                        if (getenv('LIBREOFFICE_HOST')) {
                            $config['libreoffice']['host'] = getenv('LIBREOFFICE_HOST');
                        }
                        if (getenv('LIBREOFFICE_PORT')) {
                            $config['libreoffice']['port'] = (int) getenv('LIBREOFFICE_PORT');
                        }
                        if (getenv('LIBREOFFICE_BINARY_PATH')) {
                            $config['libreoffice']['binary_path'] = getenv('LIBREOFFICE_BINARY_PATH');
                        }
                        
                        break;
                    }
                } catch (\Exception $e) {
                    // Falha ao carregar .env, ignora
                }
            }
        }
        
        return $config;
    }

    /**
     * Carrega configurações do Laravel
     */
    protected function loadFromLaravel(): array
    {
        $config = [];
        
        // Verifica se está no Laravel
        if (function_exists('config') && function_exists('app')) {
            try {
                if (config('file-converter.libreoffice.mode')) {
                    $config['libreoffice']['mode'] = config('file-converter.libreoffice.mode');
                }
                if (config('file-converter.libreoffice.host')) {
                    $config['libreoffice']['host'] = config('file-converter.libreoffice.host');
                }
                if (config('file-converter.libreoffice.port')) {
                    $config['libreoffice']['port'] = config('file-converter.libreoffice.port');
                }
                if (config('file-converter.libreoffice.binary_path')) {
                    $config['libreoffice']['binary_path'] = config('file-converter.libreoffice.binary_path');
                }
                if (config('file-converter.libreoffice.timeout')) {
                    $config['libreoffice']['timeout'] = config('file-converter.libreoffice.timeout');
                }
                if (config('file-converter.fallback.enabled') !== null) {
                    $config['fallback']['enabled'] = config('file-converter.fallback.enabled');
                }
            } catch (\Exception $e) {
                // Não está no Laravel ou config não disponível
            }
        }
        
        return $config;
    }

    /**
     * Obtém configuração
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $segment) {
            if (!isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }
        
        return $value;
    }

    /**
     * Verifica se deve usar modo externo
     */
    public function useExternalLibreOffice(): bool
    {
        return $this->get('libreoffice.mode') === 'external';
    }

    /**
     * Verifica se fallback está habilitado
     */
    public function isFallbackEnabled(): bool
    {
        return $this->get('fallback.enabled', true);
    }

    /**
     * Obtém host do LibreOffice
     */
    public function getLibreOfficeHost(): string
    {
        return $this->get('libreoffice.host', '127.0.0.1');
    }

    /**
     * Obtém porta do LibreOffice
     */
    public function getLibreOfficePort(): int
    {
        return (int) $this->get('libreoffice.port', 8100);
    }

    /**
     * Obtém connection string
     */
    public function getLibreOfficeConnectionString(): string
    {
        return $this->get('libreoffice.connection_string', 'socket,host=127.0.0.1,port=8100;urp;');
    }

    /**
     * Obtém timeout
     */
    public function getLibreOfficeTimeout(): int
    {
        return (int) $this->get('libreoffice.timeout', 60);
    }
}