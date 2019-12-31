# laminas-config-aggregator

[![Build Status](https://travis-ci.org/laminas/laminas-config-aggregator.svg?branch=master)](https://travis-ci.org/laminas/laminas-config-aggregator)

(Based on [mtymek/mezzio-config-manager](https://github.com/mtymek/mezzio-config-manager).)

Lightweight library for collecting and merging configuration from different sources. 

While designed for [Mezzio](https://github.com/mezzio/mezzio) 
applications, it can work with any PHP project for aggregating and returning
merged configuration, from either a variety of configuration formats or
"configuration providers", invokable classes returning an array of configuration
(or a PHP generator). It also supports configuration caching.
 
## Usage

### Config files

At the basic level, `ConfigAggregator` can be used to merge PHP-based
configuration files: 

```php
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

$aggregator = new ConfigAggregator([
    new PhpFileProvider('*.global.php'),
]);

var_dump($aggregator->getMergedConfig());
```

Using this provider, each file should return a PHP array:

```php
// db.global.php
return [
    'db' => [
        'dsn' => 'mysql:...',
    ],    
];

// cache.global.php
return [
    'cache_storage' => 'redis',
    'redis' => [ ... ],
];
```

Result:

```php
array(3) {
  'db' =>
  array(1) {
    'dsn' =>
    string(9) "mysql:..."
  }
  'cache_storage' =>
  string(5) "redis"
  'redis' =>
  array(0) {
     ...
  }
}
```

Configuration is merged in the same order as it is passed, with later entries
having precedence.

### Config providers

`ConfigAggregator` works by aggregating "Config Providers" passed to its
constructor.  Each provider should be a callable, returning a configuration
array (or a PHP generator) to be merged.

```php
$aggregator = new ConfigAggregator([
    function () {
        return ['foo' => 'bar'];
    },
    new PhpFileProvider('*.global.php'),
]);
var_dump($aggregator->getMergedConfig());
```

If the provider is a class name, the aggregator automatically instantiates it.
This can be used to mimic the Laminas module system: you can specify a
list of config providers from different packages, and aggregated configuration
will be available to your application.

As a library owner, you can distribute your own configuration providers that
provide default values for use with your library.

As an example:

```php
class ApplicationConfig
{
    public function __invoke()
    {
        return ['foo' => 'bar'];
    }
}

$aggregator = new ConfigAggregator(
    [
        ApplicationConfig::class,
        new PhpFileProvider('*.global.php'),
    ]
);
var_dump($configManager->getMergedConfig());
```

Output from both examples will be the same:

```php
array(4) {
  'foo' =>
  string(3) "bar"
  'db' =>
  array(1) {
    'dsn' =>
    string(9) "mysql:..."
  }
  'cache_storage' =>
  string(5) "redis"
  'redis' =>
  array(0) {
  }
}
```

### Caching

Merging configuration on every request is not performant. As such,
laminas-config-aggregator also provides the ability to enable a filesystem-based
configuration cache.

To enable the configuration cache, pass a cache file name as the second
parameter to the `ConfigAggregator` constructor:

```php
$aggrgator = new ConfigAggregator(
    [
        function () { return [ConfigAggregator::ENABLE_CACHE => true]; },
        new PhpFileProvider('*.global.php'),
    ],
    'data/config-cache.php'
);
```

When a cache file is specified, you will also need to add the
`config_cache_enabled` key (which you can also specify via the
`ConfigAggregator::ENABLE_CACHE` constant) somewhere within one of your
configuration providers, and set it to boolean `true`. Using this approach, if
you were to use the globbing pattern `{{,*.}global,{,*.}local}.php` (or similar)
with the `PhpFileProvider`, you could drop a file named `enable-cache.local.php`
into your production deployment with the following contents in order to enable
configuration caching in production:

```php
<?php
use Laminas\ConfigAggregator\ConfigAggregator;

return [
    ConfigAggregator::ENABLE_CACHE => true,
];
```

When caching is enabled, the `ConfigAggregator` does not iterate config
providers. Because of that it is very fast, but after it is enabled you cannot
make any changes to configuration without clearing the cache. **Caching should
be used only in a production environment**, and your deployment process should
clear the cache.

### Generators

Config providers can be written as generators. This way single callable can provide 
multiple configurations:

```php
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\Stdlib\Glob;

$aggregator = new ConfigAggregator([
    function () { 
        foreach (Glob::glob('data/*.global.php', Glob::GLOB_BRACE) as $file) {
            yield include $file;
        } 
    }        
]
);
var_dump($aggregator->getMergedConfig());
```

The providers `PhpFileProvider` is implemented using generators.


## Available config providers

### PhpFileProvider
 
Loads configuration from PHP files returning arrays, such as this one:

```php
return [
    'db' => [
        'dsn' => 'mysql:...',
    ],    
];
```

Wildcards are supported:

```php
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;

$aggregator = new ConfigAggregator(
    [
        new PhpFileProvider('config/*.global.php'),        
    ]
);
```

The example above will merge all matching files from the `config/` directory. If
you have files such as `app.global.php` or `database.global.php` in that
directory, they will be loaded using this above lines of code.

Globbing defaults to PHP's `glob()` function. However, if `Laminas\Stdlib\Glob` is
available, it will use that to allow for cross-platform glob patterns, including
brace notation: `'config/autoload/{{,*.}global,{,*.}local}.php'`. Install
laminas/laminas-stdlib to utilize this feature.
    
### LaminasConfigProvider

Sometimes using plain PHP files may be not enough; you may want to build your configuration 
from multiple files of different formats, such as INI, YAML, or XML.
laminas-config-aggregator allows you to do so via its `LaminasConfigProvider`:

```php
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\LaminasConfigProvider;

$aggregator = new ConfigAggregator(
    [
        new LaminasConfigProvider('*.global.json'),
        new LaminasConfigProvider('database.local.ini'),
    ]
);
```

These could even be combined into a single glob statement:

```php
$aggregator = new ConfigAggregator(
    [
        new LaminasConfigProvider('*.global.json,database.local.ini'),
    ]
);
```

`LaminasConfigProvider` accepts wildcards and autodetects the config type based on
file extension. 

LaminasConfigProvider requires two packages to be installed:
`laminas/laminas-config` and `laminas/laminas-servicemanager`. Some config
readers (JSON, YAML) may need additional dependencies; please refer to
[the laminas-config manual](https://docs.laminas.dev/laminas-config/reader/)
for more details.
