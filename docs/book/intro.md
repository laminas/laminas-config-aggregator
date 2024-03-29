# Introduction

`laminas-config-aggregator` is a lightweight library for managing application
configuration. It was designed to be flexible in dev environments and fast in
production.

It supports loading and merging configuration from multiple sources: PHP files,
arrays, or INI/YAML/XML files (using [laminas-config](https://docs.laminas.dev/laminas-config/))

It also provides the ability to post process the merged configuration to apply e.g. parameter
handling like [symfony/dependency-injection](https://symfony.com/doc/current/service_container/parameters.html#parameters-in-configuration-files)

## Basic usage

The standalone `ConfigAggregator` can be used to merge PHP-based configuration files:

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

Configuration is merged in the same order as it is passed, with later entries having precedence.

Together with `laminas-config`, `laminas-config-aggregator` can be also used to load
configuration in different formats, including YAML, JSON, XML, or INI:

```php
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\LaminasConfigProvider;

$aggregator = new ConfigAggregator([
    new LaminasConfigProvider('config/*.{json,yaml,php}'),
]);
```

You can also supply [processors](config-processors.md) for configuration. These are PHP callables that accept either the
list of providers (pre-processors) or the merged configuration (post-processors) as an argument, do something with it,
and return it on completion. This could be used, for example, to attach development only providers, or to allow
templating parameters that are used in multiple locations and resolving them to a single value later.
