<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Schema;

use JLucki\ODM\Spark\Attribute\AttributeName;
use JLucki\ODM\Spark\Attribute\AttributeType;

class AttributeDefinition
{
    /**
     * @param array<string, string> $attributeDefinition
     */
    public function __construct(
        private array $attributeDefinition,
    ) {}

    /**
     * @return array<string, string>
     */
    public function getArray(): array
    {
        return [
            'AttributeName' => $this->attributeDefinition[AttributeName::class],
            'AttributeType' => $this->attributeDefinition[AttributeType::class],
        ];
    }
}
