# laminas-config-aggregator

[![Build Status](https://github.com/laminas/laminas-config-aggregator/workflows/Continuous%20Integration/badge.svg)](https://github.com/laminas/laminas-config-aggregator/actions?query=workflow%3A"Continuous+Integration")

> ## 🇷🇺 Русским гражданам
>
> Мы, участники Laminas, родились и живем в разных странах. У многих из нас есть друзья, родственники и коллеги как в России, так и в Украине. Некоторые из нас родились в России. Некоторые из нас живут в России. У некоторых бабушки и дедушки сражались с фашистами во Второй мировой войне. Здесь никто не поддерживает фашизм.
>
> У одного из нас есть украинская родственница, которая спаслась из дома вместе с сыном. Поезд задержался из-за бомбежки на дороге впереди. У нас есть друзья, которые прячутся в бомбоубежищах. Мы с тревогой ждем весточки от них после воздушных налетов, которые беспорядочно наносят удары и попадают по больницам, школам, детским садам и домам. Мы не берем это из каких-либо СМИ. Мы наблюдаем это напрямую.
>
> Вы доверяете нам достаточно, чтоб использовать наши программы, и мы просим вас довериться нам вновь. Мы нуждаемся в помощи. Выходите и протестуйте против этой бесполезной войны. Остановите кровопролитие. Скажите "Нет войне!"
>
> ## 🇺🇸 To Citizens of Russia
>
> We at Laminas come from all over the world. Many of us have friends, family and colleagues in both Russia and Ukraine. Some of us were born in Russia. Some of us currently live in Russia. Some have grandparents who fought Nazis in World War II. Nobody here supports fascism.
>
> One team member has a Ukrainian relative who fled her home with her son. The train was delayed due to bombing on the road ahead. We have friends who are hiding in bomb shelters. We anxiously follow up on them after the air raids, which indiscriminately fire at hospitals, schools, kindergartens and houses. We're not taking this from any media. These are our actual experiences.
>
> You trust us enough to use our software. We ask that you trust us to say the truth on this. We need your help. Go out and protest this unnecessary war. Stop the bloodshed. Say "stop the war!"

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
