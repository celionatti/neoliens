<?php

declare(strict_types = 1);

namespace Neoliens\Core\Contracts;

use Neoliens\Core\Enum\AuthAttemptStatus;
use Neoliens\Core\Contracts\UserInterface;
use Neoliens\Core\DataObjects\RegisterUserData;


interface AuthInterface
{
    public function user(): ?UserInterface;

    public function attemptLogin(array $credentials): AuthAttemptStatus;

    public function checkCredentials(UserInterface $user, array $credentials): bool;

    public function logOut(): void;

    public function register(RegisterUserData $data): UserInterface;

    public function logIn(UserInterface $user): void;

    public function attemptTwoFactorLogin(array $data): bool;
}
