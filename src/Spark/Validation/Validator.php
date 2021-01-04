<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Validation;

use JLucki\ODM\Spark\Attribute\AttributeName;
use JLucki\ODM\Spark\Attribute\AttributeType;
use JLucki\ODM\Spark\Attribute\GlobalSecondaryIndex;
use JLucki\ODM\Spark\Attribute\KeyType;
use JLucki\ODM\Spark\Attribute\OpenAttribute;
use JLucki\ODM\Spark\Attribute\ReadCapacityUnits;
use JLucki\ODM\Spark\Attribute\WriteCapacityUnits;
use ReflectionProperty;

class Validator
{

    public function isPrimaryKey(ReflectionProperty $property): bool
    {
        $validator = new PrimaryKeyValidation();
        $reflectionAttributes = $property->getAttributes();
        foreach ($reflectionAttributes as $reflectionAttribute) {
            $qualifiedName = $reflectionAttribute->getName();
            switch ($qualifiedName) {
                case KeyType::class:
                    $validator->passKeyTypeCheck(true);
                    break;
                case AttributeName::class:
                    $validator->passAttributeNameCheck(true);
                    break;
                case AttributeType::class:
                    $validator->passAttributeTypeCheck(true);
                    break;
                case GlobalSecondaryIndex::class:
                case ReadCapacityUnits::class:
                case WriteCapacityUnits::class:
                    $validator->passGlobalSecondaryIndex(false);
                    break;
            }
        }
        return $validator->isValid();
    }

    public function isGlobalSecondaryIndex(ReflectionProperty $property): bool
    {
        $validator = new GlobalSecondaryIndexValidation();
        $reflectionAttributes = $property->getAttributes();
        foreach ($reflectionAttributes as $reflectionAttribute) {
            $qualifiedName = $reflectionAttribute->getName();
            switch ($qualifiedName) {
                case KeyType::class:
                    $validator->passKeyTypeCheck(true);
                    break;
                case AttributeName::class:
                    $validator->passAttributeNameCheck(true);
                    break;
                case AttributeType::class:
                    $validator->passAttributeTypeCheck(true);
                    break;
                case GlobalSecondaryIndex::class:
                    $validator->passGlobalSecondaryIndex(true);
                    break;
            }
        }
        return $validator->isValid();
    }

    public function isOpenAttribute(ReflectionProperty $property): bool
    {
        $validator = new OpenAttributeValidation();
        $reflectionAttributes = $property->getAttributes();
        foreach ($reflectionAttributes as $reflectionAttribute) {
            $qualifiedName = $reflectionAttribute->getName();
            switch ($qualifiedName) {
                case OpenAttribute::class:
                    $validator->passOpenAttributeCheck(true);
                    break;
                case AttributeName::class:
                    $validator->passAttributeNameCheck(false);
                    break;
                case AttributeType::class:
                    $validator->passAttributeTypeCheck(false);
                    break;
                case KeyType::class:
                    $validator->passKeyTypeCheck(false);
                    break;
                case GlobalSecondaryIndex::class:
                case ReadCapacityUnits::class:
                case WriteCapacityUnits::class:
                    $validator->passGlobalSecondaryIndex(false);
                    break;
            }
        }
        return $validator->isValid();
    }

}
