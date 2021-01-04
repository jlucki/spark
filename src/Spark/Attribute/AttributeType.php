<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AttributeType
{
    public function __construct(
        private string $attributeType,
    ) {}

    public function getAttributeType(): string
    {
        return $this->attributeType;
    }
}
