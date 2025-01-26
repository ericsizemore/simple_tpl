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

interface StorageInterface
{
    /**
     * Loads a template by its name.
     *
     * @param string $templateName The name of the template.
     *
     * @throws TemplateHasNoContentException If the file has no valid content.
     * @throws TemplateNotFoundException     If the template cannot be found.
     *
     * @return string The content of the template.
     */
    public function loadTemplate(string $templateName): string;
}
