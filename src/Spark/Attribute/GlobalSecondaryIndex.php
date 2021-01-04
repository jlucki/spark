<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GlobalSecondaryIndex
{
    public function __construct(
        private ?string $name = null,
    ) {}

    public function getName(): ?string
    {
        return $this->name;
    }
}
