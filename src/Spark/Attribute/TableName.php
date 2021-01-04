<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class TableName
{
    public function __construct(
        private string $tableName,
    ) {}

    public function getTableName(): string
    {
        return $this->tableName;
    }
}
