<?php

declare(strict_types=1);

/**
 * This file is part of Esi\SimpleTpl.
 *
 * (c) 2006 - 2024 Eric Sizemore <admin@secondversion.com>
 *
 * This file is licensed under The MIT License. For the full copyright and
 * license information, please view the LICENSE.md file that was distributed
 * with this source code.
 */
// Include composer autoload file
require_once 'vendor/autoload.php';
use Esi\SimpleTpl\Template;

$tpl = new Template();

/*
Or if you aren't using via composer, you can use the
following code:

// Include class file and instantiate.
require_once '../src/Template.php';
$tpl = new \Esi\SimpleTpl\Template();
*/

// Set custom delimiters:
$tpl->setLeftDelimiter('{%');
$tpl->setRightDelimiter('%}');

/**
 * assign expects an array of:
 *     variable => value
 *
 * Variables in your template(s) should now be in the form of:
 *     {%variable%}
 */
$tpl->assign([
    'title'   => 'Simple Template Engine Test',
    'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
]);

// Parse the template file
$tpl->display('exampleCustomDelimiter.tpl');

// It's that simple, really.
