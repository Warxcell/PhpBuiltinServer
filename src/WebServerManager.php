<?php

declare(strict_types=1);

namespace Arxy\Codecept\PhpBuiltinServer;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

use function array_filter;
use function array_merge;
use function sprintf;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class WebServerManager
{
    use WebServerReadinessProbeTrait;

    private string $hostname;
    private int $port;
    private string $readinessPath;
    private Process $process;

    /**
     * @throws \RuntimeException
     */
    public function __construct(
        string $documentRoot,
        string $hostname,
        int $port,
        string $router = '',
        string $readinessPath = '',
        array $env = null
    ) {
        $this->hostname = $hostname;
        $this->port = $port;
        $this->readinessPath = $readinessPath;

        $finder = new PhpExecutableFinder();
        if (false === $binary = $finder->find(false)) {
            throw new \RuntimeException('Unable to find the PHP binary.');
        }

        $this->process = new Process(
            array_filter(
                array_merge(
                    [$binary],
                    $finder->findArguments(),
                    [
                        '-dvariables_order=EGPCS',
                        sprintf('-dcodecept.router=%s', $router),
                        '-S',
                        sprintf('%s:%d', $this->hostname, $this->port),
                        '-t',
                        $documentRoot,
                        __DIR__ . '/Router.php',
                    ]
                )
            ),
            $documentRoot,
            $env,
            null,
            null
        );
        //        $this->process->disableOutput();
    }

    public function getEnv(): array
    {
        return $this->process->getEnv();
    }

    public function getHostname(): string
    {
        return $this->hostname;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function start(): void
    {
        $this->checkPortAvailable($this->hostname, $this->port);
        $this->process->start();

        $url = "http://$this->hostname:$this->port";

        if ($this->readinessPath) {
            $url .= $this->readinessPath;
        }

        $this->waitUntilReady($this->process, $url, 'web server', true);
    }

    public function getStdout(): string
    {
        if (!$this->process->isStarted()) {
            return '';
        }

        return $this->process->getOutput();
    }

    public function getStderr(): string
    {
        if (!$this->process->isStarted()) {
            return '';
        }

        return $this->process->getErrorOutput();
    }

    /**
     * @throws \RuntimeException
     */
    public function quit(): void
    {
        $this->process->stop();
    }

    public function isStarted(): bool
    {
        return $this->process->isStarted();
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function __destruct()
    {
        $this->quit();
    }
}
