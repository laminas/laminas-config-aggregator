<?php

/**
 * @see       https://github.com/laminas/laminas-config-aggregator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config-aggregator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config-aggregator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ConfigAggregator\Resources;

class FooPostProcessor
{

    /**
     * @param array $config
     *
     * @return array
     */
    public function __invoke(array $config)
    {
        return $config + ['post-processed' => true];
    }
}
