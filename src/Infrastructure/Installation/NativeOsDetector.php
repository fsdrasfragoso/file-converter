<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Installation;

use FragosoSoftware\FileConverter\Contracts\Installation\OsDetectorInterface;
use FragosoSoftware\FileConverter\Core\Installation\OsFamily;

final class NativeOsDetector implements OsDetectorInterface
{
    public function detect(): string
    {
        switch (\PHP_OS_FAMILY) {
            case 'Linux':
                return OsFamily::LINUX;
            case 'Darwin':
                return OsFamily::MAC;
            case 'Windows':
                return OsFamily::WINDOWS;
            default:
                return OsFamily::UNKNOWN;
        }
    }
}