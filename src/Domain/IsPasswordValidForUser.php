<?php declare(strict_types=1);

namespace Becklyn\Security\Domain;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-24
 */
interface IsPasswordValidForUser
{
    public function execute(User $user, string $plainPassword) : bool;
}
