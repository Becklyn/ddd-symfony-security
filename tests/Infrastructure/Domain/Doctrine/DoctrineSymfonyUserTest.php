<?php

namespace Becklyn\Security\Tests\Infrastructure\Domain\Doctrine;

use Becklyn\Ddd\Events\Domain\DomainEvent;
use Becklyn\Security\Domain\PasswordChanged;
use Becklyn\Security\Domain\PasswordReset;
use Becklyn\Security\Domain\PasswordResetRequested;
use Becklyn\Security\Domain\Role;
use Becklyn\Security\Domain\RoleAddedToUser;
use Becklyn\Security\Domain\RoleRemovedFromUser;
use Becklyn\Security\Domain\UserCreated;
use Becklyn\Security\Domain\UserDisabled;
use Becklyn\Security\Domain\UserEnabled;
use Becklyn\Security\Infrastructure\Domain\Doctrine\DoctrineSymfonyUser;
use Becklyn\Security\Domain\UserTestTrait;
use PHPUnit\Framework\TestCase;
use Tightenco\Collect\Support\Collection;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-16
 */
class DoctrineSymfonyUserTest extends TestCase
{
    use UserTestTrait;

    public function testCreate(): void
    {
        $id = $this->givenAUserId();
        $email = $this->givenAnUserEmail();
        $password = $this->givenAUserPassword();
        $user = DoctrineSymfonyUser::create($id, $email, $password);
        $this->assertTrue($id->equals($user->id()));
        $this->assertEquals($email, $user->email());
        $this->assertEquals($password, $user->getPassword());

        $events = Collection::make($user->dequeueEvents());
        $this->assertEquals(1, $events->count());
        $this->assertTrue(
            $events->contains(fn(DomainEvent $event) => $event instanceof UserCreated && $event->aggregateId()->equals($id) && $event->email() === $email)
        );
    }

    public function testChangePassword(): void
    {
        $originalPassword = $this->givenAUserPassword();
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $originalPassword);
        $user->dequeueEvents();

        $newPassword = $this->givenAUserPassword();
        $this->assertNotEquals($originalPassword, $newPassword);
        $user->changePassword($newPassword);
        $this->assertEquals($newPassword, $user->getPassword());
        $events = Collection::make($user->dequeueEvents());
        $this->assertEquals(1, $events->count());
        $this->assertTrue($events->contains(fn(DomainEvent $event) => $event instanceof PasswordChanged && $event->aggregateId()->equals($user->id())));
    }

    public function testSymfonyUserInterfaceFeatures(): void
    {
        $email = $this->givenAnUserEmail();
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $email, $this->givenAUserPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
        $this->assertNull($user->getSalt());
        $this->assertEquals($email, $user->getUsername());
    }

    public function testRolesReturnArrayContainingDefaultRoleForFreshUser(): void
    {
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $roles = $user->roles();
        $this->assertCount(1, $roles);
        $this->assertContains(Role::DEFAULT, $roles);
    }

    public function testHasRoleReturnsTrueIfUserHasRole(): void
    {
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $this->assertContains(Role::DEFAULT, $user->roles());

        $this->assertTrue($user->hasRole(Role::DEFAULT));
    }

    public function testHasRoleReturnsFalseIfUserDoesNotHaveRole(): void
    {
        $role = 'FAKE_ROLE';
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $this->assertNotContains($role, $user->roles());

        $this->assertFalse($user->hasRole($role));
    }

    public function testAddRoleAddsRole(): void
    {
        $role = 'NEW_ROLE';
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $this->assertNotContains($role, $user->roles());

        $user->addRole($role);
        $this->assertContains($role, $user->roles());
    }

    public function testAddRoleRaisesRoleAddedEventIfUserDoesNotAlreadyHaveThatRole(): void
    {
        $role = 'NEW_ROLE';
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->dequeueEvents();
        $this->assertNotContains($role, $user->roles());

        $user->addRole($role);
        $events = Collection::make($user->dequeueEvents());
        $this->assertTrue($events->contains(fn(RoleAddedToUser $event) => $event->role() === $role && $event->aggregateId()->equals($user->id())));
    }

    public function testAddRoleDoesNotRaiseRoleAddedEventIfUserAlreadyHasThatRole(): void
    {
        $role = 'NEW_ROLE';
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->addRole($role);
        $user->dequeueEvents();
        $this->assertContains($role, $user->roles());

        $user->addRole($role);
        $this->assertEmpty($user->dequeueEvents());
    }

    public function testAddRoleDoesNotRaiseRoleAddedEventForDefaultRole(): void
    {
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->dequeueEvents();

        $user->addRole(Role::DEFAULT);
        $this->assertEmpty($user->dequeueEvents());
    }

    public function testRemoveRoleRemovesRole(): void
    {
        $role = 'REMOVED_ROLE';
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->addRole($role);
        $user->dequeueEvents();
        $this->assertContains($role, $user->roles());

        $user->removeRole($role);
        $this->assertNotContains($role, $user->roles());
    }

    public function testRemoveRoleRaisesRoleRemovedEventIfUserAlreadyHasThatRole(): void
    {
        $role = 'REMOVED_ROLE';
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->addRole($role);
        $user->dequeueEvents();
        $this->assertContains($role, $user->roles());

        $user->removeRole($role);
        $events = Collection::make($user->dequeueEvents());
        $this->assertTrue($events->contains(fn(RoleRemovedFromUser $event) => $event->role() === $role && $event->aggregateId()->equals($user->id())));
    }

    public function testRemoveRoleDoesNotRaiseRoleRemovedEventIfUserDoesNotAlreadyHaveThatRole(): void
    {
        $role = 'REMOVED_ROLE';
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->dequeueEvents();
        $this->assertNotContains($role, $user->roles());

        $user->removeRole($role);
        $this->assertEmpty($user->dequeueEvents());
    }

    public function testRemoveRoleDoesNotRaiseRoleRemovedEventForDefaultRole(): void
    {
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->dequeueEvents();
        $this->assertContains(Role::DEFAULT, $user->roles());

        $user->removeRole(Role::DEFAULT);
        $this->assertEmpty($user->dequeueEvents());
    }

    public function testUserIsEnabledByDefault(): void
    {
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $this->assertTrue($user->isEnabled());
    }

    public function testDisableDisablesAnEnabledUser(): void
    {
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $this->assertTrue($user->isEnabled());

        $user->disable();
        $this->assertFalse($user->isEnabled());
    }

    public function testDisableRaisesUserDisabledEventIfUserWasPreviouslyEnabled(): void
    {
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->dequeueEvents();
        $this->assertTrue($user->isEnabled());

        $user->disable();
        $events = Collection::make($user->dequeueEvents());
        $this->assertTrue($events->contains(fn(UserDisabled $event) => $event->aggregateId()->equals($user->id())));
    }

    public function testDisableDoesNotRaiseUserDisabledEventIfUserWasAlreadyDisabled(): void
    {
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->disable();
        $user->dequeueEvents();
        $this->assertFalse($user->isEnabled());

        $user->disable();
        $this->assertEmpty($user->dequeueEvents());
    }

    public function testEnableEnablesADisabledUser(): void
    {
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->disable();
        $user->dequeueEvents();
        $this->assertFalse($user->isEnabled());

        $user->enable();
        $this->assertTrue($user->isEnabled());
    }

    public function testEnableRaisesUserEnabledEventIfUserWasPreviouslyDisabled(): void
    {
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->disable();
        $user->dequeueEvents();
        $this->assertFalse($user->isEnabled());

        $user->enable();
        $events = Collection::make($user->dequeueEvents());
        $this->assertTrue($events->contains(fn(UserEnabled $event) => $event->aggregateId()->equals($user->id())));
    }

    public function testEnableDoesNotRaiseUserEnabledEventIfUserWasAlreadyEnabled(): void
    {
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->dequeueEvents();
        $this->assertTrue($user->isEnabled());

        $user->enable();
        $this->assertEmpty($user->dequeueEvents());
    }

    public function testRequestPasswordResetRaisesPasswordResetRequestedEvent(): void
    {
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->dequeueEvents();

        $token = $this->givenAPasswordResetToken();
        $user->requestPasswordReset($token);
        $events = Collection::make($user->dequeueEvents());
        $this->assertTrue($events->contains(fn(PasswordResetRequested $event) => $event->aggregateId()->equals($user->id()) && $event->token() === $token));
    }

    public function testIsPasswordResetValidReturnsTrueIfValidTokenAndNonExpiredTokenExpirationMinutesArePassed(): void
    {
        $token = $this->givenAPasswordResetToken();
        $tokenExpirationInMinutes = 60;

        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->requestPasswordReset($token);

        $this->assertTrue($user->isPasswordResetValid($token, $tokenExpirationInMinutes));
    }

    public function testIsPasswordResetValidReturnsFalseIfInvalidTokenIsPassed(): void
    {
        $token = $this->givenAPasswordResetToken();
        $invalidToken = $this->givenAPasswordResetToken();
        $this->assertNotEquals($token, $invalidToken);
        $tokenExpirationInMinutes = 60;

        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->requestPasswordReset($token);

        $this->assertFalse($user->isPasswordResetValid($invalidToken, $tokenExpirationInMinutes));
    }

    public function testIsPasswordResetValidReturnsFalseIfExpiredTokenExpirationMinutesArePassed(): void
    {
        $token = $this->givenAPasswordResetToken();
        $tokenExpirationInMinutes = 0;

        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->requestPasswordReset($token);

        sleep(1);
        $this->assertFalse($user->isPasswordResetValid($token, $tokenExpirationInMinutes));
    }

    public function testResetPasswordRaisesPasswordResetEvent(): void
    {
        $user = DoctrineSymfonyUser::create($this->givenAUserId(), $this->givenAnUserEmail(), $this->givenAUserPassword());
        $user->dequeueEvents();

        $user->resetPassword($this->givenAUserPassword());
        $events = Collection::make($user->dequeueEvents());
        $this->assertTrue($events->contains(fn(PasswordReset $event) => $event->aggregateId()->equals($user->id())));
    }
}
