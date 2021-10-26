<?php

declare(strict_types=1);

namespace LaminasTest\ConfigAggregator\Resources;

use ArrayObject;

class FooConfigProvider
{
    /**
     * @return array|ArrayObject
     */
    public function __invoke()
    {
        return ['foo' => 'bar'];
    }
}
