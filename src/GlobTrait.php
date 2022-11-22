<?php

declare(strict_types=1);

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
     * @return array
     */
    private function glob(string $pattern): array
    {
        return Glob::glob($pattern, Glob::GLOB_BRACE, true);
    }
}
