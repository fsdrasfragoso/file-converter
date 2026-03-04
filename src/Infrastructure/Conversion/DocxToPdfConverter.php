<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Conversion;

use PhpOffice\PhpWord\IOFactory;
use FragosoSoftware\FileConverter\Core\Conversion\AbstractConverter;
use PhpOffice\PhpWord\Settings;
use Dompdf\Dompdf;

class DocxToPdfConverter extends AbstractConverter
{
     public function convert(string $sourcePath, string $destinationPath): void
     {
        Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
        
        Settings::setPdfRendererPath(
            dirname(dirname((new \ReflectionClass(Dompdf::class))->getFileName()))
        );
        
         $phpWord = IOFactory::load($sourcePath);
 
         $writer = IOFactory::createWriter($phpWord, 'PDF');
         $writer->save($destinationPath);
     }
}