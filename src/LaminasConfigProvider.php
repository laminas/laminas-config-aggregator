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

    /**
     * @param non-empty-string $pattern Glob pattern.
     */
    public function __construct(private string $pattern)
    {
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
