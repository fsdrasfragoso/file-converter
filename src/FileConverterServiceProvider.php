<?php

namespace FragosoSoftware\FileConverter;

use Illuminate\Support\ServiceProvider;

class FileConverterServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/Config/file-converter.php' => config_path('file-converter.php'),
        ], 'file-converter-config');
    }
}