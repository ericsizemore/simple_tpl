# Simple Template Engine - Text based template parser.

[![Build Status](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ericsizemore/simple_tpl/?branch=master)
[![PHPStan](https://github.com/ericsizemore/simple_tpl/actions/workflows/ci.yml/badge.svg)](https://github.com/ericsizemore/simple_tpl/actions/workflows/ci.yml)
[![Tests](https://github.com/ericsizemore/simple_tpl/actions/workflows/tests.yml/badge.svg)](https://github.com/ericsizemore/simple_tpl/actions/workflows/tests.yml)
[![Psalm Static analysis](https://github.com/ericsizemore/simple_tpl/actions/workflows/psalm.yml/badge.svg?branch=master)](https://github.com/ericsizemore/simple_tpl/actions/workflows/psalm.yml)
[![Type Coverage](https://shepherd.dev/github/ericsizemore/simple_tpl/coverage.svg)](https://shepherd.dev/github/ericsizemore/simple_tpl)
[![Psalm Level](https://shepherd.dev/github/ericsizemore/simple_tpl/level.svg)](https://shepherd.dev/github/ericsizemore/simple_tpl)
[![Latest Stable Version](https://img.shields.io/packagist/v/esi/simple_tpl.svg)](https://packagist.org/packages/esi/simple_tpl)
[![Downloads per Month](https://img.shields.io/packagist/dm/esi/simple_tpl.svg)](https://packagist.org/packages/esi/simple_tpl)
[![License](https://img.shields.io/packagist/l/esi/simple_tpl.svg)](https://packagist.org/packages/esi/simple_tpl)

[Simple Template Engine](http://github.com/ericsizemore/simple_tpl/) is a small, simple text-based template parsing engine that works on text replacement.

## Important Note

* The `master` branch currently holds the work in progress version `3.x`, which is a break from the backward compatibility promise. This will be resolved once 3.0.0 is released.
* Since `3.x` is under development, it is not recommended to use in a production environment. The public api, implementations, etc. can (and will likely) change.


## Installation

### Composer

```bash
$ composer require esi/simple_tpl
```

Then, within your project (if not already included), include composer's autoload. For example:

```php
<?php

require_once 'vendor/autoload.php';
use Esi\SimpleTpl\Template;

$tpl = new Template();

?>
```

### Usage

Some examples have been provided within the [`examples`](examples/) folder.

More documentation can be found within the [`docs`](docs/) folder.

## About

### Requirements

- Simple Template Engine works with PHP 8.2.0 or above.

### Contributing

See [CONTRIBUTING](CONTRIBUTING.md).

### Author

Eric Sizemore - <admin@secondversion.com> - <https://www.secondversion.com>

### License

Simple Template Engine's license depends on the version you are using:

* `v3.0.0` and later is licensed under [`The MIT License`](LICENSE.md).
* `v2.0.1` and prior is licensed under the [`GNU GPL v3 License`](https://github.com/ericsizemore/simple_tpl/blob/2.x/LICENSE).
