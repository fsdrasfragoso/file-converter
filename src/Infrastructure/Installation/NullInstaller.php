<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Installation;

use FragosoSoftware\FileConverter\Core\Installation\InstallPlan;

final class NullInstaller extends AbstractInstaller
{
    public function supports(): bool
    {
        return true;
    }

    public function plan(array $tools): InstallPlan
    {
        return new InstallPlan([], [
            'No compatible installer detected.',
            'Install tools manually on this system.'
        ]);
    }
}