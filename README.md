# Simple Template Engine - Text based template parser.

[Simple Template Engine](http://github.com/ericsizemore/simple_tpl/) is a small, simple text-based 
template parsing engine that works on text replacement.

## Installation

Simply drop `Template.php` in any project and call `include 'src/Template.php';`, where 
`'src/'` is the path to where you placed the template engine file(s).

For example:

```php
<?php

include 'src/simple_tpl/Template.php';

?>
```

## Basic Usage

```php
<?php

include 'src/simple_tpl/Template.php';

$tpl = new \Esi\Template();

?>
```

There are three main methods of the Template class that are used to parse and display a template. An example is provided with the `'examples'` directory. 
Those methods are `'assign()'`, `'parse()'` and `'display()'`. The `'display()'` method is a wrapper for '`parse()`' with the only difference being that `'display()'` 
will echo the contents of the template instead of returning them as a string.

## A Simple Example

### PHP Code
```php
<?php

// Include class file and instantiate.
require_once '../src/Template.php';
$tpl = new \Esi\Template();

/**
* assign expects an array of:
*     variable => value
*
* Variables in your template(s) should be in the form of:
*     {variable}
*/
$tpl->assign([
    'title'   => 'Simple Template Engine Test',
    'content' => 'This is a test of the <a href="http://www.phpclasses.org/browse/package/3171.html">Simple Template Engine</a> class by Eric Sizemore.'
]);

// Parse the template file
$tpl->display('../src/example.tpl');

?>
```

### Template HTML
```html
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="content-type" content="text/html" />
	<title>{title}</title>
</head>

<body>

<p>{content}</p>

</body>
</html>
```

## About

### Requirements

- Simplte Template Engine works with PHP 7.0.0 or above.

### Submitting bugs and feature requests

Bugs and feature requests are tracked on [GitHub](https://github.com/ericsizemore/simple_tpl/issues)

Issues are the quickest way to report a bug. If you find a bug or documentation error, please check the following first:

* That there is not an Issue already open concerning the bug
* That the issue has not already been addressed (within closed Issues, for example)

### Contributing

Simple Template Engine accepts contributions of code and documentation from the community. 
These contributions can be made in the form of Issues or [Pull Requests](http://help.github.com/send-pull-requests/) 
on the [Simple Template Engine repository](https://github.com/ericsizemore/simple_tpl).

Simple Template Engine is licensed under the MIT license. When submitting new features or patches to Simple Template Engine, you are 
giving permission to license those features or patches under the MIT license.

#### Guidelines

Before we look into how, here are the guidelines. If your Pull Requests fail to
pass these guidelines it will be declined and you will need to re-submit when
you’ve made the changes. This might sound a bit tough, but it is required for
me to maintain quality of the code-base.

#### PHP Style

Please ensure all new contributions match the [PSR-2](http://www.php-fig.org/psr/psr-2/)
coding style guide. The project is not fully PSR-2 compatible, yet; however, to ensure 
the easiest transition to the coding guidelines, I would like to go ahead and request 
that any contributions follow them.

#### Documentation

If you change anything that requires a change to documentation then you will
need to add it. New methods, parameters, changing default values, adding
constants, etc are all things that will require a change to documentation. The
change-log must also be updated for every change. Also PHPDoc blocks must be
maintained.

#### Branching

One thing at a time: A pull request should only contain one change. That does
not mean only one commit, but one change - however many commits it took. The
reason for this is that if you change X and Y but send a pull request for both
at the same time, we might really want X but disagree with Y, meaning we cannot
merge the request. Using the Git-Flow branching model you can create new
branches for both of these features and send two requests.

### Author

Eric Sizemore - <admin@secondversion.com> - <http://www.secondversion.com>

### License

Simple Template Engine is licensed under the GNU GPL v3 License - see the `LICENSE` file for details

