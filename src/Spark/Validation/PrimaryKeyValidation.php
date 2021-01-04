<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Validation;

class PrimaryKeyValidation
{

    /** @var array<string, bool> */
    private array $rules = [
        'passKeyTypeCheck' => false,
        'passAttributeNameCheck' => false,
        'passAttributeTypeCheck' => false,
        'passGlobalSecondaryIndex' => true,
    ];

    public function passKeyTypeCheck(bool $pass): self
    {
        $this->rules['passKeyTypeCheck'] = $pass;
        return $this;
    }

    public function passAttributeNameCheck(bool $pass): self
    {
        $this->rules['passAttributeNameCheck'] = $pass;
        return $this;
    }

    public function passAttributeTypeCheck(bool $pass): self
    {
        $this->rules['passAttributeTypeCheck'] = $pass;
        return $this;
    }

    public function passGlobalSecondaryIndex(bool $pass): self
    {
        $this->rules['passGlobalSecondaryIndex'] = $pass;
        return $this;
    }

    public function isValid(): bool
    {
        if (in_array(false, $this->rules, true) === false) {
            return true;
        }
        return false;
    }

}
