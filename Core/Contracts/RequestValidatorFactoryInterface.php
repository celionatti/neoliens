<?php

declare(strict_types = 1);

namespace Neoliens\Core\Contracts;

interface RequestValidatorFactoryInterface
{
    public function make(string $class): RequestValidatorInterface;
}
