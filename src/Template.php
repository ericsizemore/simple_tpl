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

namespace Esi\SimpleTpl;

use Exception;
use InvalidArgumentException;

use function array_merge;
use function file_get_contents;
use function is_file;
use function is_readable;
use function sprintf;
use function str_replace;

/**
 * Pretty simple template engine. Performs simple search and replace on defined
 * variables.
 */
final class Template
{
    /**
     * Delimiters to use when search for the variables to replace.
     */
    private string $leftDelimiter = '{';

    private string $rightDelimiter = '}';

    /**
     * Template variables and their replacements.
     *
     * @var array<string>
     */
    private array $tplVars = [];

    /**
     * Constructor.
     */
    public function __construct() {}

    /**
     * Assign our variables and replacements.
     *
     * @param array<string> $tplVars Template variables and replacements
     */
    public function assign(array $tplVars): void
    {
        $this->tplVars = array_merge($this->tplVars, $tplVars);
    }

    /**
     * Output the template.
     *
     * Essentially just a wrapper for {@see self::parse()}
     *
     * @param string $tplFile Template file
     */
    public function display(string $tplFile): void
    {
        echo $this->parse($tplFile);
    }

    /**
     * Getter for {@see self::$leftDelimiter}.
     */
    public function getLeftDelimiter(): string
    {
        return $this->leftDelimiter;
    }

    /**
     * Getter for {@see self::$rightDelimiter}.
     */
    public function getRightDelimiter(): string
    {
        return $this->rightDelimiter;
    }

    /**
     * Parse the template file.
     *
     * @param string $tplFile Template file
     *
     * @throws InvalidArgumentException if the file cannot be found or read.
     * @throws Exception                if the file has no content.
     *
     * @return string Parsed template data
     */
    public function parse(string $tplFile): string
    {
        // Make sure it's a valid file, and it exists
        if (!is_file($tplFile) || !is_readable($tplFile)) {
            throw new InvalidArgumentException(sprintf('"%s" does not exist or is not a file.', $tplFile));
        }

        $contents = file_get_contents($tplFile);

        // Make sure it has content. file_get_contents can return 'false' on error
        if ($contents === '' || $contents === false) {
            throw new Exception(sprintf('"%s" does not appear to have any valid content.', $tplFile));
        }

        // Process replacements
        foreach ($this->tplVars as $find => $replace) {
            $contents = str_replace(sprintf(
                '%s%s%s',
                $this->leftDelimiter,
                $find,
                $this->rightDelimiter
            ), $replace, $contents);
        }

        return $contents;
    }

    /**
     * Setter for {@see self::$leftDelimiter}.
     */
    public function setLeftDelimiter(string $delimiter): void
    {
        $this->leftDelimiter = $delimiter;
    }

    /**
     * Setter for {@see self::$rightDelimiter}.
     */
    public function setRightDelimiter(string $delimiter): void
    {
        $this->rightDelimiter = $delimiter;
    }

    /**
     * Return the currently assigned variables.
     *
     * @return array<string>
     */
    public function toArray(): array
    {
        return $this->tplVars;
    }
}
