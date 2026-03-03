<?php
declare(strict_types=1);

namespace FragosoSoftware\FileConverter\Core\Installation;

final class InstallPlan
{
    private array $commands;
    private array $notes;

    public function __construct(array $commands = [], array $notes = [])
    {
        $this->commands = $commands;
        $this->notes = $notes;
    }

    public function commands(): array
    {
        return $this->commands;
    }

    public function notes(): array
    {
        return $this->notes;
    }

    public function withCommand(string $command): self
    {
        $clone = clone $this;
        $clone->commands[] = $command;
        return $clone;
    }

    public function withNote(string $note): self
    {
        $clone = clone $this;
        $clone->notes[] = $note;
        return $clone;
    }
}