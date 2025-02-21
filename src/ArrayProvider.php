<?php

declare(strict_types=1);

namespace Laminas\ConfigAggregator;

/**
 * Provider that returns the array seeded to itself.
 *
 * Primary use case is configuration cache-related settings.
 *
 * @template TKey of array-key
 * @template TValue
 * @final
 */
class ArrayProvider
{
    /**
     * @param array<TKey, TValue> $config
     */
    public function __construct(private readonly array $config)
    {
    }

    /**
     * @return array<TKey, TValue>
     */
    public function __invoke()
    {
        return $this->config;
    }
}
