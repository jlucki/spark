<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NonKeyAttributes
{
    /**
     * @param mixed ...$attributes
     */
    public function __construct(
        ...$attributes,
    ) {}
}
