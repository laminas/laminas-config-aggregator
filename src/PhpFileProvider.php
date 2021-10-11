<?php

declare(strict_types=1);

namespace Laminas\ConfigAggregator;

use Generator;

/**
 * Provide a collection of PHP files returning config arrays.
 */
class PhpFileProvider
{
    use GlobTrait;

    /** @var string */
    private $pattern;

    /**
     * @param string $pattern A glob pattern by which to look up config files.
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @return Generator
     */
    public function __invoke()
    {
        foreach ($this->glob($this->pattern) as $file) {
            yield include $file;
        }
    }
}
