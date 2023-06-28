<?php

declare(strict_types = 1);

namespace Neoliens\Core\Enum;

enum AuthAttemptStatus
{
    case FAILED;
    case TWO_FACTOR_AUTH;
    case SUCCESS;
}
