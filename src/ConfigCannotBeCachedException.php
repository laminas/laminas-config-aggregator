<?php

/**
 * @see       https://github.com/laminas/laminas-config-aggregator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config-aggregator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config-aggregator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ConfigAggregator;

use Brick\VarExporter\ExportException;
use RuntimeException;

use function sprintf;

class ConfigCannotBeCachedException extends RuntimeException
{
    /**
     * @param ExportException $exportException
     *
     * @return ConfigCannotBeCachedException
     */
    public static function fromExporterException(ExportException $exportException)
    {
        return new self(
            sprintf(
                'Cannot export config into a cache file. Config contains uncacheable entries: %s',
                $exportException->getMessage()
            ),
            $exportException->getCode(),
            $exportException
        );
    }
}
