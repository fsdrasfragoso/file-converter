<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Conversion;

use FragosoSoftware\FileConverter\Core\Conversion\AbstractConverter;
use RuntimeException;

class CsvToJsonConverter extends AbstractConverter
{
    public function convert(string $sourcePath, string $destinationPath): void
    {
        $data = $this->toJson($sourcePath);

        $this->ensureDirectoryWritable(dirname($destinationPath));

        file_put_contents(
            $destinationPath,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Retorna o CSV convertido para array (JSON-ready).
     */
    public function toJson(string $csvPath): array
    {
        $this->ensureFileReadable($csvPath);

        $lines = file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!$lines) {
            throw new RuntimeException("CSV vazio ou inválido.");
        }

        $delimiter = $this->detectDelimiter($lines[0]);

        $header = str_getcsv(array_shift($lines), $delimiter);

        $data = [];

        foreach ($lines as $line) {
            $row = str_getcsv($line, $delimiter);

            if (count($row) !== count($header)) {
                continue;
            }

            $data[] = array_combine($header, $row);
        }

        return $data;
    }

    /**
     * Detecta automaticamente o delimitador do CSV.
     */
    protected function detectDelimiter(string $line): string
    {
        $delimiters = [',', ';', "\t", '|'];

        $counts = [];

        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($line, $delimiter);
        }

        arsort($counts);

        return array_key_first($counts);
    }
}