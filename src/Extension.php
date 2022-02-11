<?php

declare(strict_types=1);

namespace Arxy\Codecept\PhpBuiltinServer;

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Extension as CodeceptExtension;
use Codeception\Test\Descriptor;
use Codeception\TestInterface;
use Throwable;

use function codecept_debug;
use function codecept_output_dir;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function preg_replace;
use function realpath;
use function sprintf;

final class Extension extends CodeceptExtension
{
    public static $events = [
        'suite.before' => 'beforeSuite',
        'suite.after' => 'afterSuite',
        'test.after' => 'afterTest',
    ];

    private WebServerManager $webServerManager;

    public function __construct(array $config, array $options)
    {
        parent::__construct($config, $options);

        $this->webServerManager = new WebServerManager(
            realpath($config['documentRoot']),
            $config['hostname'],
            (int)$config['port'],
            $config['router'] ?? '',
            $config['readinessPath'] ?? '',
            $config['env'] ?? [],
        );
    }

    /**
     * @throws Throwable
     */
    public function beforeSuite(SuiteEvent $event): void
    {
        codecept_debug(
            sprintf(
                'Starting web server on %s:%s',
                $this->webServerManager->getHostname(),
                $this->webServerManager->getPort()
            )
        );

        try {
            $this->webServerManager->start();
        } catch (Throwable $e) {
            file_put_contents(
                codecept_output_dir() . 'server_stdout.txt',
                $this->webServerManager->getStdout()
            );
            file_put_contents(
                codecept_output_dir() . 'server_stderr.txt',
                $this->webServerManager->getStderr()
            );

            throw $e;
        }
    }

    private function getTestName(TestInterface $test): string
    {
        return preg_replace('~[^a-zA-Z0-9\x80-\xff]~', '.', Descriptor::getTestSignatureUnique($test));
    }

    public function afterTest(TestEvent $event): void
    {
        $test = $event->getTest();

        $subFolder = codecept_output_dir() . 'server';
        if (!is_dir($subFolder)) {
            mkdir($subFolder);
        }

        $name = $this->getTestName($test);

        file_put_contents(
            $subFolder . sprintf('/%s_stdout.txt', $name),
            $this->webServerManager->getProcess()->getIncrementalOutput()
        );
        file_put_contents(
            $subFolder . sprintf('/%s_stderr.txt', $name),
            $this->webServerManager->getProcess()->getIncrementalErrorOutput()
        );
    }

    public function afterSuite(): void
    {
        codecept_debug(
            sprintf(
                'Stopping web server',
            )
        );
        $this->webServerManager->quit();
    }
}
