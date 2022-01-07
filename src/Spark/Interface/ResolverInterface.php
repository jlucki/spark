<?php

declare(strict_types=1);

namespace JLucki\ODM\Spark\Interface;

interface ResolverInterface
{
    public function resolve(): self;
}
