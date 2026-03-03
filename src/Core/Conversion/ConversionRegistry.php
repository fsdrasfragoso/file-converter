<?php

namespace FragosoSoftware\FileConverter\Core\Conversion;

class ConversionRegistry
{
    protected array $map = [];

    public function register(string $from, string $to, string $converterClass): void
    {
        $this->map[strtolower($from)][strtolower($to)] = $converterClass;
    }

    public function resolve(string $from, string $to): string
    {
        $from = strtolower($from);
        $to   = strtolower($to);

        if (!isset($this->map[$from][$to])) {
            throw new \InvalidArgumentException("Conversão {$from} → {$to} não registrada.");
        }

        return $this->map[$from][$to];
    }
}