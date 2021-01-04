<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Query;

class Expression
{

    private string $attribute;

    private string $comparison = '=';

    private string|int|float|bool $value;

    public function attribute(string $attribute): self
    {
        $this->attribute = $attribute;
        return $this;
    }

    public function comparison(string $comparison): self
    {
        $this->comparison = $comparison;
        return $this;
    }

    public function value(string|int|float|bool $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getAttribute(): string
    {
        return $this->attribute;
    }

    public function getComparison(): string
    {
        return $this->comparison;
    }

    public function getValue(): string|int|float|bool
    {
        return $this->value;
    }

}
