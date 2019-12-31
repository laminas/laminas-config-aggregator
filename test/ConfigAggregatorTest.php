<?php

/**
 * @see       https://github.com/laminas/laminas-config-aggregator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config-aggregator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config-aggregator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ConfigAggregator;

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\InvalidConfigProcessorException;
use Laminas\ConfigAggregator\InvalidConfigProviderException;
use LaminasTest\ConfigAggregator\Resources\BarConfigProvider;
use LaminasTest\ConfigAggregator\Resources\FooConfigProvider;
use LaminasTest\ConfigAggregator\Resources\FooPostProcessor;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use stdClass;

use function file_exists;
use function var_export;

class ConfigAggregatorTest extends TestCase
{
    public function testConfigAggregatorRisesExceptionIfProviderClassDoesNotExist()
    {
        $this->expectException(InvalidConfigProviderException::class);
        new ConfigAggregator(['NonExistentConfigProvider']);
    }

    public function testConfigAggregatorRisesExceptionIfProviderIsNotCallable()
    {
        $this->expectException(InvalidConfigProviderException::class);
        new ConfigAggregator([stdClass::class]);
    }

    public function testConfigAggregatorMergesConfigFromProviders()
    {
        $aggregator = new ConfigAggregator([FooConfigProvider::class, BarConfigProvider::class]);
        $config = $aggregator->getMergedConfig();
        $this->assertEquals(['foo' => 'bar', 'bar' => 'bat'], $config);
    }

    public function testProviderCanBeClosure()
    {
        $aggregator = new ConfigAggregator([
            function () {
                return ['foo' => 'bar'];
            },
        ]);
        $config = $aggregator->getMergedConfig();
        $this->assertEquals(['foo' => 'bar'], $config);
    }

    public function testProviderCanBeGenerator()
    {
        $aggregator = new ConfigAggregator([
            function () {
                yield ['foo' => 'bar'];
                yield ['baz' => 'bat'];
            },
        ]);
        $config = $aggregator->getMergedConfig();
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bat'], $config);
    }

    public function testConfigAggregatorCanCacheConfig()
    {
        vfsStream::setup(__FUNCTION__);
        $cacheFile = vfsStream::url(__FUNCTION__) . '/mezzio_config_loader';
        new ConfigAggregator([
            function () {
                return ['foo' => 'bar', ConfigAggregator::ENABLE_CACHE => true];
            }
        ], $cacheFile);
        $this->assertTrue(file_exists($cacheFile));
        $cachedConfig = include $cacheFile;
        $this->assertInternalType('array', $cachedConfig);
        $this->assertEquals(['foo' => 'bar', ConfigAggregator::ENABLE_CACHE => true], $cachedConfig);
    }

    public function testConfigAggregatorCanLoadConfigFromCache()
    {
        $expected = [
            'foo' => 'bar',
            ConfigAggregator::ENABLE_CACHE => true,
        ];

        $root = vfsStream::setup(__FUNCTION__);
        vfsStream::newFile('mezzio_config_loader')
            ->at($root)
            ->setContent('<' . '?php return ' . var_export($expected, true) . ';');
        $cacheFile = vfsStream::url(__FUNCTION__ . '/mezzio_config_loader');

        $aggregator = new ConfigAggregator([], $cacheFile);
        $mergedConfig = $aggregator->getMergedConfig();

        $this->assertInternalType('array', $mergedConfig);
        $this->assertEquals($expected, $mergedConfig);
    }

    public function testConfigAggregatorRisesExceptionIfProcessorClassDoesNotExist()
    {
        $this->expectException(InvalidConfigProcessorException::class);
        new ConfigAggregator([], null, ['NonExistentConfigProcessor']);
    }

    public function testConfigAggregatorRisesExceptionIfProcessorIsNotCallable()
    {
        $this->expectException(InvalidConfigProcessorException::class);
        new ConfigAggregator([], null, [stdClass::class]);
    }

    public function testProcessorCanBeClosure()
    {
        $aggregator = new ConfigAggregator([], null, [
            function (array $config) {
                return $config + ['processor' => 'closure'];
            },
        ]);

        $config = $aggregator->getMergedConfig();
        $this->assertEquals(['processor' => 'closure'], $config);
    }

    public function testConfigAggregatorCanPostProcessConfiguration()
    {
        $aggregator = new ConfigAggregator([
            function () {
                return ['foo' => 'bar'];
            },
        ], null, [new FooPostProcessor]);
        $mergedConfig = $aggregator->getMergedConfig();

        $this->assertEquals(['foo' => 'bar', 'post-processed' => true], $mergedConfig);
    }
}
