<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Interface;

interface ItemInterface
{

    /**
     * @return array<string, mixed>
     */
    public function getSchema(): array;

    /**
     * @return string
     */
    public function getTableName(): string;

    /**
     * @param bool $includeKeys
     * @return array<string, mixed>
     */
    public function toArray(bool $includeKeys = true): array;

    /**
     * @return array<string, mixed>
     */
    public function getKey(): array;

}
