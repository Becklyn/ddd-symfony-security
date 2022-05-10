<?php

namespace Becklyn\Security\Application;

use Becklyn\Security\Domain\User;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-03-06
 */
interface Security
{
    public function getUser(): ?User;
}
