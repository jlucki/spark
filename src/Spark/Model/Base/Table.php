<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Model\Base;

class Table
{

    /**
     * @param string $name
     * @param array<string, mixed> $description
     */
    public function __construct(
        private string $name,
        private array $description,
    ) {}

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDescription(): array
    {
        return $this->description;
    }

}
