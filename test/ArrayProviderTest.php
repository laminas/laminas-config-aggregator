<?php

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
