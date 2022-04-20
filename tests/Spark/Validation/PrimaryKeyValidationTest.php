<?php

namespace JLucki\ODM\Spark\Tests\Validation;

use JLucki\ODM\Spark\Validation\PrimaryKeyValidation;
use PHPUnit\Framework\TestCase;

class PrimaryKeyValidationTest extends TestCase
{

    public function testOpenAttributeValidationIsValidIfAllTrue(): void
    {
        $validator = new PrimaryKeyValidation();
        $validator->passKeyTypeCheck(true);
        $validator->passAttributeNameCheck(true);
        $validator->passAttributeTypeCheck(true);
        $validator->passGlobalSecondaryIndex(true);
        $this->assertTrue($validator->isValid());
    }

    public function testOpenAttributeValidationIsNotValidIfNotAllTrue(): void
    {
        $validator = new PrimaryKeyValidation();
        $validator->passKeyTypeCheck(true);
        $validator->passAttributeNameCheck(true);
        $validator->passAttributeTypeCheck(true);
        $validator->passGlobalSecondaryIndex(false);
        $this->assertFalse($validator->isValid());
    }

}