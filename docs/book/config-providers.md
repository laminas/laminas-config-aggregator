# Config providers

The `ConfigAggregator` works by aggregating "config providers" passed to its
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

If the provider is a class name, the aggregator automatically instantiates it
before invoking it; as such, any class name you use as a config provider _must_
also define `__invoke()`, and that method _must_ return an array.

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

$aggregator = new ConfigAggregator([
    ApplicationConfig::class,
    new PhpFileProvider('*.global.php'),
]);
var_dump($aggregator->getMergedConfig());
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

### Generators

Config providers can be written as generators. This way, a single callable can
provide multiple configurations:

```php
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\Stdlib\Glob;

$aggregator = new ConfigAggregator([
    function () {
        foreach (Glob::glob('data/*.global.php', Glob::GLOB_BRACE) as $file) {
            yield include $file;
        }
    },
]);
var_dump($aggregator->getMergedConfig());
```

The `PhpFileProvider` is implemented as a generator.

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

The provider also supports _globbing_.  Globbing defaults to PHP's `glob()`
function. However, if `Laminas\Stdlib\Glob` is available, it will use that to allow
for cross-platform glob patterns, including brace notation:
`'config/autoload/{{,*.}global,{,*.}local}.php'`. Install
[laminas/laminas-stdlib](https://docs.laminas.dev/laminas-stdlib) to
utilize this feature.

### LaminasConfigProvider

Sometimes using plain PHP files may be not enough; you may want to build your
configuration from multiple files of different formats, such as INI, JSON, YAML,
or XML.  laminas-config-aggregator allows you to do so via its
`LaminasConfigProvider`. This feature requires first installing laminas-config:

```bash
$ composer require laminas/laminas-config
```

Once installed, you may use as many `LaminasConfigProvider` instances as you need:

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

`LaminasConfigProvider` accepts wildcards and globs, and autodetects the config
type based on file extension.

Some config readers (in particular, YAML) may need additional dependencies;
please refer to [the laminas-config manual](https://docs.laminas.dev/laminas-config/reader/)
for more details.
