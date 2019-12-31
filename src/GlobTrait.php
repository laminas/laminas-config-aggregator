<?php

/**
 * @see       https://github.com/laminas/laminas-config-aggregator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config-aggregator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config-aggregator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ConfigAggregator;

use Laminas\Stdlib\Glob;

/**
 * Helper trait used in config providers that require globbing.
 */
trait GlobTrait
{
    /**
     * Return a set of filesystem items based on a glob pattern.
     *
     * Uses the laminas-stdlib Glob class for cross-platform globbing to
     * ensure results are sorted by brace pattern order _after_
     * sorting by filename.
     *
     * @param string $pattern
     * @return array
     */
    private function glob($pattern)
    {
        return Glob::glob($pattern, Glob::GLOB_BRACE, true);
    }
}
