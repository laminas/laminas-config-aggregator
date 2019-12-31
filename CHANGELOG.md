# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.1 - 2017-11-06

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-config-aggregator#14](https://github.com/zendframework/zend-config-aggregator/pull/14) fixes
  how file globbing of configuration files works on FreeBSD and IBM i Series
  systems. It does so by adding a dependency on laminas-stdlib, which provides a
  glob compatibility layer.

## 1.0.0 - 2017-04-24

### Added

- [zendframework/zend-config-aggregator#7](https://github.com/zendframework/zend-config-aggregator/pull/7) adds
  online documentation at https://docs.laminas.dev/laminas-config-aggregator/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.1 - 2017-04-23

### Added

- [zendframework/zend-config-aggregator#3](https://github.com/zendframework/zend-config-aggregator/pull/3) added
  laminas-config ^3.0 support

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.0 - 2017-01-11

### Added

- [zendframework/zend-config-aggregator#2](https://github.com/zendframework/zend-config-aggregator/pull/2) adds a
  new `ArrayProvider`, which accepts an array to its constructor, and returns
  it when invoked. This can be used to provide in-line array configuration when
  feeding the `ConfigAggregator` instance.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.1.0 - 2016-12-08

Initial release.

Based on the 0.4.0 version of [mtymek/mezzio-config-manager](https://github.com/mtymek/mezzio-config-manager),
this version renames the namespace from `Mezzio\ConfigManager` to
`Laminas\ConfigAggregator`, and renames the `ConfigManager` class to
`ConfigAggregator`. All other functionality remains the same.
