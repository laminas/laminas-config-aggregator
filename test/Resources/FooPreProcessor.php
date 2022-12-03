<?php

declare(strict_types=1);

namespace LaminasTest\ConfigAggregator\Resources;

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;

/**
 * @psalm-import-type ProviderIterable from ConfigAggregator
 */
class FooPreProcessor
{
    /**
     * @param ProviderIterable $providers
     * @return ProviderIterable
     */
    public function __invoke(iterable $providers): iterable
    {
        return [...$providers, new ArrayProvider(['pre-processed' => true])];
    }
}
