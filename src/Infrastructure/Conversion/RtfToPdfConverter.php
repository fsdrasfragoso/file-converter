<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Conversion;

use FragosoSoftware\FileConverter\Core\Conversion\AbstractConverter;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use Dompdf\Dompdf;

class RtfToPdfConverter extends AbstractConverter
{
    public function convert(string $sourcePath, string $destinationPath): void
    {
        if ($this->isLibreOfficeAvailable()) {
            $this->convertWithLibreOffice($sourcePath, $destinationPath);
            return;
        }

        $this->convertWithPhpWord($sourcePath, $destinationPath);
    }

    private function convertWithLibreOffice(string $sourcePath, string $destinationPath): void
    {
        $outputDir = dirname($destinationPath);

        $command = sprintf(
            'soffice --headless --convert-to pdf --outdir %s %s',
            escapeshellarg($outputDir),
            escapeshellarg($sourcePath)
        );

        exec($command, $output, $result);

        if ($result !== 0) {
            throw new \RuntimeException('Erro ao converter via LibreOffice.');
        }

        $generatedFile = $outputDir . '/' . pathinfo($sourcePath, PATHINFO_FILENAME) . '.pdf';

        if (!file_exists($generatedFile)) {
            throw new \RuntimeException('PDF não gerado pelo LibreOffice.');
        }

        rename($generatedFile, $destinationPath);
    }

    private function convertWithPhpWord(string $sourcePath, string $destinationPath): void
    {
        Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);

        Settings::setPdfRendererPath(
            dirname(dirname((new \ReflectionClass(Dompdf::class))->getFileName()))
        );

        $phpWord = IOFactory::load($sourcePath, 'RTF');

        $writer = IOFactory::createWriter($phpWord, 'PDF');
        $writer->save($destinationPath);
    }

    private function isLibreOfficeAvailable(): bool
    {
        $command = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'where soffice'
            : 'which soffice';

        exec($command, $output, $result);

        return $result === 0;
    }
}