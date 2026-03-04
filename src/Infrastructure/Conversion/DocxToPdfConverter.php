<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Conversion;

use PhpOffice\PhpWord\IOFactory;
use FragosoSoftware\FileConverter\Core\Conversion\AbstractConverter;

class DocxToPdfConverter extends AbstractConverter
{
     public function convert(string $sourcePath, string $destinationPath): void
     {
         $phpWord = IOFactory::load($sourcePath);
 
         $writer = IOFactory::createWriter($phpWord, 'PDF');
         $writer->save($destinationPath);
     }
}