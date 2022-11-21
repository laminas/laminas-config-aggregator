<?php

declare(strict_types=1);

namespace Laminas\ConfigAggregator;

use Brick\VarExporter\ExportException;
use RuntimeException;

use function sprintf;

class ConfigCannotBeCachedException extends RuntimeException
{
    /**
     * @return self
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
