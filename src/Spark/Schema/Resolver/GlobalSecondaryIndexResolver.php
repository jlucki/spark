<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Schema\Resolver;

use JLucki\ODM\Spark\Interface\ResolverInterface;
use JLucki\ODM\Spark\Schema\Helper\ArrayHelper;
use function count;

class GlobalSecondaryIndexResolver implements ResolverInterface
{
    private array $localSchema;

    private array $describedSchema;

    private array $newGlobalSecondaryIndexes;

    private array $deletedGlobalSecondaryIndexes;

    private array $updatedGlobalSecondaryIndexes;

    public function resolve(): self
    {
        $this->resolveNewIndexes();
        $this->resolveDeletedIndexes();
        $this->resolveUpdatedIndexes();
        return $this;
    }

    public function resolveNewIndexes(): void
    {
        $this->newGlobalSecondaryIndexes = $this->getIndexesDiff($this->localSchema, $this->describedSchema);
    }

    public function resolveDeletedIndexes(): void
    {
        $this->deletedGlobalSecondaryIndexes = $this->getIndexesDiff($this->describedSchema, $this->localSchema);
    }

    public function resolveUpdatedIndexes(): void
    {
        $updatedIndexes = [];

        $localGlobalSecondaryIndexes = $this->localSchema['GlobalSecondaryIndexes'];
        $describedGlobalSecondaryIndexes = $this->describedSchema['GlobalSecondaryIndexes'];

        foreach ($localGlobalSecondaryIndexes as $localGlobalSecondaryIndex) {
            // we're only interested in indexes that exist on both the described
            // and object schemas to determine if the index was modified
            $isNew = ArrayHelper::arrayHasKeyValue($this->newGlobalSecondaryIndexes, 'IndexName', $localGlobalSecondaryIndex['IndexName']);
            $isDeleted = ArrayHelper::arrayHasKeyValue($this->deletedGlobalSecondaryIndexes, 'IndexName', $localGlobalSecondaryIndex['IndexName']);
            if ($isNew === true || $isDeleted === true) {
                continue;
            }
            $describedGlobalSecondaryIndex = ArrayHelper::getSubArrayByKeyValue($describedGlobalSecondaryIndexes, 'IndexName', $localGlobalSecondaryIndex['IndexName']);
            $diff = ArrayHelper::getArrayDiff([$localGlobalSecondaryIndex], [$describedGlobalSecondaryIndex]);
            if (count($diff) > 0) {
                $updatedIndexes[] = $diff[0];
            }
        }

        $this->updatedGlobalSecondaryIndexes = $this->removeUnchangedParameters($updatedIndexes);
    }

    private function removeUnchangedParameters(array $updatedIndexes): array
    {
        foreach ($updatedIndexes as &$updatedIndex) {
            $originalIndex = ArrayHelper::getSubArrayByKeyValue($this->describedSchema['GlobalSecondaryIndexes'], 'IndexName', $updatedIndex['IndexName']);
            $diff = ArrayHelper::getArrayDiff($updatedIndex['ProvisionedThroughput'], $originalIndex['ProvisionedThroughput']);
            if (count($diff) === 0) {
                unset($updatedIndex['ProvisionedThroughput']);
            }
        }
        return $updatedIndexes;
    }

    /**
     * Returns indexes that exist in $schemaOne, but not in $schemaTwo
     *
     * @param array $schemaOne
     * @param array $schemaTwo
     * @return array
     */
    private function getIndexesDiff(array $schemaOne, array $schemaTwo): array
    {
        $indexes = [];
        foreach ($schemaOne['GlobalSecondaryIndexes'] as $schemaOneGlobalSecondaryIndex) {
            $existsInOneOnly = true;
            foreach ($schemaTwo['GlobalSecondaryIndexes'] as $schemaTwoGlobalSecondaryIndex) {
                if ($schemaOneGlobalSecondaryIndex['IndexName'] === $schemaTwoGlobalSecondaryIndex['IndexName']) {
                    $existsInOneOnly = false;
                    break;
                }
            }
            if ($existsInOneOnly === true) {
                $indexes[] = $schemaOneGlobalSecondaryIndex;
            }
        }
        return $indexes;
    }

    public function setLocalSchema(array $localSchema): self
    {
        $this->localSchema = $localSchema;
        return $this;
    }

    public function setDescribedSchema(array $describedSchema): self
    {
        $this->describedSchema = $describedSchema;
        return $this;
    }

    public function getNewGlobalSecondaryIndexes(): array
    {
        return $this->newGlobalSecondaryIndexes;
    }

    public function getDeletedGlobalSecondaryIndexes(): array
    {
        return $this->deletedGlobalSecondaryIndexes;
    }

    public function getUpdatedGlobalSecondaryIndexes(): array
    {
        return $this->updatedGlobalSecondaryIndexes;
    }
}
