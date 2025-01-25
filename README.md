# Simple Template Engine - Text based template parser.

[![Build Status](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/?branch=master)
[![Continuous Integration](https://github.com/ericsizemore/mimey/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ericsizemore/mimey/actions/workflows/continuous-integration.yml)
[![Type Coverage](https://shepherd.dev/github/ericsizemore/simple_tpl/coverage.svg)](https://shepherd.dev/github/ericsizemore/simple_tpl)
[![Psalm Level](https://shepherd.dev/github/ericsizemore/simple_tpl/level.svg)](https://shepherd.dev/github/ericsizemore/simple_tpl)
[![Latest Stable Version](https://img.shields.io/packagist/v/esi/simple_tpl.svg?label=stable)](https://packagist.org/packages/esi/simple_tpl)
[![Development Version](https://img.shields.io/badge/dynamic/yaml?url=https%3A%2F%2Fgithub.com%2Fericsizemore%2Fsimple_tpl%2Fraw%2Fmaster%2Fcomposer.json&query=%24%5B'extra'%5D%5B'branch-alias'%5D%5B'dev-master'%5D&label=unstable&color=%23ff4c00)](https://github.com/ericsizemore/simple_tpl/tree/master)
[![Downloads per Month](https://img.shields.io/packagist/dm/esi/simple_tpl.svg)](https://packagist.org/packages/esi/simple_tpl)
<!-- Remove until 3.x release
[![License](https://img.shields.io/packagist/l/esi/simple_tpl.svg)](https://packagist.org/packages/esi/simple_tpl)
-->
[Simple Template Engine](http://github.com/ericsizemore/simple_tpl/) is a small, simple text-based template parsing engine that works on text replacement.

> [!IMPORTANT]
> The `master` branch currently holds the work in progress version `3.x`, which is a break from the backward compatibility promise. This will be resolved once 3.0.0 is released. 
> Since `3.x` is under development, it is not recommended to use in a production environment. The public api, implementations, etc. can (and will likely) change.

> [!IMPORTANT]
> The `3.x` branch is mostly an introduction of caching templates. As this is in development, I'm playing around with a few different ideas. For example, right now it just caches
> the entire template after it has been parsed (so it caches template + content). This can and will likely change as I continue testing out different approaches.

---

## Installation

Compatible with PHP >= 8.2 and can be installed with [Composer](https://getcomposer.org).

```bash
$ composer require esi/simple_tpl
```

### Usage

Basic usage, without providing a cache library:

```php
use Esi\SimpleTpl\Template;

$tpl = new Template();

$tpl->setTplVars([
    'title'   => 'Simple Template Engine Test',
    'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
]);

// Parse the template file
$tpl->display(__DIR__ . '/some_template.tpl');
```

If you would like to utilize caching for templates, you will need to provide the library a [PSR-6](https://www.php-fig.org/psr/psr-6/) cache implementation.
You can view a list of packages that provide this implementation on [Packagist](https://packagist.org/providers/psr/cache-implementation).

For example:

```bash
$ composer require symfony/cache:^7.2 
```

```php
use Esi\SimpleTpl\Template;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

$tpl = new Template(
    /**
     * Symfony's AbstractAdapter::createSystemCache() returns the best possible adapter that your runtime supports.
     * Generally, it will create a cache via PHP files (Opcache must be enabled via opcache.enable in php.ini), and chain that with APCu if your system supports it.
     *
     * For more information on symfony/cache's available cache pool (PSR-6) adapters:
     * @see https://symfony.com/doc/current/components/cache/cache_pools.html 
     */
    AbstractAdapter::createSystemCache(namespace: 'simple_tpl', defaultLifetime: 300, version: '', directory: sys_get_temp_dir())
);

// ... assign vars, parse /display template, etc ...
```

Some basic examples have also been provided within the [`examples`](./examples) folder.

## About

### Requirements

- SimpleTpl works with PHP 8.2.0 or above.

## Credits

- Author: [Eric Sizemore](https://github.com/ericsizemore)
- Thanks to [all Contributors](https://github.com/ericsizemore/simple_tpl/contributors).
- Special thanks to [JetBrains](https://www.jetbrains.com/?from=esi-simpple-tpl) for their Licenses for Open Source Development.

## Contributing

See [CONTRIBUTING](./CONTRIBUTING.md) for more information.

Bugs and feature requests are tracked on [GitHub](https://github.com/ericsizemore/simple_tpl/issues).

### Contributor Covenant Code of Conduct

See [CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md)

### Backward Compatibility Promise

See [backward-compatibility.md](./backward-compatibility.md) for more information on Backwards Compatibility.

### Changelog

See the [CHANGELOG](./CHANGELOG.md) for more information on what has changed recently.

### License

Simple Template Engine's license depends on the version you are using:

* `v3.0.0` and later is licensed under `The MIT License`.
* `v2.0.1` and earlier is licensed under the [`GNU GPL v3 License`](https://github.com/ericsizemore/simple_tpl/blob/2.x/LICENSE).

See the [LICENSE](./LICENSE.md) for more information on the license that applies to this project.

### Security

See [SECURITY](./SECURITY.md) for more information on the security disclosure process.
