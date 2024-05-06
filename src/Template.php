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

namespace Esi\SimpleTpl;

use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\PruneableInterface;

use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function file_get_contents;
use function is_file;
use function is_readable;
use function md5;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;

final class Template
{
    private AdapterInterface $cache;

    private string $leftDelimiter = '{';

    private string $rightDelimiter = '}';

    /**
     * @var array<string>
     */
    private array $tplVars = [];

    /**
     * Constructor.
     */
    public function __construct(?AdapterInterface $cacheAdapter = null, ?string $cachePath = null)
    {
        $this->cache = $cacheAdapter ?? AbstractAdapter::createSystemCache('simple_tpl', 300, '', $cachePath ?? sys_get_temp_dir());
        $this->pruneExpired();
    }

    public function clearCache(): bool
    {
        return $this->cache->clear();
    }

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
     * @throws InvalidArgumentException if the file cannot be found or read.
     * @throws RuntimeException         if the file has no content.
     * @throws LogicException           if there are no template variables set.
     */
    public function parse(string $tplFile): string
    {
        // Make sure it's a valid file, and it exists
        if (!is_file($tplFile) || !is_readable($tplFile)) {
            throw new InvalidArgumentException(sprintf('"%s" does not exist or is not a file.', $tplFile));
        }

        $cacheKey = 'template_' . md5($tplFile);

        if ($this->cache->hasItem($cacheKey)) {
            /**
             * @var string $templateCache
             */
            $templateCache = $this->cache->getItem($cacheKey)->get();

            return $templateCache;
        }

        $contents = (string) file_get_contents($tplFile);

        // Make sure it has content.
        if ($contents === '') {
            throw new RuntimeException(sprintf('"%s" does not appear to have any valid content.', $tplFile));
        }

        if ($this->tplVars === []) {
            throw new LogicException('Unable to parse template, no tplVars found');
        }

        // Perform replacements
        $contents = str_replace(
            array_map(
                fn (int|string $find): string => sprintf('%s%s%s', $this->leftDelimiter, $find, $this->rightDelimiter),
                array_keys($this->tplVars)
            ),
            array_values($this->tplVars),
            $contents
        );

        $this->cache->save($this->cache->getItem($cacheKey)->set($contents));

        return $contents;
    }

    public function pruneExpired(): void
    {
        if ($this->cache instanceof PruneableInterface) {
            $this->cache->prune();
        }
    }

    public function refreshCache(string $tplFile): bool
    {
        $cacheKey = 'template_' . md5($tplFile);

        return $this->cache->deleteItem($cacheKey);
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
        if (\count($tplVars) === 0) {
            $this->tplVars = [];
        } else {
            $this->tplVars = array_merge($this->tplVars, $tplVars);
        }
    }
}
