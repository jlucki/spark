<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Schema\Helper;

class ArrayHelper
{
    /**
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function arrayHasKeyValue(array $array, string $key, mixed $value): bool
    {
        foreach ($array as $item) {
            if (isset($item[$key]) === true && $item[$key] === $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array|null
     */
    public static function getSubArrayByKeyValue(array $array, string $key, mixed $value): ?array
    {
        foreach ($array as $item) {
            if (isset($item[$key]) === true && $item[$key] === $value) {
                return $item;
            }
        }
        return null;
    }

    /**
     * @param array $first
     * @param array $second
     * @return array
     */
    public static function getArrayDiff(array $first, array $second): array
    {
        return array_udiff_assoc($first, $second, function($a, $b) {
            return intval($a !== $b);
        });
    }
}
