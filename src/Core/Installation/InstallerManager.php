<?php

namespace FragosoSoftware\FileConverter\Core\Installation;

use FragosoSoftware\FileConverter\Contracts\Installation\OsDetectorInterface;
use FragosoSoftware\FileConverter\Contracts\Installation\CommandRunnerInterface;
use FragosoSoftware\FileConverter\Contracts\Installation\ToolInstallerInterface;
use FragosoSoftware\FileConverter\Infrastructure\Installation\{
    NativeOsDetector,
    ShellCommandRunner,
    LinuxAptInstaller,
    LinuxDnfYumInstaller,
    LinuxApkInstaller,
    MacBrewInstaller,
    WindowsChocoInstaller,
    NullInstaller
};

final class InstallerManager
{
    private array $installers;

    public function __construct(array $installers)
    {
        $this->installers = $installers;
    }

    public static function default(): self
    {
        $os = new NativeOsDetector();
        $runner = new ShellCommandRunner();

        return new self([
            new LinuxAptInstaller($os, $runner),
            new LinuxDnfYumInstaller($os, $runner),
            new LinuxApkInstaller($os, $runner),
            new MacBrewInstaller($os, $runner),
            new WindowsChocoInstaller($os, $runner),
            new NullInstaller($os, $runner),
        ]);
    }

    private function resolve(): ToolInstallerInterface
    {
        foreach ($this->installers as $installer) {
            if ($installer->supports()) {
                return $installer;
            }
        }

        throw new \RuntimeException('No installer available.');
    }

    public function plan(array $tools): InstallPlan
    {
        return $this->resolve()->plan($tools);
    }

    public function install(array $tools, int $timeoutSeconds = 1800): InstallationReport
    {
        return $this->resolve()->install($tools, $timeoutSeconds);
    }
}