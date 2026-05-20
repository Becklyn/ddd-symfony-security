<?php declare(strict_types=1);

namespace Becklyn\Security\Infrastructure\Application\Symfony;

use Becklyn\Security\Application\Security;
use Becklyn\Security\Infrastructure\Domain\Symfony\SymfonyUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SymfonySecurity implements Security
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage)
    {
    }

    public function getUser() : ?SymfonyUser
    {
        /** @var TokenInterface|null $token */
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();
        return $user instanceof SymfonyUser ? $user : null;
    }
}
