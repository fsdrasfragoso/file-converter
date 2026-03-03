<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Conversion;

use PhpOffice\PhpWord\IOFactory;
use FragosoSoftware\FileConverter\Core\Conversion\AbstractConverter;

class DocxToPdfConverter extends AbstractConverter
{
    public function convert(): void
    {
        $phpWord = IOFactory::load($this->source);

        $writer = IOFactory::createWriter($phpWord, 'PDF');
        $writer->save($this->destination);
    }
}