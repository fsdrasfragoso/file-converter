<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Installation;

use FragosoSoftware\FileConverter\Contracts\Installation\CommandRunnerInterface;
use FragosoSoftware\FileConverter\Core\Installation\CommandResult;

final class ShellCommandRunner implements CommandRunnerInterface
{
    public function run(string $command, int $timeoutSeconds = 600): CommandResult
    {
        exec($command . ' 2>&1', $output, $exitCode);

        return new CommandResult(
            $command,
            $exitCode,
            implode("\n", $output),
            ''
        );
    }

    public function exists(string $binary): bool
    {
        $command = PHP_OS_FAMILY === 'Windows'
            ? "where $binary"
            : "command -v $binary";

        exec($command, $output, $exitCode);

        return $exitCode === 0;
    }
}