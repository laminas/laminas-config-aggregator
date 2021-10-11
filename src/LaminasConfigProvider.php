<?php

declare(strict_types=1);

namespace Laminas\ConfigAggregator;

use Laminas\Config\Factory as ConfigFactory;

/**
 * Glob a set of any configuration files supported by Laminas\Config\Factory as
 * configuration providers.
 */
class LaminasConfigProvider
{
    use GlobTrait;

    /** @var string */
    private $pattern;

    /**
     * @param string $pattern Glob pattern.
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * Provide configuration.
     *
     * Globs the given files, and passes the result to ConfigFactory::fromFiles
     * for purposes of returning merged configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        $files = $this->glob($this->pattern);
        return ConfigFactory::fromFiles($files);
    }
}
