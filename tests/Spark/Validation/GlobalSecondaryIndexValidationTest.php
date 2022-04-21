<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Tests\Validation;

use JLucki\ODM\Spark\Validation\GlobalSecondaryIndexValidation;
use PHPUnit\Framework\TestCase;

class GlobalSecondaryIndexValidationTest extends TestCase
{

    public function testGlobalSecondaryIndexValidationIsValidIfAllTrue(): void
    {
        $validator = new GlobalSecondaryIndexValidation();
        $validator->passKeyTypeCheck(true);
        $validator->passAttributeNameCheck(true);
        $validator->passAttributeTypeCheck(true);
        $validator->passGlobalSecondaryIndex(true);
        $this->assertTrue($validator->isValid());
    }

    public function testGlobalSecondaryIndexValidationIsNotValidIfNotAllTrue(): void
    {
        $validator = new GlobalSecondaryIndexValidation();
        $validator->passKeyTypeCheck(true);
        $validator->passAttributeNameCheck(true);
        $validator->passAttributeTypeCheck(true);
        $validator->passGlobalSecondaryIndex(false);
        $this->assertFalse($validator->isValid());
    }

}