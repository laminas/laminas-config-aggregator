<?php

/**
 * @see       https://github.com/laminas/laminas-config-aggregator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config-aggregator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config-aggregator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ConfigAggregator;

use Laminas\ConfigAggregator\LaminasConfigProvider;
use Laminas\Stdlib\ArrayUtils;
use PHPUnit\Framework\TestCase;

class LaminasConfigProviderTest extends TestCase
{
    public function testProviderLoadsConfigFromFiles()
    {
        $provider = new LaminasConfigProvider(__DIR__ . '/Resources/laminas-config/config.*');
        $config = $provider();
        $this->assertEquals(
            [
                'database' => [
                    'adapter' => 'pdo',
                    'host' => 'db.example.com',
                    'database' => 'dbproduction',
                    'user' => 'dbuser',
                    'password' => 'secret',
                ],
            ],
            $config
        );
    }
}
