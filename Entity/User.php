<?php

declare(strict_types=1);

namespace Neoliens\Entity;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Neoliens\Entity\Traits\HasTimestamps;
use Neoliens\Core\Contracts\UserInterface;
use Neoliens\Core\Contracts\OwnableInterface;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

#[Entity, Table('users')]
#[HasLifecycleCallbacks]
class User implements UserInterface
{
    use HasTimestamps;

    #[Id, Column(options: ['unsigned' => true]), GeneratedValue]
    private int $id;

    #[Column]
    private string $name;

    #[Column]
    private string $email;

    #[Column]
    private string $password;

    #[Column(name: 'two_factor', options: ['default' => false])]
    private bool $twoFactor;

    #[Column(name: 'verified_at', nullable: true)]
    private ?\DateTime $verifiedAt;

    public function __construct()
    {
        $this->twoFactor    = false;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name): User
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
 
    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    public function canManage(OwnableInterface $entity): bool
    {
        return $this->getId() === $entity->getUser()->getId();
    }

    public function getVerifiedAt(): ?\DateTime
    {
        return $this->verifiedAt;
    }

    public function setVerifiedAt(\DateTime $verifiedAt): static
    {
        $this->verifiedAt = $verifiedAt;

        return $this;
    }

    public function hasTwoFactorAuthEnabled(): bool
    {
        return $this->twoFactor;
    }

    public function setTwoFactor(bool $twoFactor): User
    {
        $this->twoFactor = $twoFactor;

        return $this;
    }
}