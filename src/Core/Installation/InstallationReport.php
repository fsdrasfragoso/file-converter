<?php

namespace FragosoSoftware\FileConverter\Core\Installation;

final class InstallationReport
{
    private array $results;

    public function __construct(array $results)
    {
        $this->results = $results;
    }

    public function results(): array
    {
        return $this->results;
    }

    public function ok(): bool
    {
        foreach ($this->results as $result) {
            if (!$result->ok) {
                return false;
            }
        }
        return true;
    }
}