<?php

declare(strict_types=1);

namespace Laminas\ConfigAggregator;

/**
 * Provider that returns the array seeded to itself.
 *
 * Primary use case is configuration cache-related settings.
 */
class ArrayProvider
{
    /** @var array */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function __invoke()
    {
        return $this->config;
    }
}
