<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Conversion;

use FragosoSoftware\FileConverter\Core\Conversion\AbstractConverter;
use Dompdf\Dompdf;
use Dompdf\Options;

class HtmlToPdfConverter extends AbstractConverter
{
    public function convert(string $sourcePath, string $destinationPath): void
    {
        $this->ensureFileReadable($sourcePath);
        $this->ensureDirectoryWritable(dirname($destinationPath));

        $html = file_get_contents($sourcePath);

        $this->convertHtmlString($html, $destinationPath);
    }

    public function convertHtmlString(string $html, string $destinationPath): void
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        file_put_contents($destinationPath, $dompdf->output());
    }
}