<?php

namespace Becklyn\Security\Infrastructure\Domain\Symfony;

use Becklyn\Security\Domain\User;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-02
 */
interface SymfonyUser extends User, UserInterface
{
}
