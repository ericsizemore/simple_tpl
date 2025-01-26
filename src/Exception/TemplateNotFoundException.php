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

namespace Esi\SimpleTpl\Exception;

use InvalidArgumentException;

class TemplateNotFoundException extends InvalidArgumentException
{
    public static function forDatabaseTemplate(string $templateName): self
    {
        return new self(\sprintf('Template "%s" does not exist.', $templateName));
    }

    public static function forFilesystemTemplate(string $templatePath): self
    {
        return new self(\sprintf('"%s" does not exist or is not a readable file.', $templatePath));
    }
}
