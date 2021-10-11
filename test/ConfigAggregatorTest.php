<?php

declare(strict_types=1);

namespace LaminasTest\ConfigAggregator;

use Iterator;
use IteratorAggregate;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\ConfigCannotBeCachedException;
use Laminas\ConfigAggregator\InvalidConfigProcessorException;
use Laminas\ConfigAggregator\InvalidConfigProviderException;
use LaminasTest\ConfigAggregator\Resources\BarConfigProvider;
use LaminasTest\ConfigAggregator\Resources\FooConfigProvider;
use LaminasTest\ConfigAggregator\Resources\FooPostProcessor;
use PHPUnit\Framework\TestCase;
use stdClass;

use function chmod;
use function dirname;
use function file_put_contents;
use function fileperms;
use function fopen;
use function is_dir;
use function mkdir;
use function rmdir;
use function strtoupper;
use function sys_get_temp_dir;
use function umask;
use function unlink;
use function var_export;

class ConfigAggregatorTest extends TestCase
{
    private $cacheFile;

    protected function setUp(): void
    {
        parent::setUp();
        $dir = sys_get_temp_dir() . '/mezzio_config_loader';
        if (! is_dir($dir)) {
            mkdir($dir);
        }
        $this->cacheFile = $dir . '/cache';
    }

    protected function tearDown(): void
    {
        @unlink($this->cacheFile);
        @rmdir(dirname($this->cacheFile));
    }

    public function testConfigAggregatorRaisesExceptionIfProviderClassDoesNotExist(): void
    {
        $this->expectException(InvalidConfigProviderException::class);
        new ConfigAggregator(['NonExistentConfigProvider']);
    }

    public function testConfigAggregatorRaisesExceptionIfProviderIsNotCallable(): void
    {
        $this->expectException(InvalidConfigProviderException::class);
        new ConfigAggregator([stdClass::class]);
    }

    public function testConfigAggregatorMergesConfigFromArrayProviders(): void
    {
        $aggregator = new ConfigAggregator([FooConfigProvider::class, BarConfigProvider::class]);
        $config     = $aggregator->getMergedConfig();
        self::assertSame(['foo' => 'bar', 'bar' => 'bat'], $config);
    }

    public function testConfigAggregatorMergesConfigFromIteratorProvider(): void
    {
        $providers = $this->createMock(Iterator::class);
        $providers
            ->expects($this->exactly(2))
            ->method('current')
            ->willReturnOnConsecutiveCalls(FooConfigProvider::class, BarConfigProvider::class);

        $providers
            ->expects($this->exactly(3))
            ->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false);

        $aggregator = new ConfigAggregator($providers);
        $config     = $aggregator->getMergedConfig();
        self::assertSame(['foo' => 'bar', 'bar' => 'bat'], $config);
    }

    public function testConfigAggregatorMergesConfigFromIteratorAggregateProvider(): void
    {
        $providers = $this->createMock(IteratorAggregate::class);
        $iterator  = $this->createMock(Iterator::class);

        $providers
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn($iterator);

        $iterator
            ->expects($this->exactly(2))
            ->method('current')
            ->willReturnOnConsecutiveCalls(FooConfigProvider::class, BarConfigProvider::class);

        $iterator
            ->expects($this->exactly(3))
            ->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false);

        $aggregator = new ConfigAggregator($providers);
        $config     = $aggregator->getMergedConfig();
        self::assertSame(['foo' => 'bar', 'bar' => 'bat'], $config);
    }

    public function testProviderCanBeClosure(): void
    {
        $aggregator = new ConfigAggregator([
            function () {
                return ['foo' => 'bar'];
            },
        ]);
        $config     = $aggregator->getMergedConfig();
        self::assertSame(['foo' => 'bar'], $config);
    }

    public function testProviderCanBeGenerator(): void
    {
        $aggregator = new ConfigAggregator([
            function () {
                yield ['foo' => 'bar'];
                yield ['baz' => 'bat'];
            },
        ]);
        $config     = $aggregator->getMergedConfig();
        self::assertSame(['foo' => 'bar', 'baz' => 'bat'], $config);
    }

    public function testConfigAggregatorCanCacheConfig(): void
    {
        new ConfigAggregator([
            function () {
                return ['foo' => 'bar', ConfigAggregator::ENABLE_CACHE => true];
            },
        ], $this->cacheFile);
        self::assertFileExists($this->cacheFile);
        $cachedConfig = include $this->cacheFile;

        self::assertIsArray($cachedConfig);
        self::assertSame(['foo' => 'bar', ConfigAggregator::ENABLE_CACHE => true], $cachedConfig);
    }

    public function testConfigAggregatorCanCacheConfigWithClosures(): void
    {
        new ConfigAggregator([
            function () {
                    return [
                        'toUpper'                      => function ($input) {
                            return strtoupper($input);
                        },
                        ConfigAggregator::ENABLE_CACHE => true,
                    ];
            },
        ], $this->cacheFile);

        self::assertFileExists($this->cacheFile);

        $cachedConfig = include $this->cacheFile;

        self::assertIsCallable($cachedConfig['toUpper']);
        self::assertSame('FOOBAR', $cachedConfig['toUpper']('foobar'));
    }

    public function testConfigAggregatorCanCacheConfigWithClosuresWithUse(): void
    {
        $prefix          = 'prefix';
        $functionWithUse = function ($input) use ($prefix) {
            return $prefix . $input;
        };
        new ConfigAggregator([
            function () use ($functionWithUse) {
                return [
                    'addPrefix'                    => $functionWithUse,
                    ConfigAggregator::ENABLE_CACHE => true,
                ];
            },
        ], $this->cacheFile);

        self::assertFileExists($this->cacheFile);

        $cachedConfig = include $this->cacheFile;

        self::assertIsCallable($cachedConfig['addPrefix']);
        self::assertSame('prefixfoobar', $cachedConfig['addPrefix']('foobar'));
    }

    public function testConfigAggregatorRaisesExceptionIfConfigCannotBeExported(): void
    {
        $this->expectException(ConfigCannotBeCachedException::class);

        new ConfigAggregator([
            function () {
                return [
                    'file_handle'                  => fopen('php://memory', 'rb+'),
                    ConfigAggregator::ENABLE_CACHE => true,
                ];
            },
        ], $this->cacheFile);
    }

    public function testConfigAggregatorSetsDefaultModeOnCache(): void
    {
        new ConfigAggregator([
            function () {
                return ['foo' => 'bar', ConfigAggregator::ENABLE_CACHE => true];
            },
        ], $this->cacheFile);
        self::assertSame(0666 & ~umask(), fileperms($this->cacheFile) & 0777);
    }

    public function testConfigAggregatorSetsModeOnCache(): void
    {
        new ConfigAggregator([
            function () {
                return [
                    'foo'                            => 'bar',
                    ConfigAggregator::ENABLE_CACHE   => true,
                    ConfigAggregator::CACHE_FILEMODE => 0600,
                ];
            },
        ], $this->cacheFile);
        self::assertSame(0600, fileperms($this->cacheFile) & 0777);
    }

    public function testConfigAggregatorSetsHandlesUnwritableCache(): void
    {
        chmod(dirname($this->cacheFile), 0400);
        new ConfigAggregator([
            function () {
                return ['foo' => 'bar', ConfigAggregator::ENABLE_CACHE => true];
            },
        ], $this->cacheFile);

        self::assertFileDoesNotExist($this->cacheFile);
    }

    public function testConfigAggregatorCanLoadConfigFromCache(): void
    {
        $expected = [
            'foo'                          => 'bar',
            ConfigAggregator::ENABLE_CACHE => true,
        ];

        file_put_contents($this->cacheFile, '<' . '?php return ' . var_export($expected, true) . ';');

        $aggregator   = new ConfigAggregator([], $this->cacheFile);
        $mergedConfig = $aggregator->getMergedConfig();

        self::assertIsArray($mergedConfig);
        self::assertSame($expected, $mergedConfig);
    }

    public function testConfigAggregatorRaisesExceptionIfProcessorClassDoesNotExist(): void
    {
        $this->expectException(InvalidConfigProcessorException::class);
        new ConfigAggregator([], null, ['NonExistentConfigProcessor']);
    }

    public function testConfigAggregatorRaisesExceptionIfProcessorIsNotCallable(): void
    {
        $this->expectException(InvalidConfigProcessorException::class);
        new ConfigAggregator([], null, [stdClass::class]);
    }

    public function testProcessorCanBeClosure(): void
    {
        $aggregator = new ConfigAggregator([], null, [
            function (array $config) {
                return $config + ['processor' => 'closure'];
            },
        ]);

        $config = $aggregator->getMergedConfig();
        self::assertSame(['processor' => 'closure'], $config);
    }

    public function testConfigAggregatorCanPostProcessConfiguration(): void
    {
        $aggregator   = new ConfigAggregator([
            function () {
                return ['foo' => 'bar'];
            },
        ], null, [new FooPostProcessor()]);
        $mergedConfig = $aggregator->getMergedConfig();

        self::assertSame(['foo' => 'bar', 'post-processed' => true], $mergedConfig);
    }
}
