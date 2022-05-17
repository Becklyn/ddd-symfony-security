<?php declare(strict_types=1);

namespace Becklyn\Security\Application;

use Becklyn\Security\Domain\FindUserForPasswordReset;
use Becklyn\Security\Domain\PasswordResetExpiredException;
use Becklyn\Security\Domain\UserNotFoundException;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-29
 */
class FindEmailForPasswordReset
{
    public function __construct(
        private readonly FindUserForPasswordReset $findUser,
        private readonly int $tokenExpirationMinutes
    )
    {
    }

    /**
     * @throws PasswordResetExpiredException
     * @throws UserNotFoundException
     */
    public function execute(string $passwordResetToken) : string
    {
        return $this->findUser->execute($passwordResetToken, $this->tokenExpirationMinutes)->email();
    }
}
