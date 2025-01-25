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

    /**
     * Clears the cache.
     *
     * @return bool True if the cache was cleared successfully, false otherwise.
     */
    public function clearCache(): bool
    {
        if (!$this->isUsingCache()) {
            return true;
        }

        return $this->cacheItemPool->clear();
    }

    /**
     * Displays the parsed template.
     *
     * @param string $tplFile The path to the template file.
     *
     * @throws PsrInvalidArgumentException
     */
    public function display(string $tplFile): void
    {
        echo $this->parse($tplFile);
    }

    /**
     * Gets the left delimiter.
     *
     * @return string The left delimiter.
     */
    public function getLeftDelimiter(): string
    {
        return $this->leftDelimiter;
    }

    /**
     * Gets the right delimiter.
     *
     * @return string The right delimiter.
     */
    public function getRightDelimiter(): string
    {
        return $this->rightDelimiter;
    }

    /**
     * Gets the template variables.
     *
     * @return array<string> The template variables.
     */
    public function getTplVars(): array
    {
        return $this->tplVars;
    }

    /**
     * Checks if caching is enabled.
     *
     * @psalm-assert-if-true !null $this->cacheItemPool
     *
     * @return bool True if caching is enabled, false otherwise.
     */
    public function isUsingCache(): bool
    {
        return $this->cacheItemPool instanceof CacheItemPoolInterface;
    }

    /**
     * Parses the template file and replaces variables.
     *
     * @param string $tplFile The path to the template file.
     *
     * @throws InvalidArgumentException    If the file cannot be found or read.
     * @throws RuntimeException            If the file has no content.
     * @throws LogicException              If there are no template variables set.
     * @throws PsrInvalidArgumentException
     *
     * @return string The parsed template content.
     */
    public function parse(string $tplFile): string
    {
        $this->validateFile($tplFile);

        $cacheKey = $this->generateCacheKey($tplFile);

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

        $contents = $this->readFile($tplFile);

        // Perform replacements
        $contents = $this->doReplacements($contents);

        if ($this->isUsingCache()) {
            $this->cacheItemPool->save($this->cacheItemPool->getItem($cacheKey)->set($contents));
        }

        return $contents;
    }

    /**
     * Refreshes the cache for a specific template file.
     *
     * @param string $tplFile The path to the template file.
     *
     * @throws PsrInvalidArgumentException
     *
     * @return bool True if the cache was refreshed successfully, false otherwise.
     */
    public function refreshCache(string $tplFile): bool
    {
        if (!$this->isUsingCache()) {
            return true;
        }

        return $this->cacheItemPool->deleteItem(self::generateCacheKey($tplFile));
    }

    /**
     * Sets the left delimiter.
     *
     * @param string $delimiter The left delimiter.
     */
    public function setLeftDelimiter(string $delimiter): void
    {
        $this->leftDelimiter = $delimiter;
    }

    /**
     * Sets the right delimiter.
     *
     * @param string $delimiter The right delimiter.
     */
    public function setRightDelimiter(string $delimiter): void
    {
        $this->rightDelimiter = $delimiter;
    }

    /**
     * Sets the template variables.
     *
     * An empty array can be passed to clear/reset previously assigned variables.
     *
     * @param array<string> $tplVars Template variables and replacements.
     */
    public function setTplVars(array $tplVars): void
    {
        if ($tplVars === []) {
            $this->tplVars = [];

            return;
        }

        $this->tplVars = array_merge($this->tplVars, $tplVars);
    }

    /**
     * Replaces template variables in the content.
     *
     * @param string $contents The content of the template file.
     *
     * @return string The content with template variables replaced.
     */
    private function doReplacements(string $contents): string
    {
        return str_replace(
            array_map(
                fn (int|string $find): string => \sprintf('%s%s%s', $this->leftDelimiter, $find, $this->rightDelimiter),
                array_keys($this->tplVars)
            ),
            array_values($this->tplVars),
            $contents
        );
    }

    /**
     * Generates a cache key for a template file.
     *
     * @param string $file The path to the template file.
     *
     * @return string The generated cache key.
     */
    private function generateCacheKey(string $file): string
    {
        return \sprintf('template_%s', dechex(crc32($file)));
    }

    /**
     * Reads the content of the template file.
     *
     * @param string $tplFile The path to the template file.
     *
     * @throws RuntimeException If the file has no valid content.
     *
     * @return string The content of the template file.
     */
    private function readFile(string $tplFile): string
    {
        $contents = file_get_contents($tplFile);

        if ($contents === '' || $contents === false) {
            throw new RuntimeException(\sprintf('"%s" does not appear to have any valid content.', $tplFile));
        }

        return $contents;
    }

    /**
     * Validates the template file.
     *
     * @param string $tplFile The path to the template file.
     *
     * @throws InvalidArgumentException If the file does not exist or is not readable.
     */
    private function validateFile(string $tplFile): void
    {
        if (!is_file($tplFile) || !is_readable($tplFile)) {
            throw new InvalidArgumentException(\sprintf('"%s" does not exist or is not a readable file.', $tplFile));
        }
    }
}
