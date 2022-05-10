<?php

namespace Becklyn\Security\Domain;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-27
 */
class HashPasswordResetToken
{
    private string $salt;

    public function __construct(string $salt)
    {
        $this->salt = $salt;
    }

    public function execute(string $plainToken): string
    {
        return sha1($this->salt . $plainToken);
    }
}
