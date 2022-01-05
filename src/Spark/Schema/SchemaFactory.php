<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Schema;

use JLucki\ODM\Spark\Attribute\AttributeName;
use JLucki\ODM\Spark\Attribute\AttributeType;
use JLucki\ODM\Spark\Attribute\GlobalSecondaryIndex;
use JLucki\ODM\Spark\Attribute\KeyType;
use JLucki\ODM\Spark\Attribute\NonKeyAttributes;
use JLucki\ODM\Spark\Attribute\OnDemand;
use JLucki\ODM\Spark\Attribute\ProjectionType;
use JLucki\ODM\Spark\Attribute\ProjectionType as ProjectionTypeAttribute;
use JLucki\ODM\Spark\Attribute\ReadCapacityUnits;
use JLucki\ODM\Spark\Attribute\TableName;
use JLucki\ODM\Spark\Attribute\WriteCapacityUnits;
use JLucki\ODM\Spark\Constant\Defaults;
use JLucki\ODM\Spark\Interface\ItemInterface;
use JLucki\ODM\Spark\Validation\Validator;
use ReflectionAttribute;
use ReflectionClass;

/**
 * The SchemaFactory renders a schema array for the table formatted as per DynamoDB
 * requirements as outlined in the PHP SDK:
 *
 * https://docs.aws.amazon.com/aws-sdk-php/v2/api/class-Aws.DynamoDb.DynamoDbClient.html#_createTable
 *
 * Class SchemaFactory
 * @package JLucki\ODM\Spark\Schema
 */
class SchemaFactory
{

    private string $tableName;

    private int $writeCapacityUnits;

    private int $readCapacityUnits;

    private bool $onDemand;

    /** @var array<string, array> */
    private array $secondaryIndexAttributeSchemas = [];

    /** @var array<string, mixed> */
    private array $schema;

    /** @var ReflectionClass<ItemInterface> */
    private ReflectionClass $reflectionClass;

    private Validator $validator;

    public function __construct(
        private ItemInterface $item,
    ) {
        $this->validator = new Validator();
        $this->reflectionClass = new ReflectionClass($this->item);
        $this->setRequiredDefaults();
        $this->renderSchema();
    }

    /**
     * @return array<string, mixed>
     */
    public function getSchema(): array
    {
        return $this->schema;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    private function renderSchema(): void
    {
        $this->setTableAttributes();

        $this->setSchemaSkeleton();

        $this->setSchemaProperties();
    }

    /**
     * Sets the basic required table attributes as defined by the ItemInterface
     * TableName, ReadCapacityUnits, WriteCapacityUnits
     */
    private function setTableAttributes(): void
    {
        $classAttributes = $this->reflectionClass->getAttributes();

        if (count($classAttributes) > 0) {
            foreach ($classAttributes as $classAttribute) {
                $attributeName = $classAttribute->getName();

                if ($attributeName === TableName::class) {
                    $this->tableName = $this->getFirstArgumentValue($classAttribute);
                }

                if ($attributeName === ReadCapacityUnits::class) {
                    $this->readCapacityUnits = $this->getFirstArgumentValue($classAttribute);
                }

                if ($attributeName === WriteCapacityUnits::class) {
                    $this->writeCapacityUnits = $this->getFirstArgumentValue($classAttribute);
                }

                if ($attributeName === OnDemand::class) {
                    $this->onDemand = $this->getFirstArgumentValue($classAttribute);
                }
            }
        }
    }

    /**
     * Sets default values for any of the missing required table attributes
     * TableName set to the ReflectionClass short name (e.g.: Article)
     * ReadCapacityUnits, WriteCapacityUnits set to the defaults from JLucki\ODM\Spark\Constant\Defaults
     */
    private function setRequiredDefaults(): void
    {
        $this->tableName = $this->reflectionClass->getShortName();
        $this->readCapacityUnits = Defaults::DEFAULT_READ_CAPACITY_UNITS;
        $this->writeCapacityUnits = Defaults::DEFAULT_WRITE_CAPACITY_UNITS;
        $this->onDemand = Defaults::ON_DEMAND;
    }

    private function setSchemaSkeleton(): void
    {
        $schemaSkeleton = new Skeleton(
            $this->tableName,
            $this->readCapacityUnits,
            $this->writeCapacityUnits,
            $this->onDemand,
        );
        $this->schema = $schemaSkeleton->getArray();
    }

    private function setSchemaProperties(): void
    {
        $properties = $this->reflectionClass->getProperties();

        foreach ($properties as $property) {
            if ($this->validator->isPrimaryKey($property) === true) {
                $this->setSchemaAttributeDefinition($property->getAttributes());
            }
            if ($this->validator->isGlobalSecondaryIndex($property) === true) {
                $this->resolveGlobalSecondaryIndex($property->getAttributes());
            }
        }

        $this->setGlobalSecondaryIndexes();
    }

    /**
     * @param ReflectionAttribute $reflectionAttribute
     * @return mixed
     */
    private function getFirstArgumentValue(ReflectionAttribute $reflectionAttribute): mixed
    {
        // Attributes with default values will require a
        // new instance to retrieve the property value
        switch ($reflectionAttribute->getName()) {
            case OnDemand::class:
                /** @var OnDemand $instance */
                $instance = $reflectionAttribute->newInstance();
                return $instance->isOnDemand();
            case ProjectionType::class:
                /** @var ProjectionType $instance */
                $instance = $reflectionAttribute->newInstance();
                return $instance->getType();
            default:
                $arguments = $reflectionAttribute->getArguments();
                return reset($arguments);
        }
    }

    /**
     * @param array<ReflectionAttribute> $reflectionAttributes
     */
    private function setSchemaAttributeDefinition(array $reflectionAttributes): void
    {
        $attributeDefinition = [];
        foreach ($reflectionAttributes as $reflectionAttribute) {
            $qualifiedName = $reflectionAttribute->getName();
            $argumentValue = $this->getFirstArgumentValue($reflectionAttribute);
            switch ($qualifiedName) {
                case KeyType::class:
                case AttributeName::class:
                case AttributeType::class:
                    $attributeDefinition[$qualifiedName] = $argumentValue;
            }
        }

        $this->schema['AttributeDefinitions'][] = (new AttributeDefinition($attributeDefinition))->getArray();

        if (isset($attributeDefinition[KeyType::class]) === true) {
            $this->schema['KeySchema'][] = (new KeySchema($attributeDefinition))->getArray();
        }
    }

    /**
     * @param array<ReflectionAttribute> $reflectionAttributes
     */
    private function resolveGlobalSecondaryIndex(array $reflectionAttributes): void
    {
        $attributeDefinition = [];
        $readCapacityUnits = Defaults::DEFAULT_READ_CAPACITY_UNITS;
        $writeCapacityUnits = Defaults::DEFAULT_WRITE_CAPACITY_UNITS;
        $indexName = null;
        foreach ($reflectionAttributes as $reflectionAttribute) {
            $qualifiedName = $reflectionAttribute->getName();
            $argumentValue = $this->getFirstArgumentValue($reflectionAttribute);
            switch ($qualifiedName) {
                case KeyType::class:
                case AttributeName::class:
                case AttributeType::class:
                case ProjectionTypeAttribute::class:
                case NonKeyAttributes::class:
                // case OnDemand::class:
                    $attributeDefinition[$qualifiedName] = $argumentValue;
                    break;
                case GlobalSecondaryIndex::class:
                    if ($argumentValue !== false) {
                        $indexName = $argumentValue;
                    }
                    break;
                case ReadCapacityUnits::class:
                    $readCapacityUnits = $argumentValue;
                    break;
                case WriteCapacityUnits::class:
                    $writeCapacityUnits = $argumentValue;
                    break;
            }
        }

        // Fallback to attribute name if the index name isn't set
        $indexName ??= $attributeDefinition[AttributeName::class];

        $this->schema['AttributeDefinitions'][] = (new AttributeDefinition($attributeDefinition))->getArray();

        $provisionedThroughput = [
            'ReadCapacityUnits' => $readCapacityUnits,
            'WriteCapacityUnits' => $writeCapacityUnits,
            // 'OnDemand' => $attributeDefinition[OnDemand::class] ?? false,
        ];

        $this->secondaryIndexAttributeSchemas[$indexName][] = [
            'IndexName' => $indexName,
            'KeySchema' => [
                (new KeySchema($attributeDefinition))->getArray(),
            ],
            'Projection' => (new Projection($attributeDefinition))->getArray(),
            'ProvisionedThroughput' => $provisionedThroughput,
        ];
    }

    private function setGlobalSecondaryIndexes(): void
    {
        foreach ($this->secondaryIndexAttributeSchemas as $secondaryIndexAttributeSchema) {
            if ($this->validator->isValidSecondaryIndexAttributeSchema($secondaryIndexAttributeSchema) === true) {
                if (count($secondaryIndexAttributeSchema) > 1) {
                    $this->schema['GlobalSecondaryIndexes'][] = $this->mergeSecondaryIndexSchemas($secondaryIndexAttributeSchema);
                } else {
                    $this->schema['GlobalSecondaryIndexes'][] = reset($secondaryIndexAttributeSchema);
                }
            }
        }
    }

    /**
     * @param array $secondaryIndexAttributeSchema
     * @return array
     */
    private function mergeSecondaryIndexSchemas(array $secondaryIndexAttributeSchema): array
    {
        $mergedSchema = [];
        foreach ($secondaryIndexAttributeSchema as $secondaryIndexAttributeSchemaValue) {
            $mergedSchema['IndexName'] ??= $secondaryIndexAttributeSchemaValue['IndexName'];
            $mergedSchema['KeySchema'][] = $secondaryIndexAttributeSchemaValue['KeySchema'];
            $mergedSchema['Projection'] ??= $secondaryIndexAttributeSchemaValue['Projection'];
            $mergedSchema['ProvisionedThroughput'] ??= $secondaryIndexAttributeSchemaValue['ProvisionedThroughput'];
        }
        return $mergedSchema;
    }

}
