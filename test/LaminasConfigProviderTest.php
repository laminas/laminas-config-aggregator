<?php

declare(strict_types=1);

namespace LaminasTest\ConfigAggregator;

use Laminas\ConfigAggregator\LaminasConfigProvider;
use PHPUnit\Framework\TestCase;

class LaminasConfigProviderTest extends TestCase
{
    public function testProviderLoadsConfigFromFiles(): void
    {
        $provider = new LaminasConfigProvider(__DIR__ . '/Resources/laminas-config/config.*');
        $config   = $provider();
        self::assertSame(
            [
                'database' => [
                    'host'     => 'db.example.com',
                    'database' => 'dbproduction',
                    'user'     => 'dbuser',
                    'password' => 'secret',
                    'adapter'  => 'pdo',
                ],
            ],
            $config
        );
    }
}
