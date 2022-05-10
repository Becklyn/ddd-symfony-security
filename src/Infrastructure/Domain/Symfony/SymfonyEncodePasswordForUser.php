<?php

namespace Becklyn\Security\Infrastructure\Domain\Symfony;

use Becklyn\Security\Domain\EncodePasswordForUser;
use Becklyn\Security\Domain\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-23
 */
class SymfonyEncodePasswordForUser implements EncodePasswordForUser
{
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param SymfonyUser $user
     */
    public function execute(User $user, string $newPlainPassword): string
    {
        return $this->encoder->encodePassword($user, $newPlainPassword);
    }
}
