<?php declare(strict_types=1);

namespace Becklyn\Security\Domain;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-20
 */
abstract class Role
{
    final public const USER = 'ROLE_USER';
    final public const SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    final public const DEFAULT = self::USER;
}
