<?php

namespace Becklyn\Security\Domain;

use Prophecy\Prophecy\ObjectProphecy;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-15
 */
trait UserTestTrait
{
    /**
     * @var UserRepository|ObjectProphecy
     */
    protected $userRepository;

    protected function initUserTestTrait(): void
    {
        $this->userRepository = $this->prophesize(UserRepository::class);
    }

    protected function givenAUserId(): UserId
    {
        return UserId::next();
    }

    protected function givenAnUserEmail(): string
    {
        return uniqid() . '@' . uniqid() . '.com';
    }

    protected function givenAUserPassword(): string
    {
        return uniqid();
    }

    protected function thenUserNotFoundExceptionShouldBeThrown(): void
    {
        $this->expectException(UserNotFoundException::class);
    }

    /**
     * @return ObjectProphecy|User
     */
    protected function givenAUserCanBeFoundByEmail(string $email): ObjectProphecy
    {
        /** @var User|ObjectProphecy $user */
        $user = $this->prophesize(User::class);
        $user->email()->willReturn($email);
        $this->userRepository->findOneByEmail($email)->willReturn($user->reveal());
        return $user;
    }

    protected function givenAUserCanNotBeFoundByEmail(string $email): void
    {
        $this->userRepository->findOneByEmail($email)->willThrow(new UserNotFoundException());
    }

    /**
     * @return ObjectProphecy|User
     */
    protected function givenAUser(): ObjectProphecy
    {
        return $this->prophesize(User::class);
    }

    /**
     * @param ObjectProphecy|User $user
     */
    protected function thenPasswordShouldBeChangedForUser(ObjectProphecy $user, string $password): void
    {
        $user->changePassword($password)->shouldBeCalled();
    }

    protected function givenAPasswordResetToken(): string
    {
        return uniqid();
    }

    protected function givenAPasswordResetTokenExpirationInMinutes(): int
    {
        return random_int(1, 1000);
    }

    /**
     * @return ObjectProphecy|User
     */
    protected function givenAUserCanBeFoundByPasswordResetToken($token): ObjectProphecy
    {
        /** @var User|ObjectProphecy $user */
        $user = $this->prophesize(User::class);
        $this->userRepository->findOneByPasswordResetToken($token)->willReturn($user->reveal());
        return $user;
    }

    /**
     * @param ObjectProphecy|User $user
     */
    protected function givenPasswordResetIsValidForUser(ObjectProphecy $user, string $token, int $expirationMinutes): void
    {
        $user->isPasswordResetValid($token, $expirationMinutes)->willReturn(true);
    }

    /**
     * @param ObjectProphecy|User $user
     */
    protected function givenPasswordResetIsNotValidForUser(ObjectProphecy $user, string $token, int $expirationMinutes): void
    {
        $user->isPasswordResetValid($token, $expirationMinutes)->willReturn(false);
    }

    protected function givenAPasswordResetRequestTs(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    /**
     * @param ObjectProphecy|User $user
     */
    protected function givenUserHasEmail(ObjectProphecy $user, string $email): void
    {
        $user->email()->willReturn($email);
    }
}
