<?php

/**
 * @see       https://github.com/laminas/laminas-config-aggregator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config-aggregator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config-aggregator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ConfigAggregator;

use Laminas\ConfigAggregator\ArrayProvider;
use PHPUnit\Framework\TestCase;

class ArrayProviderTest extends TestCase
{
    public function testProviderIsCallable(): void
    {
        $provider = new ArrayProvider([]);
        self::assertIsCallable($provider);
    }

    public function testProviderReturnsArrayProvidedAtConstruction(): void
    {
        $expected = [
            'foo' => 'bar',
        ];
        $provider = new ArrayProvider($expected);

        self::assertSame($expected, $provider());
    }
}
