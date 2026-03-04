<?php

namespace FragosoSoftware\FileConverter\Contracts\Conversion;

interface ConverterInterface
{
    public function convert(string $sourcePath, string $destinationPath): void;

    public function convertFromBinary(string $binary): string;

    public function convertFromBase64(string $base64): string;
}