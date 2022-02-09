<?php

declare(strict_types=1);

namespace Arxy\Codecept\PhpBuiltinServer;

use Codeception\Exception\ExtensionException;
use Codeception\Exception\ModuleConfigException;
use Codeception\Extension as CodeceptExtension;

use function realpath;

final class Extension extends CodeceptExtension
{
    public static $events = [
        'suite.before' => 'beforeSuite',
        'suite.after' => 'afterSuite',
    ];

    private WebServerManager $webServerManager;

    public function __construct(array $config, array $options)
    {
        parent::__construct($config, $options);

        $this->webServerManager = new WebServerManager(
            realpath($config['documentRoot']),
            $config['hostname'],
            $config['port'],
            $config['router'] ?? '',
            $config['readinessPath'] ?? '',
            $config['env'] ?? [],
        );
    }

    public function beforeSuite()
    {
        $this->webServerManager->start();
    }

    public function afterSuite()
    {
        $this->webServerManager->quit();
    }

    public function __destruct()
    {
        unset($this->webServerManager);
    }
}
