<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Query;

use JLucki\ODM\Spark\Interface\ItemInterface;

class QueryResult
{

    /**
     * @param ItemInterface[] $items
     * @param int $count
     * @param int $scannedCount
     * @param array<string, array>|null $lastEvaluatedKey
     * @param array<string, mixed> $metaData
     */
    public function __construct(
        private array $items,
        private int $count,
        private int $scannedCount,
        private ?array $lastEvaluatedKey,
        private array $metaData,
    ) {}

    /**
     * @return array<ItemInterface>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getScannedCount(): int
    {
        return $this->scannedCount;
    }

    /**
     * @return array<string, array>|null
     */
    public function getLastEvaluatedKey(): ?array
    {
        return $this->lastEvaluatedKey;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetaData(): array
    {
        return $this->metaData;
    }

}
