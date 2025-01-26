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
use InvalidArgumentException;

use function file_get_contents;
use function is_file;
use function is_readable;
use function rtrim;

use const DIRECTORY_SEPARATOR;

class FilesystemStorage implements StorageInterface
{
    private readonly string $templateDir;

    public function __construct(string $templateDir)
    {
        $this->templateDir = rtrim($templateDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function loadTemplate(string $templateName): string
    {
        $templatePath = \sprintf('%s%s.tpl', $this->templateDir, $templateName);

        return $this->readFile($templatePath);
    }

    /**
     * Reads the content of the template file.
     *
     * @param string $templatePath The path to the template file.
     *
     * @throws TemplateHasNoContentException If the file has no valid content.
     *
     * @return string The content of the template file.
     */
    protected function readFile(string $templatePath): string
    {
        $this->validateFile($templatePath);

        $contents = file_get_contents($templatePath);

        if ($contents === '' || $contents === false) {
            throw TemplateHasNoContentException::create($templatePath);
        }

        return $contents;
    }

    /**
     * Validates the template file.
     *
     * @param string $templatePath The path to the template file.
     *
     * @throws InvalidArgumentException If the file does not exist or is not readable.
     */
    protected function validateFile(string $templatePath): void
    {
        if (!is_file($templatePath) || !is_readable($templatePath)) {
            throw TemplateNotFoundException::forFilesystemTemplate($templatePath);
        }
    }
}
