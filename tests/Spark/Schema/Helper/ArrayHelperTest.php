<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Tests\Schema\Helper;

use JLucki\ODM\Spark\Schema\Helper\ArrayHelper;
use PHPUnit\Framework\TestCase;

class ArrayHelperTest extends TestCase
{

    public function testArrayHasKeyValue(): void
    {
        $array = [
            [
                'key' => 'value',
            ],
        ];
        $this->assertTrue(ArrayHelper::arrayHasKeyValue($array, 'key', 'value'));
    }

    public function testArrayHasKeyValueNotFound(): void
    {
        $array = [
            [
                'key' => 'value',
            ],
        ];
        $this->assertFalse(ArrayHelper::arrayHasKeyValue($array, 'key', 'value2'));
    }

    public function testGetSubArrayByKeyValue(): void
    {
        $array = [
            [
                'key' => 'value',
            ]
        ];
        $this->assertEquals(['key' => 'value'], ArrayHelper::getSubArrayByKeyValue($array, 'key', 'value'));
    }

    public function testGetSubArrayByKeyValueNotFound(): void
    {
        $array = [
            'key' => 'value',
        ];
        $this->assertEquals(null, ArrayHelper::getSubArrayByKeyValue($array, 'key2', 'value2'));
    }

    public function testGetArrayDiff(): void
    {
        $array1 = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $array2 = [
            'key1' => 'value1',
            'key3' => 'value3',
        ];
        $this->assertEquals(['key2' => 'value2'], ArrayHelper::getArrayDiff($array1, $array2));
    }

    public function testGetArrayDiffEmpty(): void
    {
        $array1 = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $array2 = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];
        $this->assertEquals([], ArrayHelper::getArrayDiff($array1, $array2));
    }

}