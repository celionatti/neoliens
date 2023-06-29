<?php

declare(strict_types = 1);

namespace Neoliens\Core\Contracts;

interface OwnableInterface
{
    public function getUser(): UserInterface;
}
