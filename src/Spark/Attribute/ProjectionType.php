<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ProjectionType
{
    public const KEYS_ONLY = 'KEYS_ONLY';
    public const INCLUDE = 'INCLUDE';
    public const ALL = 'ALL';

    public function __construct(
        private string $type = self::KEYS_ONLY,
    ) {}

    public function getType(): string
    {
        return $this->type;
    }
}
