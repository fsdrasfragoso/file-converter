<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Conversion;

use FragosoSoftware\FileConverter\Core\Conversion\AbstractConverter;
use PhpOffice\PhpPresentation\IOFactory as PresentationIOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Converte apresentações para PDF.
 * Suporta: PPTX, PPT, ODP
 *
 * Prioridade: LibreOffice
 * Fallback: PhpPresentation + Dompdf
 */
class PptxToPdfConverter extends AbstractConverter
{
    public function convert(string $sourcePath, string $destinationPath): void
    {
        $this->ensureFileReadable($sourcePath);
        $this->ensureDirectoryWritable(dirname($destinationPath));

        if ($this->canUseLibreOffice()) {
            try {
                $this->convertWithLibreOffice($sourcePath, $destinationPath);
                return;
            } catch (\RuntimeException $e) {
                error_log(
                    'LibreOffice presentation conversion failed: ' . $e->getMessage()
                );
            }
        }

        $this->convertWithPhpPresentation($sourcePath, $destinationPath);
    }

    protected function convertWithLibreOffice(string $sourcePath, string $destinationPath): void
    {
        $outputDir = dirname($destinationPath);

        $command = sprintf(
            'soffice --headless --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($outputDir),
            escapeshellarg($sourcePath)
        );

        exec($command, $output, $resultCode);

        if ($resultCode !== 0) {
            throw new \RuntimeException(
                'LibreOffice conversion failed: ' . implode("\n", $output)
            );
        }

        $generatedPdf = $outputDir . '/' . pathinfo($sourcePath, PATHINFO_FILENAME) . '.pdf';

        if (!file_exists($generatedPdf)) {
            throw new \RuntimeException('Expected PDF not generated.');
        }

        rename($generatedPdf, $destinationPath);
    }

    protected function convertWithPhpPresentation(string $sourcePath, string $destinationPath): void
    {
        $presentation = PresentationIOFactory::load($sourcePath);

        $htmlWriter = PresentationIOFactory::createWriter($presentation, 'HTML');

        $tempDir = sys_get_temp_dir() . '/ppt_html_' . uniqid();
        mkdir($tempDir);

        $htmlFile = $tempDir . '/index.html';

        $htmlWriter->save($htmlFile);

        $dompdf = new Dompdf($this->getOptions());
        $dompdf->loadHtml(file_get_contents($htmlFile));
        $dompdf->render();

        file_put_contents($destinationPath, $dompdf->output());
    }

    protected function getOptions(): Options
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        return $options;
    }

    protected function canUseLibreOffice(): bool
    {
        if (!function_exists('exec')) {
            return false;
        }

        exec('soffice --version 2>&1', $output, $code);

        return $code === 0;
    }
}