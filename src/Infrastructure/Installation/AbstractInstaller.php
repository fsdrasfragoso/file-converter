<?php

namespace FragosoSoftware\FileConverter\Infrastructure\Installation;

use FragosoSoftware\FileConverter\Contracts\Installation\ToolInstallerInterface;
use FragosoSoftware\FileConverter\Contracts\Installation\OsDetectorInterface;
use FragosoSoftware\FileConverter\Contracts\Installation\CommandRunnerInterface;
use FragosoSoftware\FileConverter\Core\Installation\InstallPlan;
use FragosoSoftware\FileConverter\Core\Installation\InstallationReport;

abstract class AbstractInstaller implements ToolInstallerInterface
{
    protected OsDetectorInterface $osDetector;
    protected CommandRunnerInterface $runner;

    public function __construct(
        OsDetectorInterface $osDetector,
        CommandRunnerInterface $runner
    ) {
        $this->osDetector = $osDetector;
        $this->runner = $runner;
    }

    public function install(array $tools, int $timeoutSeconds = 1800): InstallationReport
    {
        $plan = $this->plan($tools);

        $results = [];
        foreach ($plan->commands() as $command) {
            $results[] = $this->runner->run($command, $timeoutSeconds);
        }

        return new InstallationReport($results);
    }

    protected function unique(array $tools): array
    {
        $tools = array_values(array_unique($tools));
        sort($tools);
        return $tools;
    }

    protected function mapTools(array $tools, array $map): array
    {
        $packages = [];

        foreach ($tools as $tool) {
            if (isset($map[$tool])) {
                $packages[] = $map[$tool];
            }
        }

        return array_values(array_unique($packages));
    }
}