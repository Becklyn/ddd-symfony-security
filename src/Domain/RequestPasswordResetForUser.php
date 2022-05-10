<?php

namespace Becklyn\Security\Domain;

use Becklyn\Ddd\Events\Domain\EventRegistry;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-27
 */
class RequestPasswordResetForUser
{
    private EventRegistry $eventRegistry;
    private HashPasswordResetToken $hashPasswordResetToken;

    public function __construct(EventRegistry $eventRegistry, HashPasswordResetToken $hashPasswordResetToken)
    {
        $this->eventRegistry = $eventRegistry;
        $this->hashPasswordResetToken = $hashPasswordResetToken;
    }

    public function execute(User $user, string $passwordResetToken): void
    {
        $hashedToken = $this->hashPasswordResetToken->execute($passwordResetToken);
        $user->requestPasswordReset($hashedToken);
        $this->eventRegistry->dequeueProviderAndRegister($user);
    }
}
