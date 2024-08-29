<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

readonly class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(private UserRepository $repository)
    {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return User::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->repository->findOneBy(['email' => $identifier, 'isBanned' => false]);
    }

    public function upgradePassword(
        PasswordAuthenticatedUserInterface $user,
        string $newHashedPassword,
    ): void {
        $this->repository->upgradePassword($user, $newHashedPassword);
    }
}