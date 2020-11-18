<?php

/**
 * @see       https://github.com/laminas/laminas-config-aggregator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config-aggregator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config-aggregator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ConfigAggregator;

use Laminas\ConfigAggregator\PhpFileProvider;
use Laminas\Stdlib\ArrayUtils;
use PHPUnit\Framework\TestCase;

class PhpFileProviderTest extends TestCase
{
    public function testProviderLoadsConfigFromFiles(): void
    {
        $provider = new PhpFileProvider(__DIR__ . '/Resources/config/{{,*.}global,{,*.}local}.php');
        $merged = [];
        foreach ($provider() as $item) {
            $merged = ArrayUtils::merge($merged, $item);
        }
        self::assertSame(['fruit' => 'banana', 'vegetable' => 'potato'], $merged);
    }
}
