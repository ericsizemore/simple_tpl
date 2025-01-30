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

use function file_get_contents;
use function is_file;
use function is_readable;
use function rtrim;

use const DIRECTORY_SEPARATOR;

readonly class FilesystemStorage implements StorageInterface
{
    /**
     * The format used by {@see getTemplatePath()}.
     *
     * @const string
     */
    private const TemplatePathString = '%s%s.tpl';

    /**
     * The directory/folder containing template files.
     */
    private string $templateDir;

    /**
     * Constructor.
     *
     * @param string $templateDir The directory/folder containing template files.
     */
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
        return $this->readFile($this->getTemplatePath($templateName));
    }

    /**
     * Builds the full path to a given template.
     *
     * @param string $templateName The name of the template.
     *
     * @return string The full path to the template.
     */
    private function getTemplatePath(string $templateName): string
    {
        return \sprintf(self::TemplatePathString, $this->templateDir, $templateName);
    }

    /**
     * Reads the content of the template file.
     *
     * @param string $templatePath The path to the template file.
     *
     * @throws TemplateHasNoContentException If the file has no valid content.
     * @throws TemplateNotFoundException     If the file does not exist or is not readable.
     *
     * @return string The content of the template file.
     */
    private function readFile(string $templatePath): string
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
     * @throws TemplateNotFoundException If the file does not exist or is not readable.
     */
    private function validateFile(string $templatePath): void
    {
        if (!is_file($templatePath) || !is_readable($templatePath)) {
            throw TemplateNotFoundException::forFilesystemTemplate($templatePath);
        }
    }
}
