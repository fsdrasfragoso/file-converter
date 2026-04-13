<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Conversion;

use FragosoSoftware\FileConverter\Config\ConverterConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class HttpLibreOfficeClient
{
    protected ConverterConfig $config;
    protected Client $http;

    public function __construct(?ConverterConfig $config = null)
    {
        $this->config = $config ?? ConverterConfig::getInstance();

        $this->http = new Client([
            'base_uri' => $this->config->getLibreOfficeBaseUrl(),
            'timeout'  => $this->config->getLibreOfficeTimeout(),
        ]);
    }

    public function isAvailable(): bool
    {
        try {
            $response = $this->http->get('/docs');

            return $response->getStatusCode() === 200;
        } catch (RequestException $e) {
            return false;
        }
    }

    public function convert(string $sourcePath, string $destinationPath): void
    {
        try {
            $response = $this->http->post('/convert', [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($sourcePath, 'r'),
                        'filename' => basename($sourcePath),
                    ],
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException('HTTP conversion failed with status ' . $response->getStatusCode());
            }

            file_put_contents($destinationPath, $response->getBody()->getContents());

        } catch (RequestException $e) {
            throw new \RuntimeException(
                'HTTP LibreOffice error: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}