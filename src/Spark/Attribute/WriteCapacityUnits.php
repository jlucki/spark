<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class WriteCapacityUnits
{
    public function __construct(
        private int $units,
    ) {}

    public function getUnits(): int
    {
        return $this->units;
    }
}
