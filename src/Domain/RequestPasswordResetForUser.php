<?php declare(strict_types=1);

namespace Becklyn\Security\Domain;

use Becklyn\Ddd\Events\Domain\EventRegistry;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-27
 */
class RequestPasswordResetForUser
{
    public function __construct(
        private readonly EventRegistry $eventRegistry,
        private readonly HashPasswordResetToken $hashPasswordResetToken
    )
    {
    }

    public function execute(User $user, string $passwordResetToken) : void
    {
        $hashedToken = $this->hashPasswordResetToken->execute($passwordResetToken);
        $user->requestPasswordReset($hashedToken);
        $this->eventRegistry->dequeueProviderAndRegister($user);
    }
}
