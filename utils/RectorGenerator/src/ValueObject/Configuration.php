<?php

declare(strict_types=1);

namespace Rector\Utils\RectorGenerator\ValueObject;

use Nette\Utils\Strings;

final class Configuration
{
    /**
     * @var string
     */
    private $package;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $category;

    /**
     * @var string
     */
    private $codeBefore;

    /**
     * @var string
     */
    private $codeAfter;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string|null
     */
    private $setConfig;

    /**
     * @var string[]
     */
    private $nodeTypes = [];

    /**
     * @var string[]
     */
    private $source = [];

    /**
     * @var bool
     */
    private $isPhpSnippet = false;

    /**
     * @param string[] $nodeTypes
     * @param string[] $source
     */
    public function __construct(
        string $package,
        string $name,
        string $category,
        array $nodeTypes,
        string $description,
        string $codeBefore,
        string $codeAfter,
        array $source,
        ?string $setConfig,
        bool $isPhpSnippet
    ) {
        $this->package = $package;
        $this->setName($name);
        $this->category = $category;
        $this->nodeTypes = $nodeTypes;
        $this->codeBefore = $codeBefore;
        $this->codeAfter = $codeAfter;
        $this->description = $description;
        $this->source = $source;
        $this->setConfig = $setConfig;
        $this->isPhpSnippet = $isPhpSnippet;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPackage(): string
    {
        return $this->package;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return $this->nodeTypes;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getCodeBefore(): string
    {
        return $this->codeBefore;
    }

    public function getCodeAfter(): string
    {
        return $this->codeAfter;
    }

    /**
     * @return string[]
     */
    public function getSource(): array
    {
        return $this->source;
    }

    public function getSetConfig(): ?string
    {
        return $this->setConfig;
    }

    public function isPhpSnippet(): bool
    {
        return $this->isPhpSnippet;
    }

    private function setName(string $name): void
    {
        if (! Strings::endsWith($name, 'Rector')) {
            $name .= 'Rector';
        }

        $this->name = $name;
    }
}
