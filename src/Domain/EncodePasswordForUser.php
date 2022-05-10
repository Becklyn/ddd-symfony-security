<?php

namespace Becklyn\Security\Domain;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-23
 */
interface EncodePasswordForUser
{
    public function execute(User $user, string $newPlainPassword): string;
}
