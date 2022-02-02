<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Model\Base;

use DateTime;
use JLucki\ODM\Spark\Attribute\AttributeName;
use JLucki\ODM\Spark\Attribute\OpenAttribute;
use JLucki\ODM\Spark\Interface\ItemInterface;
use Aws\DynamoDb\Marshaler;
use JLucki\ODM\Spark\Schema\Factory\SchemaFactory;
use JLucki\ODM\Spark\Validation\Validator;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

class Item implements ItemInterface
{

    /** @var array<string, mixed> */
    private array $schema;

    private string $tableName;

    public function __construct()
    {
        $schemaFactory = new SchemaFactory($this);
        $this->schema = $schemaFactory->getSchema();
        $this->tableName = $schemaFactory->getTableName();
    }

    /**
     * @return array<string, mixed>
     */
    public function getSchema(): array
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @param bool $includeKeys
     * @return array<string, mixed>
     */
    public function toArray(bool $includeKeys = true): array
    {
        $validAttributes[] = OpenAttribute::class;
        if ($includeKeys === true) {
            $validAttributes[] = AttributeName::class;
        }

        $itemAsArray = [];
        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $reflectionAttributes = $property->getAttributes();
            foreach ($reflectionAttributes as $reflectionAttribute) {
                $qualifiedName = $reflectionAttribute->getName();
                if (in_array($qualifiedName, $validAttributes) === true) {
                    $value = $this->getPropertyValue($property);
                    $itemAsArray[$property->getName()] = $value;
                }
            }
        }
        return $itemAsArray;
    }

    /**
     * Use this to get the item's primary key schema
     *
     * @return array<string, mixed>
     */
    public function getKey(): array
    {
        $key = [];
        $validator = new Validator();
        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties();
        foreach ($properties as $property) {
            if ($validator->isPrimaryKey($property) === true) {
                $value = $this->getPropertyValue($property);
                $key[$property->getName()] = $value;
            }
        }
        $marshaler = new Marshaler();
        return $marshaler->marshalItem($key);
    }

    /**
     * @param ReflectionProperty $property
     * @return int|float|string|bool|null
     */
    private function getPropertyValue(ReflectionProperty $property): int|float|string|bool|null
    {
        /** @var ReflectionNamedType $type */
        $type = $property->getType();
        if ($type !== null) {
            $getter = 'get' . ucfirst($property->getName());
            $typeName = $type->getName();
            if ($typeName === 'DateTime') {
                if ($this->$getter() instanceof DateTime) {
                    return $this->$getter()->getTimestamp();
                }
            } else {
                if ($typeName === 'bool') {
                    $getter = 'is' . ucfirst($property->getName());
                }
                return $this->$getter();
            }
        }
        return null;
    }

}
