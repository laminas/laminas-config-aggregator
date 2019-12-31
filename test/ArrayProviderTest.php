<?php

/**
 * @see       https://github.com/laminas/laminas-config-aggregator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config-aggregator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config-aggregator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ConfigAggregator;

use Laminas\ConfigAggregator\ArrayProvider;
use PHPUnit_Framework_TestCase as TestCase;

class ArrayProviderTest extends TestCase
{
    public function testProviderIsCallable()
    {
        $provider = new ArrayProvider([]);
        $this->assertInternalType('callable', $provider);
    }

    public function testProviderReturnsArrayProvidedAtConstruction()
    {
        $expected = [
            'foo' => 'bar',
        ];
        $provider = new ArrayProvider($expected);

        $this->assertSame($expected, $provider());
    }
}
