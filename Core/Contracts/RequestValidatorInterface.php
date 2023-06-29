<?php

declare(strict_types = 1);

namespace Neoliens\Core\Contracts;

interface RequestValidatorInterface
{
    public function validate(array $data): array;
}
