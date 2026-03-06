<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Conversion;

use FragosoSoftware\FileConverter\Core\Conversion\AbstractConverter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Dompdf\Dompdf;
use RuntimeException;

class CsvToPdfConverter extends AbstractConverter
{
    public function convert(string $sourcePath, string $destinationPath): void
    {
        $this->ensureFileReadable($sourcePath);
        $this->ensureDirectoryWritable(dirname($destinationPath));

        if ($this->canUseLibreOffice()) {
            try {
                $this->convertWithLibreOffice($sourcePath, $destinationPath);
                return;
            } catch (RuntimeException $e) {
                error_log('LibreOffice CSV conversion failed, falling back: '.$e->getMessage());
            }
        }

        $this->convertWithPhpSpreadsheet($sourcePath, $destinationPath);
    }

    protected function convertWithPhpSpreadsheet(string $sourcePath, string $destinationPath): void
    {
        $spreadsheet = IOFactory::load($sourcePath);

        $writer = IOFactory::createWriter($spreadsheet, 'Pdf');

        $writer->save($destinationPath);
    }
}