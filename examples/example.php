<?php

/**
 * Simple Template Engine
 *
 * @author    Eric Sizemore <admin@secondversion.com>
 * @package   Simple Template Engine
 * @link      http://www.secondversion.com/
 * @version   1.0.5
 * @copyright (C) 2006-2019 Eric Sizemore
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GNU Public License
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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

// It's that simple, really.
