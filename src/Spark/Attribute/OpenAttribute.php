<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OpenAttribute
{
    public function __construct(
        private string $attributeName,
    ) {}

    public function getAttributeName(): string
    {
        return $this->attributeName;
    }
}
