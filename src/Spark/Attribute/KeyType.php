<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class KeyType
{
    public function __construct(
        private string $type,
    ) {}

    public function getType(): string
    {
        return $this->type;
    }
}
