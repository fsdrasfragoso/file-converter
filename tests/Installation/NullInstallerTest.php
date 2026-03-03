<?php

namespace FragosoSoftware\FileConverter\Tests\Installation;

use PHPUnit\Framework\TestCase;
use FragosoSoftware\FileConverter\Contracts\Installation\OsDetectorInterface;
use FragosoSoftware\FileConverter\Contracts\Installation\CommandRunnerInterface;
use FragosoSoftware\FileConverter\Infrastructure\Installation\NullInstaller;

final class NullInstallerTest extends TestCase
{
    public function test_it_returns_empty_plan(): void
    {
        $os = $this->createMock(OsDetectorInterface::class);
        $runner = $this->createMock(CommandRunnerInterface::class);

        $installer = new NullInstaller($os, $runner);

        $plan = $installer->plan([]);

        $this->assertEmpty($plan->commands());
        $this->assertNotEmpty($plan->notes());
    }
}