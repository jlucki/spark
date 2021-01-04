<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Schema;

class Skeleton
{

    public function __construct(
        private string $tableName,
        private int $readCapacityUnits,
        private int $writeCapacityUnits,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getArray(): array
    {
        return [
            'TableName' => $this->tableName,
            'ProvisionedThroughput' => [
                'ReadCapacityUnits' => $this->readCapacityUnits,
                'WriteCapacityUnits' => $this->writeCapacityUnits,
            ],
            'KeySchema' => [],
            'AttributeDefinitions' => [],
        ];
    }

}
