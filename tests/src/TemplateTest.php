<?php

declare(strict_types=1);

/**
 * This file is part of Esi\SimpleTpl.
 *
 * (c) 2006 - 2024 Eric Sizemore <admin@secondversion.com>
 *
 * This file is licensed under the GNU Public License v3. For the full
 * copyright and license information, please view the LICENSE.md file
 * that was distributed with this source code.
 */

namespace Esi\SimpleTpl\Tests;

use Esi\SimpleTpl\Template;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_exists;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function rmdir;
use function touch;
use function unlink;
use function usleep;

use const DIRECTORY_SEPARATOR;

/**
 * @internal
 */
#[CoversClass(Template::class)]
final class TemplateTest extends TestCase
{
    private static string $testDir;

    /**
     * @var array<string>
     */
    private static array $testFiles;

    public static function setUpBeforeClass(): void
    {
        self::$testDir   = __DIR__ . DIRECTORY_SEPARATOR . 'dir1';
        self::$testFiles = [
            'file1' => self::$testDir . DIRECTORY_SEPARATOR . 'file1',
            'file2' => self::$testDir . DIRECTORY_SEPARATOR . 'file2',
        ];

        if (!is_dir(self::$testDir)) {
            mkdir(self::$testDir);
        }

        if (!file_exists(self::$testFiles['file1'])) {
            touch(self::$testFiles['file1']);
        }

        if (!file_exists(self::$testFiles['file2'])) {
            touch(self::$testFiles['file2']);
        }
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$testFiles['file1']);
        unlink(self::$testFiles['file2']);
        usleep(100000);
        rmdir(self::$testDir);

        self::$testDir   = '';
        self::$testFiles = [];
    }

    /**
     * Test assign() and toArray().
     */
    public function testAssign(): void
    {
        $template = new Template();

        $template->assign([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        self::assertSame([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ], $template->toArray());
    }

    /**
     * Test display().
     */
    public function testDisplay(): void
    {
        $template = new Template();

        file_put_contents(
            self::$testFiles['file1'],
            <<<html_WRAP
                <!DOCTYPE HTML>
                <html>
                <head>
                \t<meta http-equiv="content-type" content="text/html" />
                \t<title>{title}</title>
                </head>

                <body>

                <p>{content}</p>

                </body>
                </html>
                html_WRAP
        );

        $template->assign([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        ob_start();
        $template->display(self::$testFiles['file1']);
        $data = ob_get_contents();
        ob_end_clean();

        self::assertSame(<<<html_WRAP
            <!DOCTYPE HTML>
            <html>
            <head>
            \t<meta http-equiv="content-type" content="text/html" />
            \t<title>Simple Template Engine Test</title>
            </head>

            <body>

            <p>This is a test of the Simple Template Engine class by Eric Sizemore.</p>

            </body>
            </html>
            html_WRAP, $data);
    }

    /**
     * Test parse().
     */
    public function testParse(): void
    {
        $template = new Template();

        file_put_contents(
            self::$testFiles['file1'],
            <<<html_WRAP
                <!DOCTYPE HTML>
                <html>
                <head>
                \t<meta http-equiv="content-type" content="text/html" />
                \t<title>{title}</title>
                </head>

                <body>

                <p>{content}</p>

                </body>
                </html>
                html_WRAP
        );

        $template->assign([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $data = $template->parse(self::$testFiles['file1']);

        self::assertSame(<<<html_WRAP
            <!DOCTYPE HTML>
            <html>
            <head>
            \t<meta http-equiv="content-type" content="text/html" />
            \t<title>Simple Template Engine Test</title>
            </head>

            <body>

            <p>This is a test of the Simple Template Engine class by Eric Sizemore.</p>

            </body>
            </html>
            html_WRAP, $data);

    }

    /**
     * Test parse() with empty file.
     */
    public function testParseEmptyFile(): void
    {
        $template = new Template();

        file_put_contents(self::$testFiles['file2'], '');

        /** @scrutinizer ignore-call */
        self::expectException(Exception::class);

        $template->parse(self::$testFiles['file2']);
    }

    /**
     * Test parse() with non-existent file.
     */
    public function testParseInvalidFile(): void
    {
        $template = new Template();

        /** @scrutinizer ignore-call */
        self::expectException(InvalidArgumentException::class);

        $template->parse('/this/should/not/exist.tpl');
    }

    /**
     * Test setLeftDelimiter().
     */
    public function testSetLeftDelimiter(): void
    {
        $template = new Template();

        $template->setLeftDelimiter('{{');
        self::assertSame('{{', $template->getLeftDelimiter());

        $template->setLeftDelimiter('{');
        self::assertSame('{', $template->getLeftDelimiter());
    }

    /**
     * Test setRightDelimiter().
     */
    public function testSetRightDelimiter(): void
    {
        $template = new Template();

        $template->setRightDelimiter('}}');
        self::assertSame('}}', $template->getRightDelimiter());

        $template->setRightDelimiter('}');
        self::assertSame('}', $template->getRightDelimiter());
    }
}
