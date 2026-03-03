<?php

namespace FragosoSoftware\FileConverter\Tests\Installation;

use PHPUnit\Framework\TestCase;
use FragosoSoftware\FileConverter\Core\Installation\Tool;
use FragosoSoftware\FileConverter\Contracts\Installation\OsDetectorInterface;
use FragosoSoftware\FileConverter\Contracts\Installation\CommandRunnerInterface;
use FragosoSoftware\FileConverter\Infrastructure\Installation\WindowsChocoInstaller;

final class WindowsChocoInstallerTest extends TestCase
{
    public function test_it_generates_choco_command_for_libreoffice(): void
    {
        $os = $this->createMock(OsDetectorInterface::class);
        $runner = $this->createMock(CommandRunnerInterface::class);

        $os->method('detect')->willReturn('windows');
        $runner->method('exists')->willReturn(true);

        $installer = new WindowsChocoInstaller($os, $runner);

        $plan = $installer->plan([Tool::LIBREOFFICE]);

        $commands = $plan->commands();

        $this->assertNotEmpty($commands);
        $this->assertStringContainsString('choco install', $commands[0]);
        $this->assertStringContainsString('libreoffice', $commands[0]);
    }
}