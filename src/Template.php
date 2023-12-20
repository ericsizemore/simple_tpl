<?php

declare(strict_types=1);

/**
 * Simple Template Engine
 *
 * @author    Eric Sizemore <admin@secondversion.com>
 * @package   Simple Template Engine
 * @link      http://www.secondversion.com/
 * @version   2.0.1
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
namespace Esi\SimpleTpl;

/**
 * Pretty simple template engine. Performs simple search and replace on defined
 * variables.
 */
class Template
{
    /**
     * Template variables and their replacements
     *
     * @var  array<mixed>
     */
    private array $tplVars = [];

    /**
     * Delimiters to use when search for the variables to replace.
     *
     * @var  string
     */
    private string $leftDelimiter = '{';
    private string $rightDelimiter = '}';

    /**
     * Constructor
     */
    public function __construct()
    {
        //
    }

    /**
     * Setter for {@see self::$leftDelimiter}
     *
     * @param  string  $delimiter
     */
    public function setLeftDelimiter(string $delimiter): void
    {
        $this->leftDelimiter = $delimiter;
    }

    /**
     * Getter for {@see self::$leftDelimiter}
     *
     * @return  string
     */
    public function getLeftDelimiter(): string
    {
        return $this->leftDelimiter;
    }

    /**
     * Setter for {@see self::$rightDelimiter}
     *
     * @param  string  $delimiter
     */
    public function setRightDelimiter(string $delimiter): void
    {
        $this->rightDelimiter = $delimiter;
    }

    /**
     * Getter for {@see self::$rightDelimiter}
     *
     * @return  string
     */
    public function getRightDelimiter(): string
    {
        return $this->rightDelimiter;
    }

    /**
     * Return the currently assigned variables.
     *
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->tplVars;
    }

    /**
     * Assign our variables and replacements
     *
     * @param  array<mixed>  $tplVars  Template variables and replacements
     */
    public function assign(array $tplVars): void
    {
        $this->tplVars = \array_merge($this->tplVars, $tplVars);
    }

    /**
     * Output the template.
     *
     * Essentially just a wrapper for {@see self::parse()}
     *
     * @param  string  $tplFile  Template file
     */
    public function display(string $tplFile): void
    {
        echo $this->parse($tplFile);
    }

    /**
     * Parse the template file
     *
     * @param   string  $tplFile  Template file
     * @return  string            Parsed template data
     *
     * @throws  \InvalidArgumentException if the file cannot be found or read.
     * @throws  \Exception                if the file has no content.
     */
    public function parse(string $tplFile): string
    {
        // Make sure it's a valid file, and it exists
        if (!\is_file($tplFile) || !\is_readable($tplFile)) {
            throw new \InvalidArgumentException(sprintf('"%s" does not exist or is not a file.', $tplFile));
        }

        $contents = \file_get_contents($tplFile);

        // Make sure it has content. file_get_contents can return 'false' on error
        if ($contents === '' || $contents === false) {
            throw new \Exception(\sprintf('"%s" does not appear to have any valid content.', $tplFile));
        }

        // Process replacements
        /** @var string $replace **/
        foreach ($this->tplVars as $find => $replace) {
            $contents = \str_replace(\sprintf(
                '%s%s%s',
                $this->leftDelimiter,
                $find,
                $this->rightDelimiter
            ), $replace, $contents);
        }
        return $contents;
    }
}
