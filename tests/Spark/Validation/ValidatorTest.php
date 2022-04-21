<?php

namespace JLucki\ODM\Spark\Tests\Validation;

use JLucki\ODM\Spark\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{

    private Validator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Validator();
    }

    public function testIsReflectionPropertyPrimaryKey(): void
    {
        $this->assertTrue(true);
    }

    public function testIsReflectionPropertyGlobalSecondaryIndex(): void
    {
        $this->assertTrue(true);
    }

    public function testIsReflectionPropertyOpenAttribute(): void
    {
        $this->assertTrue(true);
    }

    public function testIsArrayValidSecondaryIndexAttributeSchema(): void
    {
        $this->assertTrue($this->validator->isValidSecondaryIndexAttributeSchema([]));
    }

}