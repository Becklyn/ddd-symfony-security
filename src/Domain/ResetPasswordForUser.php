<?php declare(strict_types=1);

namespace Becklyn\Security\Domain;

use Becklyn\Ddd\Events\Domain\EventRegistry;
use Becklyn\Security\Application\ResetPassword;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-29
 */
class ResetPasswordForUser
{
    public function __construct(
        private readonly EventRegistry $eventRegistry,
        private readonly EncodePasswordForUser $encodePasswordForUser
    )
    {
    }

    public function execute(User $user, string $newPlainPassword, ResetPassword $command) : void
    {
        $newEncodedPassword = $this->encodePasswordForUser->execute($user, $newPlainPassword);
        $user->resetPassword($newEncodedPassword);
        $this->eventRegistry->dequeueProviderAndRegister($user, $command);
    }
}
