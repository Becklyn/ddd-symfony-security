<?php

namespace Becklyn\Security\Domain;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-20
 */
abstract class Role
{
    public const USER = 'ROLE_USER';
    public const SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const DEFAULT = self::USER;
}
