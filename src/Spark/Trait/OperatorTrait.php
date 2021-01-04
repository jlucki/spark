<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Trait;

use Aws\DynamoDb\Marshaler;
use JLucki\ODM\Spark\Attribute\AttributeName;
use JLucki\ODM\Spark\Attribute\OpenAttribute;
use JLucki\ODM\Spark\Interface\ItemInterface;
use DateTime;
use ReflectionClass;
use ReflectionNamedType;

trait OperatorTrait
{

    /** @var array<string, ItemInterface> */
    private array $items = [];

    /**
     * @param string $itemClass
     * @return ItemInterface
     */
    protected function getItemObject(string $itemClass): ItemInterface
    {
        if (array_key_exists($itemClass, $this->items) === true) {
            return $this->items[$itemClass];
        }

        $itemObject = new $itemClass();

        $this->items[$itemClass] = $itemObject;

        return $itemObject;
    }

    /**
     * @param string $itemClass
     * @param array<string, mixed> $objectData
     * @return ItemInterface
     */
    protected function makeModel(string $itemClass, array $objectData): ItemInterface
    {
        $itemObject = new $itemClass();
        return $this->hydrateObject($itemObject, $objectData);
    }

    /**
     * @param ItemInterface $itemObject
     * @param array<string, mixed> $objectData
     * @return ItemInterface
     */
    private function hydrateObject(ItemInterface $itemObject, array $objectData): ItemInterface
    {
        $reflectionClass = new ReflectionClass($itemObject);
        $properties = $reflectionClass->getProperties();
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $dataKey = $property->getName();
            $reflectionAttributes = $property->getAttributes();
            if (count($reflectionAttributes) > 0) {
                foreach ($reflectionAttributes as $reflectionAttribute) {
                    $qualifiedName = $reflectionAttribute->getName();
                    $arguments = $reflectionAttribute->getArguments();
                    $argumentValue = reset($arguments);
                    if ($qualifiedName === AttributeName::class || $qualifiedName === OpenAttribute::class) {
                        $dataKey = $argumentValue;
                        break;
                    }
                }
            }
            if (isset($objectData[$propertyName]) === true) {
                $data = $objectData[$dataKey];
                $setter = 'set' . ucfirst($propertyName);
                /** @var ReflectionNamedType $type */
                $type = $property->getType();
                if ($type !== null) {
                    $typeName = $type->getName();
                    $data = match ($typeName) {
                        'DateTime' => (new DateTime())->setTimestamp($data),
                        default => $data,
                    };
                    if (method_exists($itemObject, $setter)) {
                        $itemObject->$setter($data);
                    }
                    unset($objectData[$propertyName]);
                }
            }
        }
        return $itemObject;
    }

    /**
     * @param string $itemClass
     * @return array<string, mixed>
     */
    protected function getSchema(string $itemClass): array
    {
        $item = $this->getItemObject($itemClass);
        return $item->getSchema();
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    private function makeKey(array $values): array
    {
        return (new Marshaler())->marshalItem($values);
    }

}
