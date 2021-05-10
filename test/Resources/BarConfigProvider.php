<?php

namespace LaminasTest\ConfigAggregator\Resources;

use ArrayObject;

class BarConfigProvider
{
    /**
     * @return array|ArrayObject
     */
    public function __invoke()
    {
        return ['bar' => 'bat'];
    }
}
