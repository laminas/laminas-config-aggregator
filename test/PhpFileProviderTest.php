<?php

namespace LaminasTest\ConfigAggregator;

use Laminas\ConfigAggregator\PhpFileProvider;
use Laminas\Stdlib\ArrayUtils;
use PHPUnit\Framework\TestCase;

class PhpFileProviderTest extends TestCase
{
    public function testProviderLoadsConfigFromFiles(): void
    {
        $provider = new PhpFileProvider(__DIR__ . '/Resources/config/{{,*.}global,{,*.}local}.php');
        $merged = [];
        foreach ($provider() as $item) {
            $merged = ArrayUtils::merge($merged, $item);
        }
        self::assertSame(['fruit' => 'banana', 'vegetable' => 'potato'], $merged);
    }
}
