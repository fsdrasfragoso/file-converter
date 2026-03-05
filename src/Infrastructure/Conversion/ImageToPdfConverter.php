<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Conversion;

use FragosoSoftware\FileConverter\Core\Conversion\AbstractConverter;
use Spatie\Image\Image;

class ImageToPdfConverter extends AbstractConverter
{
    public function convert(string $sourcePath, string $destinationPath): void
    {
        $this->ensureFileReadable($sourcePath);
        $this->ensureDirectoryWritable(dirname($destinationPath));

        Image::load($sourcePath)
            ->savePdf($destinationPath);
    }
}