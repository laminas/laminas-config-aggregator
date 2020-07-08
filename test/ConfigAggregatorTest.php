<?php

/**
 * @see       https://github.com/laminas/laminas-config-aggregator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config-aggregator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config-aggregator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ConfigAggregator;

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\ConfigCannotBeCachedException;
use Laminas\ConfigAggregator\InvalidConfigProcessorException;
use Laminas\ConfigAggregator\InvalidConfigProviderException;
use LaminasTest\ConfigAggregator\Resources\BarConfigProvider;
use LaminasTest\ConfigAggregator\Resources\FooConfigProvider;
use LaminasTest\ConfigAggregator\Resources\FooPostProcessor;
use PHPUnit\Framework\TestCase;
use stdClass;

use function file_exists;
use function var_export;

class ConfigAggregatorTest extends TestCase
{
    private $cacheFile;

    protected function setUp()
    {
        parent::setUp();
        $dir = sys_get_temp_dir() . '/mezzio_config_loader';
        if (! is_dir($dir)) {
            mkdir($dir);
        }
        $this->cacheFile = $dir . '/cache';
    }

    protected function tearDown()
    {
        @unlink($this->cacheFile);
        @rmdir(dirname($this->cacheFile));
    }

    public function testConfigAggregatorRaisesExceptionIfProviderClassDoesNotExist()
    {
        $this->expectException(InvalidConfigProviderException::class);
        new ConfigAggregator(['NonExistentConfigProvider']);
    }

    public function testConfigAggregatorRaisesExceptionIfProviderIsNotCallable()
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
        new ConfigAggregator([
            function () {
                return ['foo' => 'bar', ConfigAggregator::ENABLE_CACHE => true];
            }
        ], $this->cacheFile);
        $this->assertTrue(file_exists($this->cacheFile));
        $cachedConfig = include $this->cacheFile;

        $this->assertInternalType('array', $cachedConfig);
        $this->assertEquals(['foo' => 'bar', ConfigAggregator::ENABLE_CACHE => true], $cachedConfig);
    }

    public function testConfigAggregatorCanCacheConfigWithClosures()
    {
        new ConfigAggregator([
                function () {
                    return [
                     'toUpper' => function ($input) {
                        return strtoupper($input);
                     },
                     ConfigAggregator::ENABLE_CACHE => true
                    ];
                }
         ], $this->cacheFile);
        $this->assertTrue(file_exists($this->cacheFile));

        $cachedConfig = include $this->cacheFile;

        $this->assertTrue(is_callable($cachedConfig['toUpper']));
        $this->assertEquals('FOOBAR', call_user_func($cachedConfig['toUpper'], 'foobar'));
    }

    public function testConfigAggregatorRaisesExceptionIfConfigCannotBeExported()
    {
        $this->expectException(ConfigCannotBeCachedException::class);

        $prefix = 'prefix';
        $functionWithUse = function ($input) use ($prefix) {
            return $prefix . $input;
        };
        new ConfigAggregator([
                function () use ($functionWithUse) {
                    return [
                     'toUpper' => $functionWithUse,
                     ConfigAggregator::ENABLE_CACHE => true
                    ];
                }
         ], $this->cacheFile);
    }

    public function testConfigAggregatorSetsDefaultModeOnCache()
    {
        new ConfigAggregator([
            function () {
                return ['foo' => 'bar', ConfigAggregator::ENABLE_CACHE => true];
            }
        ], $this->cacheFile);
        $this->assertEquals(0666 & ~umask(), fileperms($this->cacheFile) & 0777);
    }

    public function testConfigAggregatorSetsModeOnCache()
    {
        new ConfigAggregator([
            function () {
                return [
                    'foo' => 'bar',
                    ConfigAggregator::ENABLE_CACHE => true,
                    ConfigAggregator::CACHE_FILEMODE => 0600
                ];
            }
        ], $this->cacheFile);
        $this->assertEquals(0600, fileperms($this->cacheFile) & 0777);
    }

    public function testConfigAggregatorSetsHandlesUnwritableCache()
    {
        chmod(dirname($this->cacheFile), 0400);
        new ConfigAggregator([
            function () {
                return ['foo' => 'bar', ConfigAggregator::ENABLE_CACHE => true];
            }
        ], $this->cacheFile);

        $this->assertFalse(file_exists($this->cacheFile));
    }

    public function testConfigAggregatorCanLoadConfigFromCache()
    {
        $expected = [
            'foo' => 'bar',
            ConfigAggregator::ENABLE_CACHE => true,
        ];

        file_put_contents($this->cacheFile, '<' . '?php return ' . var_export($expected, true) . ';');

        $aggregator = new ConfigAggregator([], $this->cacheFile);
        $mergedConfig = $aggregator->getMergedConfig();

        $this->assertInternalType('array', $mergedConfig);
        $this->assertEquals($expected, $mergedConfig);
    }

    public function testConfigAggregatorRaisesExceptionIfProcessorClassDoesNotExist()
    {
        $this->expectException(InvalidConfigProcessorException::class);
        new ConfigAggregator([], null, ['NonExistentConfigProcessor']);
    }

    public function testConfigAggregatorRaisesExceptionIfProcessorIsNotCallable()
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
