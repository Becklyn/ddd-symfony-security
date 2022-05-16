<?php declare(strict_types=1);

namespace Becklyn\Security\Domain;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-30
 */
interface UserFactory
{
    public function create(UserId $id, string $email, string $password) : User;
}
