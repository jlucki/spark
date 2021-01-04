<?php


namespace JLucki\ODM\Spark\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class ReadCapacityUnits
{
    public function __construct(
        private int $units,
    ) {}

    public function getUnits(): int
    {
        return $this->units;
    }
}
