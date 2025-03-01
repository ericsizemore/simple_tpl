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

use Esi\SimpleTpl\Exception\TemplateHasNoContentException;
use Esi\SimpleTpl\Exception\TemplateNotFoundException;
use Esi\SimpleTpl\Exception\TemplateVariablesException;
use Esi\SimpleTpl\Storage\StorageInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException as PsrInvalidArgumentException;

use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function crc32;
use function dechex;
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
    public function __construct(
        private readonly StorageInterface $storage,
        private readonly ?CacheItemPoolInterface $cacheItemPool = null
    ) {}

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
     * @param string $templateName The path to the template file.
     *
     * @throws PsrInvalidArgumentException
     */
    public function display(string $templateName): void
    {
        echo $this->parse($templateName);
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
     * Parses the template and replaces variables.
     *
     * @param string $templateName The name of the template.
     *
     * @throws TemplateNotFoundException     If the template cannot be found or read.
     * @throws TemplateHasNoContentException If the template has no content.
     * @throws TemplateVariablesException    If there are no template variables set.
     * @throws PsrInvalidArgumentException
     *
     * @return string The parsed template content.
     */
    public function parse(string $templateName): string
    {
        $cacheKey = $this->generateCacheKey($templateName);

        if ($this->isUsingCache() && $this->cacheItemPool->hasItem($cacheKey)) {
            /**
             * @var string $templateCache
             */
            $templateCache = $this->cacheItemPool->getItem($cacheKey)->get();

            return $templateCache;
        }

        // Load template content
        $contents = $this->storage->loadTemplate($templateName);

        if ($this->tplVars === []) {
            throw TemplateVariablesException::create();
        }

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
     * @param string $templateName The path to the template file.
     *
     * @throws PsrInvalidArgumentException
     *
     * @return bool True if the cache was refreshed successfully, false otherwise.
     */
    public function refreshCache(string $templateName): bool
    {
        if (!$this->isUsingCache()) {
            return true;
        }

        return $this->cacheItemPool->deleteItem($this->generateCacheKey($templateName));
    }

    /**
     * Sets the left delimiter.
     *
     * @param string $delimiter The left delimiter.
     */
    public function setLeftDelimiter(string $delimiter): self
    {
        $this->leftDelimiter = $delimiter;

        return $this;
    }

    /**
     * Sets the right delimiter.
     *
     * @param string $delimiter The right delimiter.
     */
    public function setRightDelimiter(string $delimiter): self
    {
        $this->rightDelimiter = $delimiter;

        return $this;
    }

    /**
     * Sets the template variables.
     *
     * An empty array can be passed to clear/reset previously assigned variables.
     *
     * @param array<string> $tplVars Template variables and replacements.
     */
    public function setTplVars(array $tplVars): self
    {
        $this->tplVars = ($tplVars === []) ? [] : array_merge($this->tplVars, $tplVars);

        return $this;
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
     * Generates a cache key for a template.
     *
     * @param string $templateName The name of the template.
     *
     * @return string The generated cache key.
     */
    private function generateCacheKey(string $templateName): string
    {
        return \sprintf('template_%s', dechex(crc32($templateName)));
    }
}
