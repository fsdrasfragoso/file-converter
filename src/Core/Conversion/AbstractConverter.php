<?php

namespace FragosoSoftware\FileConverter\Core\Conversion;

use FragosoSoftware\FileConverter\Contracts\Conversion\ConverterInterface;

abstract class AbstractConverter implements ConverterInterface
{
    protected string $source;
    protected string $destination;

    public function __construct(?string $source = null, ?string $destination = null)
    {
        $this->source = $source ?? '';
        $this->destination = $destination ?? '';
    }

    abstract public function convert(string $sourcePath, string $destinationPath): void;

    public function convertFromBinary(string $binary): string
    {
        $input = tmpfile();
        $meta = stream_get_meta_data($input);
        $inputPath = $meta['uri'];

        fwrite($input, $binary);

        $outputPath = tempnam(sys_get_temp_dir(), 'conv_');

        $this->convert($inputPath, $outputPath);

        $result = file_get_contents($outputPath);

        unlink($outputPath);
        fclose($input);

        return $result;
    }

    public function convertFromBase64(string $base64): string
    {
        $binary = base64_decode($base64);

        $converted = $this->convertFromBinary($binary);

        return base64_encode($converted);
    }
}