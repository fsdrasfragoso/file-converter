<?php

namespace FragosoSoftware\FileConverter\Contracts\Installation;

use FragosoSoftware\FileConverter\Core\Installation\InstallPlan;
use FragosoSoftware\FileConverter\Core\Installation\InstallationReport;

interface ToolInstallerInterface
{
    public function supports(): bool;

    public function plan(array $tools): InstallPlan;

    public function install(array $tools, int $timeoutSeconds = 1800): InstallationReport;
}