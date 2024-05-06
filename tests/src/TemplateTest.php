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

namespace Esi\SimpleTpl\Tests;

use Esi\SimpleTpl\Template;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Cache\Adapter\NullAdapter;

use function chmod;
use function ob_get_clean;
use function ob_start;

/**
 * @internal
 */
#[CoversClass(Template::class)]
final class TemplateTest extends TestCase
{
    private static string $fixtureDir;

    /**
     * @var array<string>
     */
    private static array $fixtureFiles;

    public static function setUpBeforeClass(): void
    {
        self::$fixtureDir   = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'fixtures';
        self::$fixtureFiles = [
            'empty'         => self::$fixtureDir . \DIRECTORY_SEPARATOR . 'empty.tpl',
            'no_cache_test' => self::$fixtureDir . \DIRECTORY_SEPARATOR . 'no_cache_test.tpl',
            'refresh_cache' => self::$fixtureDir . \DIRECTORY_SEPARATOR . 'refresh_cache.tpl',
            'unreadable'    => self::$fixtureDir . \DIRECTORY_SEPARATOR . 'unreadable.tpl',
            'valid'         => self::$fixtureDir . \DIRECTORY_SEPARATOR . 'valid.tpl',
            'valid_parsed'  => self::$fixtureDir . \DIRECTORY_SEPARATOR . 'valid_parsed.tpl',
        ];
    }

    public static function tearDownAfterClass(): void
    {
        self::$fixtureDir   = '';
        self::$fixtureFiles = [];
    }

    public function testDisplayDoesDisplay(): void
    {
        $template = new Template();

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        ob_start();
        $template->display(self::$fixtureFiles['valid']);
        $data = ob_get_clean();

        self::assertIsString($data);
        self::assertStringEqualsFile(self::$fixtureFiles['valid_parsed'], $data);
    }

    public function testGetTplVars(): void
    {
        $template = new Template();

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $tplVars = $template->getTplVars();

        self::assertSame([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ], $tplVars);
    }

    public function testParseEmptyTemplateFile(): void
    {
        $template = new Template();

        $this->expectException(RuntimeException::class);

        $template->parse(self::$fixtureFiles['empty']);
    }

    public function testParseInvalidTemplateFile(): void
    {
        $template = new Template();

        $this->expectException(InvalidArgumentException::class);

        $template->parse('/this/should/not/exist.tpl');
    }

    public function testParseTemplateIsNotCached(): void
    {
        $template = new Template(new NullAdapter());

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $data = $template->parse(self::$fixtureFiles['valid']);
        self::assertStringEqualsFile(self::$fixtureFiles['valid_parsed'], $data);

        // If things are working properly, with no cache, this should return the changed vars.
        $template->setTplVars([
            'title'   => 'Simple Template Engine Test - No Cache',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $data = $template->parse(self::$fixtureFiles['valid']);
        self::assertStringEqualsFile(self::$fixtureFiles['no_cache_test'], $data);
    }

    #[RequiresOperatingSystemFamily('Linux')]
    public function testParseUnreadableTemplateFile(): void
    {
        $template = new Template();

        chmod(self::$fixtureFiles['unreadable'], 0o000);

        $this->expectException(InvalidArgumentException::class);

        $template->parse(self::$fixtureFiles['unreadable']);

        chmod(self::$fixtureFiles['unreadable'], 0o644);
    }

    public function testParseWithCachedTemplate(): void
    {
        $template = new Template();

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $data = $template->parse(self::$fixtureFiles['valid']);
        self::assertStringEqualsFile(self::$fixtureFiles['valid_parsed'], $data);

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test - Is it cached?',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $data = $template->parse(self::$fixtureFiles['valid']);
        self::assertStringEqualsFile(self::$fixtureFiles['valid_parsed'], $data);

        self::assertTrue($template->clearCache());
    }

    public function testParseWithDirectoryNotFile(): void
    {
        $template = new Template();

        $this->expectException(InvalidArgumentException::class);

        $template->parse(self::$fixtureDir);
    }

    public function testParseWithoutTplVars(): void
    {
        $template = new Template();

        $this->expectException(LogicException::class);

        $template->setTplVars([]);
        $template->parse(self::$fixtureFiles['valid']);
    }

    public function testRefreshCache(): void
    {
        $template = new Template();

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $data = $template->parse(self::$fixtureFiles['refresh_cache']);
        self::assertStringEqualsFile(self::$fixtureFiles['valid_parsed'], $data);

        $isRefreshed = $template->refreshCache(self::$fixtureFiles['refresh_cache']);

        if ($isRefreshed) {
            $template->setTplVars([
                'title'   => 'Simple Template Engine Test - Refresh Cache',
                'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
            ]);

            $data = $template->parse(self::$fixtureFiles['refresh_cache']);
            self::assertStringNotEqualsFile(self::$fixtureFiles['valid_parsed'], $data);
        }
    }

    public function testSetLeftDelimiter(): void
    {
        $template = new Template();

        $template->setLeftDelimiter('{{');
        self::assertSame('{{', $template->getLeftDelimiter());

        $template->setLeftDelimiter('{');
        self::assertSame('{', $template->getLeftDelimiter());
    }

    public function testSetRightDelimiter(): void
    {
        $template = new Template();

        $template->setRightDelimiter('}}');
        self::assertSame('}}', $template->getRightDelimiter());

        $template->setRightDelimiter('}');
        self::assertSame('}', $template->getRightDelimiter());
    }

    public function testSetTplVars(): void
    {
        $template = new Template();

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        self::assertSame([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ], $template->getTplVars());

        $template->setTplVars([
            'altTitle'   => 'Can setTplVars merge?',
            'altContent' => 'Test if variables are merged.',
        ]);

        self::assertSame([
            'title'      => 'Simple Template Engine Test',
            'content'    => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
            'altTitle'   => 'Can setTplVars merge?',
            'altContent' => 'Test if variables are merged.',
        ], $template->getTplVars());

        $template->setTplVars([]);
        self::assertSame([], $template->getTplVars());
    }
}
