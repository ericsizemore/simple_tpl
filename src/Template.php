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

namespace Esi\SimpleTpl;

use InvalidArgumentException;
use LogicException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException as PsrInvalidArgumentException;
use RuntimeException;

use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function crc32;
use function dechex;
use function file_get_contents;
use function str_replace;

final class Template
{
    private string $leftDelimiter = '{';

    private string $rightDelimiter = '}';

    /**
     * @var array<string>
     */
    private array $tplVars = [];

    /**
     * Constructor.
     */
    public function __construct(private readonly ?CacheItemPoolInterface $cacheItemPool = null) {}

    public function clearCache(): bool
    {
        if (!$this->isUsingCache()) {
            return true;
        }

        return $this->cacheItemPool->clear();
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    public function display(string $tplFile): void
    {
        echo $this->parse($tplFile);
    }

    public function getLeftDelimiter(): string
    {
        return $this->leftDelimiter;
    }

    public function getRightDelimiter(): string
    {
        return $this->rightDelimiter;
    }

    /**
     * @return array<string>
     */
    public function getTplVars(): array
    {
        return $this->tplVars;
    }

    /**
     * @psalm-assert-if-true !null $this->cacheItemPool
     */
    public function isUsingCache(): bool
    {
        return $this->cacheItemPool instanceof CacheItemPoolInterface;
    }

    /**
     * @throws InvalidArgumentException    if the file cannot be found or read.
     * @throws RuntimeException            if the file has no content.
     * @throws LogicException              if there are no template variables set.
     * @throws PsrInvalidArgumentException
     */
    public function parse(string $tplFile): string
    {
        // Make sure it's a valid file, and it exists
        if (!is_file($tplFile) || !is_readable($tplFile)) {
            throw new InvalidArgumentException(\sprintf('"%s" does not exist or is not a file.', $tplFile));
        }

        // are we using cache?
        $cacheKey = self::generateCacheKey($tplFile);

        if ($this->isUsingCache() && $this->cacheItemPool->hasItem($cacheKey)) {
            /**
             * @var string $templateCache
             */
            $templateCache = $this->cacheItemPool->getItem($cacheKey)->get();

            return $templateCache;
        }

        if ($this->tplVars === []) {
            throw new LogicException('Unable to parse template, no tplVars found');
        }

        $contents = (string) file_get_contents($tplFile);

        // Make sure it has content.
        if ($contents === '') {
            throw new RuntimeException(\sprintf('"%s" does not appear to have any valid content.', $tplFile));
        }

        // Perform replacements
        $contents = str_replace(
            array_map(
                fn (int|string $find): string => \sprintf('%s%s%s', $this->leftDelimiter, $find, $this->rightDelimiter),
                array_keys($this->tplVars)
            ),
            array_values($this->tplVars),
            $contents
        );

        if ($this->isUsingCache()) {
            $this->cacheItemPool->save($this->cacheItemPool->getItem($cacheKey)->set($contents));
        }

        return $contents;
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    public function refreshCache(string $tplFile): bool
    {
        if (!$this->isUsingCache()) {
            return true;
        }

        return $this->cacheItemPool->deleteItem(self::generateCacheKey($tplFile));
    }

    public function setLeftDelimiter(string $delimiter): void
    {
        $this->leftDelimiter = $delimiter;
    }

    public function setRightDelimiter(string $delimiter): void
    {
        $this->rightDelimiter = $delimiter;
    }

    /**
     * @param array<string> $tplVars Template variables and replacements
     */
    public function setTplVars(array $tplVars): void
    {
        if ($tplVars === []) {
            $this->tplVars = [];

            return;
        }

        $this->tplVars = array_merge($this->tplVars, $tplVars);
    }

    private static function generateCacheKey(string $file): string
    {
        return \sprintf('template_%s', dechex(crc32($file)));
    }
}
