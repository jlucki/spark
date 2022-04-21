<?php

namespace JLucki\ODM\Spark\Tests\Validation;

use JLucki\ODM\Spark\Validation\OpenAttributeValidation;
use PHPUnit\Framework\TestCase;

class OpenAttributeValidationTest extends TestCase
{

    public function testOpenAttributeValidationIsValidIfAllTrue(): void
    {
        $validator = new OpenAttributeValidation();
        $validator->passOpenAttributeCheck(true);
        $validator->passAttributeNameCheck(true);
        $validator->passAttributeTypeCheck(true);
        $validator->passKeyTypeCheck(true);
        $validator->passGlobalSecondaryIndex(true);
        $this->assertTrue($validator->isValid());
    }

    public function testOpenAttributeValidationIsNotValidIfNotAllTrue(): void
    {
        $validator = new OpenAttributeValidation();
        $validator->passOpenAttributeCheck(false);
        $validator->passAttributeNameCheck(true);
        $validator->passAttributeTypeCheck(true);
        $validator->passKeyTypeCheck(true);
        $validator->passGlobalSecondaryIndex(true);
        $this->assertFalse($validator->isValid());
    }

}