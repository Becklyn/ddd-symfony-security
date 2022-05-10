<?php

namespace Becklyn\Security\Domain;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-27
 */
class GeneratePasswordResetToken
{
    public function execute(): string
    {
        return bin2hex(random_bytes(32));
    }
}
