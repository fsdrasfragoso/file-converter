<?php

namespace FragosoSoftware\FileConverter\Core\Conversion;

use FragosoSoftware\FileConverter\Contracts\Conversion\ConverterInterface;

abstract class AbstractConverter implements ConverterInterface
{
    protected string $source;
    protected string $destination;

    public function __construct(string $source, string $destination)
    {
        $this->source = $source;
        $this->destination = $destination;

        $this->validate();
    }

    protected function validate(): void
    {
        if (!file_exists($this->source)) {
            throw new \InvalidArgumentException("Arquivo de origem não encontrado: {$this->source}");
        }
    }

    abstract public function convert(): void;
}