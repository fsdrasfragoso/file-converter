<?php

namespace FragosoSoftware\FileConverter\Tests\Installation;

use PHPUnit\Framework\TestCase;
use FragosoSoftware\FileConverter\Core\Installation\InstallerManager;
use FragosoSoftware\FileConverter\Core\Installation\Tool;
use FragosoSoftware\FileConverter\Contracts\Installation\OsDetectorInterface;
use FragosoSoftware\FileConverter\Contracts\Installation\CommandRunnerInterface;
use FragosoSoftware\FileConverter\Infrastructure\Installation\LinuxAptInstaller;
use FragosoSoftware\FileConverter\Infrastructure\Installation\NullInstaller;

final class InstallerManagerTest extends TestCase
{
    public function test_it_resolves_linux_apt_installer(): void
    {
        $os = $this->createMock(OsDetectorInterface::class);
        $runner = $this->createMock(CommandRunnerInterface::class);

        $os->method('detect')->willReturn('linux');
        $runner->method('exists')->willReturn(true);

        $manager = new InstallerManager([
            new LinuxAptInstaller($os, $runner),
            new NullInstaller($os, $runner),
        ]);

        $plan = $manager->plan([Tool::FFMPEG]);

        $this->assertNotEmpty($plan->commands());
        $this->assertStringContainsString('apt-get', $plan->commands()[0]);
    }
}