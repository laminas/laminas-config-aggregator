# laminas-config-aggregator

[![Build Status](https://travis-ci.com/laminas/laminas-config-aggregator.svg?branch=master)](https://travis-ci.com/laminas/laminas-config-aggregator)
[![Coverage Status](https://coveralls.io/repos/github/laminas/laminas-config-aggregator/badge.svg?branch=master)](https://coveralls.io/github/laminas/laminas-config-aggregator?branch=master)

Aggregates and merges configuration, from a variety of formats. Supports caching
for fast bootstrap in production environments.
 
## Usage

The standalone `ConfigAggregator` can be used to merge PHP-based configuration
files: 

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

Together with `laminas-config`, `laminas-config-aggregator` can be also used to load
configuration in different formats, including YAML, JSON, XML, or INI:

```php
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\LaminasConfigProvider;

$aggregator = new ConfigAggregator([
    new LaminasConfigProvider('config/*.{json,yaml,php}'),
]);
```

For more details, please refer to the [documentation](https://docs.laminas.dev/laminas-config-aggregator/).

-----

- File issues at https://github.com/laminas/laminas-config-aggregator/issues
- Documentation is at https://docs.laminas.dev/laminas-config-aggregator/
