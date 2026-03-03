<?php

namespace FragosoSoftware\FileConverter\Core\Installation;

final class CommandResult
{
    public bool $ok;
    public int $exitCode;
    public string $stdout;
    public string $stderr;
    public string $command;

    public function __construct(string $command, int $exitCode, string $stdout, string $stderr)
    {
        $this->command = $command;
        $this->exitCode = $exitCode;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
        $this->ok = ($exitCode === 0);
    }
}