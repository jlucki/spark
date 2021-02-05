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
     * @param bool $includeKeySchema
     * @return array<string, mixed>
     */
    public function getArray(bool $includeKeySchema = true): array
    {
        $schemaSkeleton = [
            'TableName' => $this->tableName,
            'ProvisionedThroughput' => [
                'ReadCapacityUnits' => $this->readCapacityUnits,
                'WriteCapacityUnits' => $this->writeCapacityUnits,
            ],
            'AttributeDefinitions' => [],
        ];

        if ($includeKeySchema === true) {
            $schemaSkeleton['KeySchema'] = [];
        }

        return $schemaSkeleton;
    }

}
