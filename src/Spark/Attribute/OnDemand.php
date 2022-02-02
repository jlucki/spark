<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_PROPERTY)]
class OnDemand
{
    public function __construct(
        private bool $onDemand = true,
    ) {}

    public function isOnDemand(): bool
    {
        return $this->onDemand;
    }
}
