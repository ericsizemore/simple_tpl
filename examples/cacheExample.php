<?php

declare(strict_types=1);

/**
 * This file is part of Esi\SimpleTpl.
 *
 * (c) 2006 - 2025 Eric Sizemore <admin@secondversion.com>
 *
 * This file is licensed under The MIT License. For the full copyright and
 * license information, please view the LICENSE.md file that was distributed
 * with this source code.
 */
// Include composer autoload file
require_once __DIR__ . '/vendor/autoload.php';

use Esi\SimpleTpl\Template;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

/**
 * NOTE: This example requires symfony/cache being installed.
 */
if (!class_exists(AbstractAdapter::class)) {
    throw new RuntimeException('Please install symfony/cache to run this example. E.g.: composer require symfony/cache:^7.1');
}

/**
 * Symfony's AbstractAdapter::createSystemCache() returns the best possible adapter that your runtime supports.
 * Generally, it will create a cache via PHP files (Opcache must be enabled via opcache.enable in php.ini), and chain that with APCu if your system supports it.
 *
 * For more information on symfony/cache's available cache pool (PSR-6) adapters:
 *
 * @see https://symfony.com/doc/current/components/cache/cache_pools.html
 */
$tpl = new Template(
    AbstractAdapter::createSystemCache(namespace: 'simple_tpl', defaultLifetime: 300, version: '', directory: sys_get_temp_dir())
);

/**
 * assign expects an array of:
 *     variable => value
 *
 * Variables in your template(s) should be in the form of:
 *     {variable}
 * Unless you have changed the delimiters with:
 *      Template::setLeftDelimiter()
 *      Template::setRightDelimiter()
 */
$tpl->setTplVars([
    'title'   => 'Simple Template Engine Test',
    'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
]);

// Parse the template file
$tpl->display(__DIR__ . '/example.tpl');
