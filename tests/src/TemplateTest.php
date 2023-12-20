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
use PHPUnit\Framework\Attributes\CoversClass;

/**
 */
#[CoversClass(Template::class)]
class TemplateTest extends TestCase
{
    /**
     * @var string
     */
    protected static string $testDir;

    /**
     * @var array<string>
     */
    protected static array $testFiles;

    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testDir = \dirname(__FILE__) . \DIRECTORY_SEPARATOR . 'dir1';
        self::$testFiles = [
            'file1' => self::$testDir . \DIRECTORY_SEPARATOR . 'file1',
            'file2' => self::$testDir . \DIRECTORY_SEPARATOR . 'file2'
        ];

        if (!\is_dir(self::$testDir)) {
            \mkdir(self::$testDir);
        }

        if (!\file_exists(self::$testFiles['file1'])) {
            \touch(self::$testFiles['file1']);
        }

        if (!\file_exists(self::$testFiles['file2'])) {
            \touch(self::$testFiles['file2']);
        }
    }

    /**
     */
    public static function tearDownAfterClass(): void
    {
        \unlink(self::$testFiles['file1']);
        \unlink(self::$testFiles['file2']);
        \usleep(90000);
        \rmdir(self::$testDir);

        self::$testDir = '';
        self::$testFiles = [];
    }

    /**
     * Test setLeftDelimiter()
     */
    public function testSetLeftDelimiter(): void
    {
        $template = new Template();

        $template->setLeftDelimiter('{{');
        self::assertEquals('{{', $template->getLeftDelimiter());

        $template->setLeftDelimiter('{');
        self::assertEquals('{', $template->getLeftDelimiter());
    }


    /**
     * Test setRightDelimiter()
     */
    public function testSetRightDelimiter(): void
    {
        $template = new Template();

        $template->setRightDelimiter('}}');
        self::assertEquals('}}', $template->getRightDelimiter());

        $template->setRightDelimiter('}');
        self::assertEquals('}', $template->getRightDelimiter());
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

        self::assertEquals([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.'
        ], $template->toArray());
    }

    /**
     * Test display()
     */
    public function testDisplay(): void
    {
        $template = new Template();

        \file_put_contents(self::$testFiles['file1'], <<<HTML
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

        \ob_start();
        $template->display(self::$testFiles['file1']);
        $data = \ob_get_contents();
        \ob_end_clean();

        self::assertEquals(<<<HTML
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
    }

    /**
     * Test parse() with non-existent file
     */
    public function testParseInvalidFile(): void
    {
        $template = new Template();

        self::expectException(\InvalidArgumentException::class);
        $data = $template->parse('/this/should/not/exist.tpl');        
    }

    /**
     * Test parse() with empty file
     */
    public function testParseEmptyFile(): void
    {
        $template = new Template();

        \file_put_contents(self::$testFiles['file2'], '');

        self::expectException(\Exception::class);
        $data = $template->parse(self::$testFiles['file2']);
    }

    /**
     * Test parse()
     */
    public function testParse(): void
    {
        $template = new Template();

        \file_put_contents(self::$testFiles['file1'], <<<HTML
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

        $data = $template->parse(self::$testFiles['file1']);

        self::assertEquals(<<<HTML
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

    }
}
