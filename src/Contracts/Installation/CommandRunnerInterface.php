<?php

namespace FragosoSoftware\FileConverter\Contracts\Installation;

use FragosoSoftware\FileConverter\Core\Installation\CommandResult;

interface CommandRunnerInterface
{
    public function run(string $command, int $timeoutSeconds = 600): CommandResult;

    public function exists(string $binary): bool;
}