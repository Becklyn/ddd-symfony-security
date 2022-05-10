<?php

namespace Becklyn\Security\Domain;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-27
 */
interface NotifyPasswordReset
{
    public function execute(User $user, string $passwordResetToken): void;
}
