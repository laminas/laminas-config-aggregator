<?php

declare(strict_types=1);

namespace LaminasTest\ConfigAggregator\Resources;

use ArrayObject;

final class FooConfigProvider
{
    /**
     * @return array|ArrayObject
     */
    public function __invoke()
    {
        return ['foo' => 'bar'];
    }
}
