<?php

namespace Laminas\ConfigAggregator;

use RuntimeException;

use function sprintf;

class InvalidConfigProviderException extends RuntimeException
{

    /**
     * @param string $provider
     *
     * @return InvalidConfigProviderException
     */
    public static function fromNamedProvider($provider)
    {
        return new self(sprintf(
            'Cannot read config from %s - class cannot be loaded.',
            $provider
        ));
    }

    /**
     * @param string $type
     *
     * @return InvalidConfigProviderException
     */
    public static function fromUnsupportedType($type)
    {
        return new self(
            sprintf("Cannot read config from %s - config provider must be callable.", $type)
        );
    }
}
