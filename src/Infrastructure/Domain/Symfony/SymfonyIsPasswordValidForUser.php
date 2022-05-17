<?php declare(strict_types=1);

namespace Becklyn\Security\Infrastructure\Domain\Symfony;

use Becklyn\Security\Domain\IsPasswordValidForUser;
use Becklyn\Security\Domain\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-24
 */
class SymfonyIsPasswordValidForUser implements IsPasswordValidForUser
{
    public function __construct(private readonly UserPasswordHasherInterface $encoder)
    {
    }

    /**
     * @param SymfonyUser $user
     */
    public function execute(User $user, string $plainPassword) : bool
    {
        return $this->encoder->isPasswordValid($user, $plainPassword);
    }
}
