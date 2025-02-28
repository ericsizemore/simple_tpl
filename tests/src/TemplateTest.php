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

namespace Esi\SimpleTpl\Tests;

use Esi\SimpleTpl\Exception\TemplateHasNoContentException;
use Esi\SimpleTpl\Exception\TemplateNotFoundException;
use Esi\SimpleTpl\Exception\TemplateVariablesException;
use Esi\SimpleTpl\Storage\DatabaseStorage;
use Esi\SimpleTpl\Storage\DatabaseStorageConfig;
use Esi\SimpleTpl\Storage\FilesystemStorage;
use Esi\SimpleTpl\Template;
use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;

use function chmod;
use function ob_get_clean;
use function ob_start;
use function sys_get_temp_dir;

/**
 * @internal
 */
#[CoversClass(Template::class)]
#[CoversClass(DatabaseStorage::class)]
#[CoversClass(DatabaseStorageConfig::class)]
#[CoversClass(FilesystemStorage::class)]
#[CoversClass(TemplateHasNoContentException::class)]
#[CoversClass(TemplateNotFoundException::class)]
#[CoversClass(TemplateVariablesException::class)]
final class TemplateTest extends TestCase
{
    private static string $fixtureDir;

    /**
     * @var array<string>
     */
    private static array $fixtureFiles;

    #[\Override]
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

    #[\Override]
    public static function tearDownAfterClass(): void
    {
        self::$fixtureDir   = '';
        self::$fixtureFiles = [];
    }

    public function testDatabaseLoadTemplate(): void
    {
        $pdo             = $this->createMock(PDO::class);
        $stmt            = $this->createMock(PDOStatement::class);
        $databaseStorage = new DatabaseStorage($pdo, new DatabaseStorageConfig());

        $templateName    = 'test_template';
        $templateContent = 'This is a test template content.';

        $pdo->expects(self::once())
            ->method('prepare')
            ->with('SELECT content FROM templates WHERE name = :name')
            ->willReturn($stmt);

        $stmt->expects(self::once())
            ->method('execute')
            ->with(['name' => $templateName]);

        $stmt->expects(self::once())
            ->method('fetchColumn')
            ->willReturn($templateContent);

        $result = $databaseStorage->loadTemplate($templateName);

        self::assertSame($templateContent, $result);
    }

    public function testDatabaseLoadTemplateNoContent(): void
    {
        $pdo             = $this->createMock(PDO::class);
        $stmt            = $this->createMock(PDOStatement::class);
        $databaseStorage = new DatabaseStorage($pdo, new DatabaseStorageConfig());

        $templateName = 'non_existent_template';

        $pdo->expects(self::once())
            ->method('prepare')
            ->with('SELECT content FROM templates WHERE name = :name')
            ->willReturn($stmt);

        $stmt->expects(self::once())
            ->method('execute')
            ->with(['name' => $templateName]);

        $stmt->expects(self::once())
            ->method('fetchColumn')
            ->willReturn('');

        $this->expectException(TemplateHasNoContentException::class);
        $this->expectExceptionMessage(\sprintf('"%s" does not appear to have any valid content.', $templateName));

        $databaseStorage->loadTemplate($templateName);
    }

    public function testDatabaseLoadTemplateNotFound(): void
    {
        $pdo             = $this->createMock(PDO::class);
        $stmt            = $this->createMock(PDOStatement::class);
        $databaseStorage = new DatabaseStorage($pdo, new DatabaseStorageConfig());

        $templateName = 'non_existent_template';

        $pdo->expects(self::once())
            ->method('prepare')
            ->with('SELECT content FROM templates WHERE name = :name')
            ->willReturn($stmt);

        $stmt->expects(self::once())
            ->method('execute')
            ->with(['name' => $templateName]);

        $stmt->expects(self::once())
            ->method('fetchColumn')
            ->willReturn(false);

        $this->expectException(TemplateNotFoundException::class);
        $this->expectExceptionMessage(\sprintf('Template "%s" does not exist.', $templateName));

        $databaseStorage->loadTemplate($templateName);
    }

    public function testDisplayDoesDisplay(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
            AbstractAdapter::createSystemCache('simple_tpl', 300, '', sys_get_temp_dir())
        );

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        ob_start();
        $template->display('valid');
        $data = ob_get_clean();

        self::assertIsString($data);
        self::assertStringEqualsFile(self::$fixtureFiles['valid_parsed'], $data);
    }

    public function testGetSchemaReturnsDefault(): void
    {
        $databaseStorageConfig = new DatabaseStorageConfig();

        $expected = \sprintf(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `%1$s` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `%2$s` VARCHAR(255) NOT NULL,
                `%3$s` MEDIUMTEXT NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `%2$s` (`%2$s`)
            )
            SQL, $databaseStorageConfig->tableName, $databaseStorageConfig->nameField, $databaseStorageConfig->contentField);

        self::assertSame($expected, $databaseStorageConfig->getSchema());
    }

    public function testGetTplVars(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
            AbstractAdapter::createSystemCache('simple_tpl', 300, '', sys_get_temp_dir())
        );

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
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
        );

        $this->expectException(TemplateHasNoContentException::class);
        $template->setTplVars(['foo' => 'bar']);
        $template->parse('empty');
    }

    public function testParseInvalidTemplateFile(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
        );

        $this->expectException(TemplateNotFoundException::class);

        $template->parse('not_existing');
    }

    public function testParseTemplateIsNotCached(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
            new NullAdapter()
        );

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $data = $template->parse('valid');
        self::assertStringEqualsFile(self::$fixtureFiles['valid_parsed'], $data);

        // If things are working properly, with no cache, this should return the changed vars.
        $template->setTplVars([
            'title'   => 'Simple Template Engine Test - No Cache',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $data = $template->parse('valid');
        self::assertStringEqualsFile(self::$fixtureFiles['no_cache_test'], $data);
    }

    #[RequiresOperatingSystemFamily('Linux')]
    public function testParseUnreadableTemplateFile(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
        );

        chmod(self::$fixtureFiles['unreadable'], 0o000);

        $this->expectException(TemplateNotFoundException::class);

        $template->parse('unreadable');

        chmod(self::$fixtureFiles['unreadable'], 0o644);
    }

    public function testParseWithCachedTemplate(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
            AbstractAdapter::createSystemCache('simple_tpl', 300, '', sys_get_temp_dir())
        );

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $data = $template->parse('valid');
        self::assertStringEqualsFile(self::$fixtureFiles['valid_parsed'], $data);

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test - Is it cached?',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $data = $template->parse('valid');
        self::assertStringEqualsFile(self::$fixtureFiles['valid_parsed'], $data);

        self::assertTrue($template->clearCache());
    }

    public function testParseWithDirectoryNotFile(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
        );

        $this->expectException(TemplateNotFoundException::class);

        $template->parse(self::$fixtureDir);
    }

    public function testParseWithMethodChaining(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
        );

        $data = $template->setTplVars([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ])->parse('valid');
        self::assertStringEqualsFile(self::$fixtureFiles['valid_parsed'], $data);

        $data = $template->setTplVars([
            'title'   => 'Simple Template Engine Test - Is it cached?',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ])->parse('valid');
        self::assertStringNotEqualsFile(self::$fixtureFiles['valid_parsed'], $data);

        self::assertTrue($template->clearCache());
    }

    public function testParseWithNoCacheProvided(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
        );

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $data = $template->parse('valid');
        self::assertStringEqualsFile(self::$fixtureFiles['valid_parsed'], $data);

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test - Is it cached?',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $data = $template->parse('valid');
        self::assertStringNotEqualsFile(self::$fixtureFiles['valid_parsed'], $data);

        self::assertTrue($template->clearCache());
    }

    public function testParseWithoutTplVars(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
        );

        $this->expectException(TemplateVariablesException::class);

        $template->setTplVars([]);
        $template->parse('valid');
    }

    public function testRefreshCache(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
            AbstractAdapter::createSystemCache('simple_tpl', 300, '', sys_get_temp_dir())
        );

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $data = $template->parse('refresh_cache');
        self::assertStringEqualsFile(self::$fixtureFiles['valid_parsed'], $data);

        $isRefreshed = $template->refreshCache('refresh_cache');

        if ($isRefreshed) {
            $template->setTplVars([
                'title'   => 'Simple Template Engine Test - Refresh Cache',
                'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
            ]);

            $data = $template->parse('refresh_cache');
            self::assertStringNotEqualsFile(self::$fixtureFiles['valid_parsed'], $data);
        }
    }

    public function testRefreshCacheNoCacheProvided(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
        );

        $template->setTplVars([
            'title'   => 'Simple Template Engine Test',
            'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
        ]);

        $data = $template->parse('refresh_cache');
        self::assertStringEqualsFile(self::$fixtureFiles['valid_parsed'], $data);

        $isRefreshed = $template->refreshCache('refresh_cache');

        if ($isRefreshed) {
            $template->setTplVars([
                'title'   => 'Simple Template Engine Test - Refresh Cache',
                'content' => 'This is a test of the Simple Template Engine class by Eric Sizemore.',
            ]);

            $data = $template->parse('refresh_cache');
            self::assertStringNotEqualsFile(self::$fixtureFiles['valid_parsed'], $data);
        }
    }

    public function testSetLeftDelimiter(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
        );

        $template->setLeftDelimiter('{{');
        self::assertSame('{{', $template->getLeftDelimiter());

        $template->setLeftDelimiter('{');
        self::assertSame('{', $template->getLeftDelimiter());
    }

    public function testSetRightDelimiter(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
        );

        $template->setRightDelimiter('}}');
        self::assertSame('}}', $template->getRightDelimiter());

        $template->setRightDelimiter('}');
        self::assertSame('}', $template->getRightDelimiter());
    }

    public function testSetTplVars(): void
    {
        $template = new Template(
            new FilesystemStorage(self::$fixtureDir),
        );

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

    public function testVerifyConfigRevertsToDefaults(): void
    {
        $databaseStorageConfig = new DatabaseStorageConfig('', ' ', '');

        self::assertSame('templates', $databaseStorageConfig->tableName);
        self::assertSame('name', $databaseStorageConfig->nameField);
        self::assertSame('content', $databaseStorageConfig->contentField);
    }
}
