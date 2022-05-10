<?php

namespace Becklyn\Security\Infrastructure\Domain\Doctrine;

use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserFactory;
use Becklyn\Security\Domain\UserId;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-30
 */
class DoctrineSymfonyUserFactory implements UserFactory
{
    public function create(UserId $id, string $email, string $password): User
    {
        return DoctrineSymfonyUser::create($id, $email, $password);
    }
}
