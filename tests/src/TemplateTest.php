<?php

declare(strict_types = 1);

/**
 * Simple Template Engine
 *
 * @author    Eric Sizemore <admin@secondversion.com>
 * @package   Simple Template Engine
 * @link      http://www.secondversion.com/
 * @version   2.0.0
 * @copyright (C) 2006-2023 Eric Sizemore
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
 *    along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Esi\SimpleTpl\Tests;

use Esi\SimpleTpl\Template;
use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    /**
     * Test setLeftDelimiter()
     */
    public function testSetLeftDelimiter(): void
    {
        $template = new Template();

        $template->setLeftDelimiter('{{');
        $this->assertEquals('{{', $template->getLeftDelimiter());

        $template->setLeftDelimiter('{');
        $this->assertEquals('{', $template->getLeftDelimiter());
    }


    /**
     * Test setRightDelimiter()
     */
    public function testSetRightDelimiter(): void
    {
        $template = new Template();

        $template->setRightDelimiter('}}');
        $this->assertEquals('}}', $template->getRightDelimiter());

        $template->setRightDelimiter('}');
        $this->assertEquals('}', $template->getRightDelimiter());
    }


    /**
     * Test assign() and toArray()
     */
    public function testAssign(): void
    {
        $template = new Template();

        $template->assign([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.'
        ]);

        $this->assertEquals([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.'
        ], $template->toArray());
    }


    /**
     * Test parse()
     */
    public function testParse(): void
    {
        $template = new Template();

        $dir = \dirname(__FILE__) . \DIRECTORY_SEPARATOR . 'dir1';

        if (!\is_dir($dir)) {
            \mkdir($dir);
        }

        $file1 = $dir . \DIRECTORY_SEPARATOR . 'file1';

        if (!\file_exists($file1)) {
            \touch($file1);
        }

        \file_put_contents($file1, <<<HTML
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
HTML);

        $template->assign([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.'
        ]);

        $data = $template->parse($file1);

        $this->assertEquals(<<<HTML
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="content-type" content="text/html" />
	<title>Simple Template Engine Test</title>
</head>

<body>

<p>This is a test of the Simple Template Engine class by Eric Sizemore.</p>

</body>
</html>
HTML, $data);

        if (\unlink($file1)) {
            \rmdir($dir);
        }
    }
}
