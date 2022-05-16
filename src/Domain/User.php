<?php declare(strict_types=1);

namespace Becklyn\Security\Domain;

use Becklyn\Ddd\Events\Domain\EventProvider;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Illuminate\Support\Collection;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-03-02
 */
interface User extends EventProvider, PasswordAuthenticatedUserInterface
{
    public function id() : UserId;

    public function email() : string;

    public function isEnabled() : bool;

    public function enable() : self;

    public function disable() : self;

    public function roles() : Collection;

    public function hasRole(string $role) : bool;

    public function addRole(string $role) : self;

    public function removeRole(string $role) : self;

    public function changePassword(string $newPassword) : self;

    public function requestPasswordReset(string $passwordResetToken) : self;

    public function isPasswordResetValid(string $passwordResetToken, int $tokenExpirationMinutes) : bool;

    public function resetPassword(string $newPassword) : self;
}
