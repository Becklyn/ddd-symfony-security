<?php

namespace Becklyn\Security\Domain;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-24
 */
interface IsPasswordValidForUser
{
    public function execute(User $user, string $plainPassword): bool;
}
