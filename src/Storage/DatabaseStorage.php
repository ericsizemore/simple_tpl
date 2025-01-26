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

use Esi\SimpleTpl\Exception\TemplateHasNoContentException;
use Esi\SimpleTpl\Exception\TemplateNotFoundException;
use PDO;

readonly class DatabaseStorage implements StorageInterface
{
    public function __construct(private PDO $pdo) {}

    /**
     * @inheritDoc
     */
    #[\Override]
    public function loadTemplate(string $templateName): string
    {
        $stmt = $this->pdo->prepare('SELECT content FROM templates WHERE name = :name');

        $stmt->execute(['name' => $templateName]);

        /**
         * @var false|string $content
         */
        $content = $stmt->fetchColumn();

        if ($content === false) {
            throw TemplateNotFoundException::forDatabaseTemplate($templateName);
        }

        if ($content === '') {
            throw TemplateHasNoContentException::create($templateName);
        }

        return $content;
    }
}
