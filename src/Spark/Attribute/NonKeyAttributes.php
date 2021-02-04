<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NonKeyAttributes
{

    /**
     * @param string[] $attributes
     */
    public function __construct(
        private array $attributes,
    ) {}

    /**
     * @return string[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

}
