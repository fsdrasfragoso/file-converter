<?php

namespace FragosoSoftware\FileConverter\Contracts\Installation;

interface OsDetectorInterface
{
    public function detect(): string;
}