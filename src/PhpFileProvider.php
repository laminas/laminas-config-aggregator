<?php

/**
 * @see       https://github.com/laminas/laminas-config-aggregator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config-aggregator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config-aggregator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ConfigAggregator;

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
     * @return \Generator
     */
    public function __invoke()
    {
        foreach ($this->glob($this->pattern) as $file) {
            yield include $file;
        }
    }
}
