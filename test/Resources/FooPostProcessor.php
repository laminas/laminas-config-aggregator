<?php

declare(strict_types=1);

namespace LaminasTest\ConfigAggregator\Resources;

class FooPostProcessor
{
    /**
     * @param array $config
     * @return array
     */
    public function __invoke(array $config)
    {
        return $config + ['post-processed' => true];
    }
}
