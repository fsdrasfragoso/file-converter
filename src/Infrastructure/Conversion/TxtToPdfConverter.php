<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Conversion;

use FragosoSoftware\FileConverter\Core\Conversion\AbstractConverter;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Converte arquivos TXT para PDF usando Dompdf.
 */
class TxtToPdfConverter extends AbstractConverter
{
    /**
     * {@inheritdoc}
     */
    public function convert(string $sourcePath, string $destinationPath): void
    {
        $this->ensureFileReadable($sourcePath);
        $this->ensureDirectoryWritable(dirname($destinationPath));

        $textContent = file_get_contents($sourcePath);

        if ($textContent === false) {
            throw new \RuntimeException("Falha ao ler o arquivo TXT.");
        }

        $html = $this->wrapTextAsHtml($textContent);

        $dompdf = new Dompdf($this->getOptions());
        $dompdf->loadHtml($html);
        $dompdf->render();

        file_put_contents($destinationPath, $dompdf->output());
    }

    protected function wrapTextAsHtml(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: monospace; font-size: 12px; }
pre { white-space: pre-wrap; word-wrap: break-word; }
</style>
</head>
<body>
<pre>{$escaped}</pre>
</body>
</html>
HTML;
    }

    protected function getOptions(): Options
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        return $options;
    }
}