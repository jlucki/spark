<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Schema;

use JLucki\ODM\Spark\Attribute\NonKeyAttributes;
use JLucki\ODM\Spark\Attribute\ProjectionType;

class Projection
{
    /**
     * @param array<string, string> $attributeDefinition
     */
    public function __construct(
        private array $attributeDefinition,
    ) {}

    /**
     * @return array<string, string>
     */
    public function getArray(): array
    {
        if (isset($this->attributeDefinition[ProjectionType::class]) === false) {
            return [
                'ProjectionType' => ProjectionType::KEYS_ONLY,
            ];
        }

        $projection['ProjectionType'] = $this->attributeDefinition[ProjectionType::class];

        if (isset($this->attributeDefinition[NonKeyAttributes::class]) === true) {
            $projection['NonKeyAttributes'] = $this->attributeDefinition[NonKeyAttributes::class];
        }

        return $projection;
    }
}
