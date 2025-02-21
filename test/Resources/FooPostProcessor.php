<?php

declare(strict_types=1);

namespace LaminasTest\ConfigAggregator\Resources;

final class FooPostProcessor
{
    /**
     * @return array
     */
    public function __invoke(array $config)
    {
        return $config + ['post-processed' => true];
    }
}
