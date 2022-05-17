<?php declare(strict_types=1);

namespace Becklyn\Security\Domain;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-27
 */
class HashPasswordResetToken
{
    public function __construct(private readonly string $salt)
    {
    }

    public function execute(string $plainToken) : string
    {
        return \sha1($this->salt . $plainToken);
    }
}
