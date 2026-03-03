<?php

namespace FragosoSoftware\FileConverter\Tests\Installation;

use PHPUnit\Framework\TestCase;
use FragosoSoftware\FileConverter\Core\Installation\Tool;
use FragosoSoftware\FileConverter\Contracts\Installation\OsDetectorInterface;
use FragosoSoftware\FileConverter\Contracts\Installation\CommandRunnerInterface;
use FragosoSoftware\FileConverter\Infrastructure\Installation\LinuxAptInstaller;

final class LinuxAptInstallerTest extends TestCase
{
    public function test_it_generates_correct_install_commands(): void
    {
        $os = $this->createMock(OsDetectorInterface::class);
        $runner = $this->createMock(CommandRunnerInterface::class);

        $os->method('detect')->willReturn('linux');
        $runner->method('exists')->willReturn(true);

        $installer = new LinuxAptInstaller($os, $runner);

        $plan = $installer->plan([Tool::FFMPEG, Tool::IMAGEMAGICK]);

        $commands = $plan->commands();

        $this->assertCount(2, $commands);
        $this->assertStringContainsString('apt-get update', $commands[0]);
        $this->assertStringContainsString('apt-get install', $commands[1]);
        $this->assertStringContainsString('ffmpeg', $commands[1]);
        $this->assertStringContainsString('imagemagick', $commands[1]);
    }
}