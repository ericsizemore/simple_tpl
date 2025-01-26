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

use LogicException;

class TemplateVariablesException extends LogicException
{
    public static function create(): self
    {
        return new self('Unable to parse template, no tplVars found');
    }
}
