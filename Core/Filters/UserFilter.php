<?php

declare(strict_types = 1);

namespace Neoliens\Core\Filters;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Neoliens\Core\Contracts\OwnableInterface;

class UserFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if (! $targetEntity->getReflectionClass()->implementsInterface(OwnableInterface::class)) {
            return '';
        }

        return $targetTableAlias . '.user_id = ' . $this->getParameter('user_id');
    }
}
