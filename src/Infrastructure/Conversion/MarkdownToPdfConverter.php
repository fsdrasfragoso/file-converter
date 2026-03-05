<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Conversion;

use FragosoSoftware\FileConverter\Core\Conversion\AbstractConverter;
use League\CommonMark\CommonMarkConverter;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Converte Markdown para PDF.
 */
class MarkdownToPdfConverter extends AbstractConverter
{
    public function convert(string $sourcePath, string $destinationPath): void
    {
        $this->ensureFileReadable($sourcePath);
        $this->ensureDirectoryWritable(dirname($destinationPath));

        $markdown = file_get_contents($sourcePath);

        if ($markdown === false) {
            throw new \RuntimeException('Failed to read Markdown file.');
        }

        $converter = new CommonMarkConverter();
        $htmlContent = $converter->convert($markdown)->getContent();

        $html = $this->wrapHtml($htmlContent);

        $dompdf = new Dompdf($this->getOptions());
        $dompdf->loadHtml($html);
        $dompdf->render();

        file_put_contents($destinationPath, $dompdf->output());
    }

    protected function wrapHtml(string $content): string
    {
        return <<<HTML
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; font-size: 14px; line-height:1.6 }
pre { background:#f4f4f4;padding:10px }
code { background:#f4f4f4;padding:2px 4px }
table { border-collapse: collapse; width:100% }
td, th { border:1px solid #ccc;padding:6px }
</style>
</head>
<body>
$content
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