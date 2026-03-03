<?php

namespace FragosoSoftware\FileConverter\Core\Conversion;

use FragosoSoftware\FileConverter\Contracts\Conversion\ConverterInterface;
use FragosoSoftware\FileConverter\Infrastructure\Conversion\DocxToPdfConverter;

class ConverterManager
{
    protected ConversionRegistry $registry;

    public function __construct()
    {
        $this->registry = new ConversionRegistry();

        $this->registerDefaultConverters();
    }

    protected function registerDefaultConverters(): void
    {
        $this->registry->register('docx', 'pdf', DocxToPdfConverter::class);
        
    }

    public function convert(string $source, string $destination): void
    {
        $from = pathinfo($source, PATHINFO_EXTENSION);
        $to   = pathinfo($destination, PATHINFO_EXTENSION);

        $converterClass = $this->registry->resolve($from, $to);

        $converter = new $converterClass($source, $destination);

        if (!$converter instanceof ConverterInterface) {
            throw new \LogicException("Conversor inválido.");
        }

        $converter->convert();
    }
}