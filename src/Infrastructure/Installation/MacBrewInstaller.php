<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Installation;

use FragosoSoftware\FileConverter\Core\Installation\InstallPlan;
use FragosoSoftware\FileConverter\Core\Installation\OsFamily;
use FragosoSoftware\FileConverter\Core\Installation\Tool;

final class MacBrewInstaller extends AbstractInstaller
{
    public function supports(): bool
    {
        return $this->osDetector->detect() === OsFamily::MAC
            && $this->runner->exists('brew');
    }

    public function plan(array $tools): InstallPlan
    {
        $tools = $this->unique($tools);

        $packages = $this->mapTools($tools, [
            Tool::FFMPEG => 'ffmpeg',
            Tool::IMAGEMAGICK => 'imagemagick',
            Tool::GHOSTSCRIPT => 'ghostscript',
            Tool::LIBREOFFICE => 'libreoffice',
        ]);

        if (!$packages) {
            return new InstallPlan([], ['No packages to install.']);
        }

        return new InstallPlan([
            'brew update',
            'brew install ' . implode(' ', $packages),
        ], ['Using brew (macOS).']);
    }
}