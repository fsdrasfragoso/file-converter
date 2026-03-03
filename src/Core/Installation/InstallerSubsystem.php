<?php
// File: src/Core/Installation/InstallerSubsystem.php
declare(strict_types=1);

namespace FragosoSoftware\FileConverter\Core\Installation;

use RuntimeException;

/**
 * OS families supported by the installer subsystem.
 */
final class OsFamily
{
    public const LINUX = 'linux';
    public const MAC = 'mac';
    public const WINDOWS = 'windows';
    public const UNKNOWN = 'unknown';

    private function __construct() {}
}

/**
 * Represents a tool the library may depend on.
 * Keep this list small at first; expand per driver needs.
 */
final class Tool
{
    public const FFMPEG = 'ffmpeg';
    public const IMAGEMAGICK = 'imagemagick';
    public const GHOSTSCRIPT = 'ghostscript';
    public const LIBREOFFICE = 'libreoffice';

    private function __construct() {}
}

/**
 * Result of planning an installation: commands + notes.
 */
final class InstallPlan
{
    /** @var list<string> */
    private array $commands;

    /** @var list<string> */
    private array $notes;

    /**
     * @param list<string> $commands
     * @param list<string> $notes
     */
    public function __construct(array $commands = [], array $notes = [])
    {
        $this->commands = $commands;
        $this->notes = $notes;
    }

    /** @return list<string> */
    public function commands(): array
    {
        return $this->commands;
    }

    /** @return list<string> */
    public function notes(): array
    {
        return $this->notes;
    }

    public function isEmpty(): bool
    {
        return $this->commands === [];
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

/**
 * Execution report when running an InstallPlan.
 */
final class InstallationReport
{
    /** @var list<CommandResult> */
    private array $results;

    /** @param list<CommandResult> $results */
    public function __construct(array $results)
    {
        $this->results = $results;
    }

    /** @return list<CommandResult> */
    public function results(): array
    {
        return $this->results;
    }

    public function ok(): bool
    {
        foreach ($this->results as $r) {
            if (!$r->ok) {
                return false;
            }
        }
        return true;
    }
}

/**
 * Low-level command result.
 */
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

/**
 * Port: detects the server OS family.
 */
interface OsDetectorInterface
{
    /** @return OsFamily::* */
    public function detect(): string;
}

/**
 * Adapter: native PHP OS detection.
 */
final class NativeOsDetector implements OsDetectorInterface
{
    public function detect(): string
    {
        $family = \PHP_OS_FAMILY;

        if ($family === 'Linux') {
            return OsFamily::LINUX;
        }
        if ($family === 'Darwin') {
            return OsFamily::MAC;
        }
        if ($family === 'Windows') {
            return OsFamily::WINDOWS;
        }

        return OsFamily::UNKNOWN;
    }
}

/**
 * Port: runs shell commands (optional; can be replaced/mocked in tests).
 */
interface CommandRunnerInterface
{
    public function run(string $command, int $timeoutSeconds = 600): CommandResult;

    public function exists(string $binary): bool;
}

/**
 * Adapter: runs commands via proc_open.
 */
final class ShellCommandRunner implements CommandRunnerInterface
{
    public function run(string $command, int $timeoutSeconds = 600): CommandResult
    {
        $descriptor = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = \proc_open($command, $descriptor, $pipes);
        if (!\is_resource($process)) {
            throw new RuntimeException('Failed to start process.');
        }

        \fclose($pipes[0]);
        \stream_set_blocking($pipes[1], true);
        \stream_set_blocking($pipes[2], true);

        $start = \time();
        $stdout = '';
        $stderr = '';

        while (true) {
            $status = \proc_get_status($process);
            if (!$status['running']) {
                break;
            }
            if ((\time() - $start) > $timeoutSeconds) {
                \proc_terminate($process, 9);
                $stderr .= "\nTimed out after {$timeoutSeconds}s";
                break;
            }
            \usleep(100_000);
        }

        $stdout .= (string) \stream_get_contents($pipes[1]);
        $stderr .= (string) \stream_get_contents($pipes[2]);
        \fclose($pipes[1]);
        \fclose($pipes[2]);

        $exitCode = \proc_close($process);
        return new CommandResult($command, (int) $exitCode, $stdout, $stderr);
    }

    public function exists(string $binary): bool
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            $r = $this->run('where ' . \escapeshellarg($binary));
            return $r->ok && \trim($r->stdout) !== '';
        }

        $r = $this->run('command -v ' . \escapeshellarg($binary));
        return $r->ok && \trim($r->stdout) !== '';
    }
}

/**
 * Port: an installer capable of planning/installing tools.
 */
interface ToolInstallerInterface
{
    public function supports(): bool;

    /** @param list<string> $tools */
    public function plan(array $tools): InstallPlan;

    /** @param list<string> $tools */
    public function install(array $tools, int $timeoutSeconds = 1800): InstallationReport;
}

/**
 * Use case: selects the best installer based on OS + available package manager.
 */
final class InstallerManager
{
    private OsDetectorInterface $osDetector;
    private CommandRunnerInterface $runner;

    /** @var list<ToolInstallerInterface> */
    private array $installers;

    /**
     * @param list<ToolInstallerInterface> $installers
     */
    public function __construct(
        OsDetectorInterface $osDetector,
        CommandRunnerInterface $runner,
        array $installers
    ) {
        $this->osDetector = $osDetector;
        $this->runner = $runner;
        $this->installers = $installers;
    }

    public static function default(): self
    {
        $os = new NativeOsDetector();
        $runner = new ShellCommandRunner();

        $installers = [
            new LinuxAptInstaller($os, $runner),
            new LinuxDnfYumInstaller($os, $runner),
            new LinuxApkInstaller($os, $runner),
            new MacBrewInstaller($os, $runner),
            new WindowsChocoInstaller($os, $runner),
        ];

        return new self($os, $runner, $installers);
    }

    public function resolveInstaller(): ToolInstallerInterface
    {
        foreach ($this->installers as $installer) {
            if ($installer->supports()) {
                return $installer;
            }
        }
        return new NullInstaller($this->osDetector, $this->runner);
    }

    /** @param list<string> $tools */
    public function plan(array $tools): InstallPlan
    {
        return $this->resolveInstaller()->plan($tools);
    }

    /** @param list<string> $tools */
    public function install(array $tools, int $timeoutSeconds = 1800): InstallationReport
    {
        return $this->resolveInstaller()->install($tools, $timeoutSeconds);
    }
}

/**
 * Base class for installers (shared mapping + execution).
 */
abstract class AbstractInstaller implements ToolInstallerInterface
{
    protected OsDetectorInterface $osDetector;
    protected CommandRunnerInterface $runner;

    public function __construct(OsDetectorInterface $osDetector, CommandRunnerInterface $runner)
    {
        $this->osDetector = $osDetector;
        $this->runner = $runner;
    }

    public function install(array $tools, int $timeoutSeconds = 1800): InstallationReport
    {
        $plan = $this->plan($tools);
        $results = [];

        foreach ($plan->commands() as $cmd) {
            $results[] = $this->runner->run($cmd, $timeoutSeconds);
        }

        return new InstallationReport($results);
    }

    /** @param list<string> $tools */
    protected function uniqueTools(array $tools): array
    {
        $tools = \array_values(\array_unique(\array_map('strval', $tools)));
        \sort($tools);
        return $tools;
    }
}

final class NullInstaller extends AbstractInstaller
{
    public function supports(): bool
    {
        return true;
    }

    public function plan(array $tools): InstallPlan
    {
        $family = $this->osDetector->detect();

        $plan = new InstallPlan([], [
            "No supported installer detected for OS family: {$family}.",
            "You can still install tools manually and use the drivers normally.",
        ]);

        $tools = $this->uniqueTools($tools);
        if ($tools !== []) {
            $plan = $plan->withNote('Requested tools: ' . \implode(', ', $tools));
        }

        return $plan;
    }
}

/**
 * Linux (Debian/Ubuntu): apt-get
 */
final class LinuxAptInstaller extends AbstractInstaller
{
    public function supports(): bool
    {
        return $this->osDetector->detect() === OsFamily::LINUX
            && $this->runner->exists('apt-get');
    }

    public function plan(array $tools): InstallPlan
    {
        $packages = $this->mapToolsToPackages($this->uniqueTools($tools));
        if ($packages === []) {
            return new InstallPlan([], ['No packages to install.']);
        }

        $cmds = [
            'sudo apt-get update',
            'sudo apt-get install -y ' . \implode(' ', \array_map('escapeshellarg', $packages)),
        ];

        return new InstallPlan($cmds, ['Using apt-get (Debian/Ubuntu).']);
    }

    /** @param list<string> $tools @return list<string> */
    private function mapToolsToPackages(array $tools): array
    {
        $map = [
            Tool::FFMPEG => 'ffmpeg',
            Tool::IMAGEMAGICK => 'imagemagick',
            Tool::GHOSTSCRIPT => 'ghostscript',
            Tool::LIBREOFFICE => 'libreoffice',
        ];

        $pkgs = [];
        foreach ($tools as $t) {
            if (isset($map[$t])) {
                $pkgs[] = $map[$t];
            }
        }
        return \array_values(\array_unique($pkgs));
    }
}

/**
 * Linux (RHEL/CentOS/Fedora/Amazon Linux): dnf or yum
 */
final class LinuxDnfYumInstaller extends AbstractInstaller
{
    public function supports(): bool
    {
        return $this->osDetector->detect() === OsFamily::LINUX
            && ($this->runner->exists('dnf') || $this->runner->exists('yum'));
    }

    public function plan(array $tools): InstallPlan
    {
        $packages = $this->mapToolsToPackages($this->uniqueTools($tools));
        if ($packages === []) {
            return new InstallPlan([], ['No packages to install.']);
        }

        $pm = $this->runner->exists('dnf') ? 'dnf' : 'yum';
        $cmds = [
            'sudo ' . $pm . ' install -y ' . \implode(' ', \array_map('escapeshellarg', $packages)),
        ];

        return new InstallPlan($cmds, ["Using {$pm} (RHEL/CentOS/Fedora)."]);
    }

    /** @param list<string> $tools @return list<string> */
    private function mapToolsToPackages(array $tools): array
    {
        $map = [
            Tool::FFMPEG => 'ffmpeg',
            Tool::IMAGEMAGICK => 'ImageMagick',
            Tool::GHOSTSCRIPT => 'ghostscript',
            Tool::LIBREOFFICE => 'libreoffice',
        ];

        $pkgs = [];
        foreach ($tools as $t) {
            if (isset($map[$t])) {
                $pkgs[] = $map[$t];
            }
        }
        return \array_values(\array_unique($pkgs));
    }
}

/**
 * Linux (Alpine): apk
 */
final class LinuxApkInstaller extends AbstractInstaller
{
    public function supports(): bool
    {
        return $this->osDetector->detect() === OsFamily::LINUX
            && $this->runner->exists('apk');
    }

    public function plan(array $tools): InstallPlan
    {
        $packages = $this->mapToolsToPackages($this->uniqueTools($tools));
        if ($packages === []) {
            return new InstallPlan([], ['No packages to install.']);
        }

        $cmds = [
            'sudo apk add --no-cache ' . \implode(' ', \array_map('escapeshellarg', $packages)),
        ];

        return new InstallPlan($cmds, ['Using apk (Alpine).']);
    }

    /** @param list<string> $tools @return list<string> */
    private function mapToolsToPackages(array $tools): array
    {
        $map = [
            Tool::FFMPEG => 'ffmpeg',
            Tool::IMAGEMAGICK => 'imagemagick',
            Tool::GHOSTSCRIPT => 'ghostscript',
            Tool::LIBREOFFICE => 'libreoffice',
        ];

        $pkgs = [];
        foreach ($tools as $t) {
            if (isset($map[$t])) {
                $pkgs[] = $map[$t];
            }
        }
        return \array_values(\array_unique($pkgs));
    }
}

/**
 * macOS: Homebrew
 */
final class MacBrewInstaller extends AbstractInstaller
{
    public function supports(): bool
    {
        return $this->osDetector->detect() === OsFamily::MAC
            && $this->runner->exists('brew');
    }

    public function plan(array $tools): InstallPlan
    {
        $packages = $this->mapToolsToPackages($this->uniqueTools($tools));
        if ($packages === []) {
            return new InstallPlan([], ['No packages to install.']);
        }

        $cmds = [
            'brew update',
            'brew install ' . \implode(' ', \array_map('escapeshellarg', $packages)),
        ];

        return new InstallPlan($cmds, ['Using brew (macOS).']);
    }

    /** @param list<string> $tools @return list<string> */
    private function mapToolsToPackages(array $tools): array
    {
        $map = [
            Tool::FFMPEG => 'ffmpeg',
            Tool::IMAGEMAGICK => 'imagemagick',
            Tool::GHOSTSCRIPT => 'ghostscript',
            Tool::LIBREOFFICE => 'libreoffice',
        ];

        $pkgs = [];
        foreach ($tools as $t) {
            if (isset($map[$t])) {
                $pkgs[] = $map[$t];
            }
        }
        return \array_values(\array_unique($pkgs));
    }
}

/**
 * Windows: Chocolatey
 */
final class WindowsChocoInstaller extends AbstractInstaller
{
    public function supports(): bool
    {
        return $this->osDetector->detect() === OsFamily::WINDOWS
            && $this->runner->exists('choco');
    }

    public function plan(array $tools): InstallPlan
    {
        $packages = $this->mapToolsToPackages($this->uniqueTools($tools));
        if ($packages === []) {
            return new InstallPlan([], ['No packages to install.']);
        }

        $cmds = [
            'choco install -y ' . \implode(' ', \array_map('escapeshellarg', $packages)),
        ];

        return new InstallPlan($cmds, ['Using choco (Windows).']);
    }

    /** @param list<string> $tools @return list<string> */
    private function mapToolsToPackages(array $tools): array
    {
        $map = [
            Tool::FFMPEG => 'ffmpeg',
            Tool::IMAGEMAGICK => 'imagemagick',
            Tool::GHOSTSCRIPT => 'ghostscript',
            Tool::LIBREOFFICE => 'libreoffice',
        ];

        $pkgs = [];
        foreach ($tools as $t) {
            if (isset($map[$t])) {
                $pkgs[] = $map[$t];
            }
        }
        return \array_values(\array_unique($pkgs));
    }
}