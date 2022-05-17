<?php declare(strict_types=1);

namespace Becklyn\Security\Infrastructure\Domain\Doctrine;

use Becklyn\Ddd\Events\Domain\EventProviderCapabilities;
use Becklyn\Security\Domain\PasswordChanged;
use Becklyn\Security\Domain\PasswordReset;
use Becklyn\Security\Domain\PasswordResetRequested;
use Becklyn\Security\Domain\Role;
use Becklyn\Security\Domain\RoleAddedToUser;
use Becklyn\Security\Domain\RoleRemovedFromUser;
use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserCreated;
use Becklyn\Security\Domain\UserDisabled;
use Becklyn\Security\Domain\UserEnabled;
use Becklyn\Security\Domain\UserId;
use Becklyn\Security\Infrastructure\Domain\Symfony\SymfonyUser;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Support\Collection;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-02
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="becklyn_users",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="uniq_user_uuid", columns={"uuid"}),
 *          @ORM\UniqueConstraint(name="uniq_user_email", columns={"email"})
 *     }
 * )
 */
class DoctrineSymfonyUser implements SymfonyUser
{
    use EventProviderCapabilities;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * Internal ids must be nullable otherwise Doctrine breaks when deleting records
     */
    protected ?int $internalId = null;

    /**
     * @ORM\Column(name="uuid", type="string", length=36, unique=true, nullable=false)
     */
    protected string $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=false)
     */
    protected string $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected string $password;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $enabled = true;

    /**
     * @ORM\Column(type="json")
     */
    protected array $roles = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected ?string $passwordResetToken = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    protected ?\DateTimeImmutable $passwordResetRequestTs = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    protected \DateTimeImmutable $createdTs;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    protected \DateTimeImmutable $updatedTs;

    protected function __construct()
    {
    }

    public static function create(UserId $id, string $email, string $password) : self
    {
        $user = new static();
        $user->id = $id->asString();
        $user->email = $email;
        $user->password = $password;
        $user->raiseEvent(new UserCreated($user->nextEventIdentity(), new \DateTimeImmutable(), $id, $email));
        $user->createdTs = new \DateTimeImmutable();
        $user->updatedTs = new \DateTimeImmutable();
        return $user;
    }

    public function id() : UserId
    {
        return UserId::fromString($this->id);
    }

    public function email() : string
    {
        return $this->email;
    }

    public function roles() : Collection
    {
        return Collection::make($this->getRoles());
    }

    public function hasRole(string $role) : bool
    {
        return $this->roles()->containsStrict(\strtoupper($role));
    }

    public function addRole(string $role) : self
    {
        $role = \strtoupper($role);

        if (Role::DEFAULT === $role) {
            return $this;
        }

        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
            $this->raiseEvent(new RoleAddedToUser($this->nextEventIdentity(), new \DateTimeImmutable(), $this->id(), $role));
            $this->markAsModified();
        }

        return $this;
    }

    public function removeRole(string $role) : self
    {
        $role = \strtoupper($role);

        if (false !== $key = \array_search($role, $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = \array_values($this->roles);
            $this->raiseEvent(new RoleRemovedFromUser($this->nextEventIdentity(), new \DateTimeImmutable(), $this->id(), $role));
            $this->markAsModified();
        }

        return $this;
    }

    public function changePassword(string $newPassword) : User
    {
        $this->password = $newPassword;
        $this->raiseEvent(new PasswordChanged($this->nextEventIdentity(), new \DateTimeImmutable(), $this->id()));
        $this->markAsModified();
        return $this;
    }

    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    public function enable() : self
    {
        if (!$this->enabled) {
            $this->enabled = true;
            $this->raiseEvent(new UserEnabled($this->nextEventIdentity(), new \DateTimeImmutable(), $this->id()));
            $this->markAsModified();
        }
        return $this;
    }

    public function disable() : self
    {
        if ($this->enabled) {
            $this->enabled = false;
            $this->raiseEvent(new UserDisabled($this->nextEventIdentity(), new \DateTimeImmutable(), $this->id()));
            $this->markAsModified();
        }
        return $this;
    }

    public function requestPasswordReset(string $passwordResetToken) : self
    {
        $this->passwordResetToken = $passwordResetToken;
        $this->passwordResetRequestTs = new \DateTimeImmutable();
        $this->raiseEvent(
            new PasswordResetRequested($this->nextEventIdentity(), new \DateTimeImmutable(), $this->id(), $passwordResetToken, $this->passwordResetRequestTs)
        );
        $this->markAsModified();
        return $this;
    }

    public function isPasswordResetValid(string $passwordResetToken, int $tokenExpirationMinutes) : bool
    {
        if ($passwordResetToken !== $this->passwordResetToken) {
            return false;
        }

        if (null === $this->passwordResetRequestTs) {
            return false;
        }

        if (\time() - $this->passwordResetRequestTs->getTimestamp() > $tokenExpirationMinutes * 60) {
            return false;
        }

        return true;
    }

    public function resetPassword(string $newPassword) : self
    {
        $this->password = $newPassword;
        $this->passwordResetToken = null;
        $this->passwordResetRequestTs = null;
        $this->raiseEvent(new PasswordReset($this->nextEventIdentity(), new \DateTimeImmutable(), $this->id()));
        $this->markAsModified();
        return $this;
    }

    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return ['ROLE_USER'];
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return  (Role|string)[] The user roles
     *
     * @internal Required by Symfony
     */
    public function getRoles() : array
    {
        // ensure that the user always has the default user role
        return \array_values(\array_unique(\array_merge($this->roles, [Role::DEFAULT])));
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string|null The encoded password if any
     *
     * @internal Required by Symfony
     */
    public function getPassword() : ?string
    {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     *
     * @internal Required by Symfony
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     *
     * @internal Required by Symfony
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     *
     * @internal Required by Symfony
     */
    public function eraseCredentials() : void
    {
    }


    public function getUserIdentifier () : string
    {
        return $this->getUsername();
    }


    public function  markAsModified () : void
    {
        $this->updatedTs = new \DateTimeImmutable();
    }
}
