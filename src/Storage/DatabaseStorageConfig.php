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

namespace Esi\SimpleTpl\Storage;

/**
 * Provides the table name, name of the field that stores template content, and name of the field
 * that holds the template name.
 *
 * For a structure of:
 *
 * ```
 * CREATE TABLE IF NOT EXISTS `templates` (
 * `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
 * `name` VARCHAR(255) NOT NULL,
 * `content` MEDIUMTEXT NOT NULL,
 * PRIMARY KEY (`id`),
 * UNIQUE KEY `name` (`name`)
 * )
 * ```
 *
 * You would instantiate this class as:
 *
 * ```
 * $config = new DatabaseStorageConfig('templates', 'name', 'content');
 * ```
 */
final class DatabaseStorageConfig
{
    public function __construct(
        public string $tableName = 'templates',
        public string $nameField = 'name',
        public string $contentField = 'content'
    ) {
        $this->verifyConfig();
    }

    /**
     * Simple utility method to return the table schema with the given tableName, nameField, and contentField.
     */
    public function getSchema(): string
    {
        return \sprintf(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `%1$s` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `%2$s` VARCHAR(255) NOT NULL,
                `%3$s` MEDIUMTEXT NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `%2$s` (`%2$s`)
            )
            SQL, $this->tableName, $this->nameField, $this->contentField);
    }

    /**
     * Performs a simple check on the provided options and reverts to defaults if needed.
     */
    private function verifyConfig(): void
    {
        $this->contentField = trim($this->contentField);
        $this->tableName    = trim($this->tableName);
        $this->nameField    = trim($this->nameField);

        // Set to defaults, if somehow empty after trim.
        if ($this->contentField === '') {
            $this->contentField = 'content';
        }

        if ($this->tableName === '') {
            $this->tableName = 'templates';
        }

        if ($this->nameField === '') {
            $this->nameField = 'name';
        }
    }
}
