# Simple Template Engine - Text based template parser.

[![Build Status](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/?branch=master)
[![Continuous Integration](https://github.com/ericsizemore/simple_tpl/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/ericsizemore/simple_tpl/actions/workflows/continuous-integration.yml)
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
> The `master` branch currently holds the work-in-progress version `3.x`, which is a break from the backward compatibility promise.
> This will be resolved once `3.0.0` is released. Since `3.x` is under development, it is not recommended to use in a production environment.
> The public API, implementations, etc., can (and will likely) change.

---

## Installation

Compatible with PHP >= 8.2 and can be installed with [Composer](https://getcomposer.org).

```bash
$ composer require esi/simple_tpl:^3.0
```

## Usage

### Storage

There are two storage implementations available: `Storage\FilesystemStorage` and `Storage\DatabaseStorage`.
Both storage implementations implement the `Storage\StorageInterface` interface, with only one defined method: `loadTemplate()`.

**NOTE:** If you wish to have a system for editing/saving, deleting or updating the templates themselves, you would need to implement this on your own.
This library only searches for templates that have already been created, by name, then parses them with a `key => value` associative array of variables.

#### Filesystem Storage

The `FilesystemStorage` implementation allows you to use regular files for your templates.
Your template files are expected to end with the `.tpl` extension. I plan to allow the ability to use different extensions later.

1. Create a `FilesystemStorage` instance with the path/directory to where your templates are located.
2. Pass this instance to the `Template` class when creating it.

Let's say you had a template called `example_template.tpl` within the `./templates/` directory.

Template file:
```html
<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html" />
    <title>{title}</title>
</head>
<body>
<p>{content}</p>
</body>
</html>
```

PHP to parse the template:
```php
use Esi\SimpleTpl\Template;
use Esi\SimpleTpl\Storage\FilesystemStorage;

$templateStorage = new FilesystemStorage('./templates/');
$template = new Template($templateStorage);

$template->setTplVars([
    'title' => 'Hello, World!',
    'content' => 'This is a simple template engine.'
]);

echo $template->parse('example_template');
```

When calling `display()` or `parse()`, you only need to provide the file name without extension.
For example, if your template file is `mytemplate.tpl`, you would call either of these methods with `mytemplate`.

#### Database Storage

The `DatabaseStorage` implementation allows you to use a database for your templates.

1. Create a `PDO` instance with your database details to create a connection.
2. Create a `DatabaseStorageConfig` with your template tables `tableName`, `nameField`, and `contentField`.
3. Create a `DatabaseStorage` instance and pass the `PDO` and `DatabaseStorageConfig` instances to it.
4. Pass the `DatabaseStorage` instance to the `Template` class when creating it.

Let's say the content of the `example_template` is the same as in the [filesystem example](#filesystem-storage), and your
database table structure is:

```sql
CREATE TABLE IF NOT EXISTS `templates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `content` MEDIUMTEXT NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
)
```

Then your code would look something like:

```php
use Esi\SimpleTpl\Template;
use Esi\SimpleTpl\Storage\DatabaseStorage;
use Esi\SimpleTpl\Storage\DatabaseStorageConfig;
use PDO;

$pdo = new PDO('mysql:host=localhost;dbname=templates', 'user', 'password');
$storageConfig = new DatabaseStorageConfig(); // with the example table structure above, the config defaults to 'templates', 'name', 'content'
$templateStorage = new DatabaseTemplateStorage($pdo, $storageConfig);
$template = new Template($templateStorage);

$template->setTplVars([
    'title' => 'Hello, World!',
    'content' => 'This is a simple template engine.'
]);

echo $template->parse('example_template');
```

### Caching

If you would like to utilize caching for templates, you will need to provide the library a [PSR-6](https://www.php-fig.org/psr/psr-6/) cache implementation.
You can view a list of packages that provide this implementation on [Packagist](https://packagist.org/providers/psr/cache-implementation).

Whether you use `FilesystemStorage` or `DatabaseStorage`, you can use caching for either by passing an object that implements `\Psr\Cache\CacheItemPoolInterface`.

For example:

```bash
$ composer require symfony/cache:^7.2 
```

```php
use Esi\SimpleTpl\Template;
use Esi\SimpleTpl\Storage\FilesystemStorage;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

$templateStorage = new FilesystemStorage('/path/to/templates');
$template = new Template(
    $templateStorage,
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
