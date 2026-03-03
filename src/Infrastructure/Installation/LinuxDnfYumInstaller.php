<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Installation;

use FragosoSoftware\FileConverter\Core\Installation\InstallPlan;
use FragosoSoftware\FileConverter\Core\Installation\OsFamily;
use FragosoSoftware\FileConverter\Core\Installation\Tool;

final class LinuxDnfYumInstaller extends AbstractInstaller
{
    public function supports(): bool
    {
        return $this->osDetector->detect() === OsFamily::LINUX
            && ($this->runner->exists('dnf') || $this->runner->exists('yum'));
    }

    public function plan(array $tools): InstallPlan
    {
        $tools = $this->unique($tools);

        $packages = $this->mapTools($tools, [
            Tool::FFMPEG => 'ffmpeg',
            Tool::IMAGEMAGICK => 'ImageMagick',
            Tool::GHOSTSCRIPT => 'ghostscript',
            Tool::LIBREOFFICE => 'libreoffice',
        ]);

        if (!$packages) {
            return new InstallPlan([], ['No packages to install.']);
        }

        $pm = $this->runner->exists('dnf') ? 'dnf' : 'yum';

        return new InstallPlan([
            "sudo {$pm} install -y " . implode(' ', $packages),
        ], ["Using {$pm} (RHEL/CentOS/Fedora)."]);
    }
}