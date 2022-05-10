<?php

namespace Becklyn\Security\Tests\Domain;

use Becklyn\Ddd\Events\Domain\DomainEventTestTrait;
use Becklyn\Security\Domain\EncodePasswordForUser;
use Becklyn\Security\Domain\ResetPasswordForUser;
use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ResetPasswordForUserTest extends TestCase
{
    use ProphecyTrait;
    use DomainEventTestTrait;
    use UserTestTrait;

    /**
     * @var ObjectProphecy|EncodePasswordForUser
     */
    private ObjectProphecy $encodePasswordForUser;

    private ResetPasswordForUser $fixture;

    protected function setUp(): void
    {
        $this->initDomainEventTestTrait();
        $this->encodePasswordForUser = $this->prophesize(EncodePasswordForUser::class);
        $this->fixture = new ResetPasswordForUser($this->eventRegistry->reveal(), $this->encodePasswordForUser->reveal());
    }

    public function testPasswordIsResetWithEncodedPasswordAndUserIsDequeued(): void
    {
        $user = $this->givenAUser();
        $password = $this->givenAUserPassword();

        $encodedPassword = $this->givenPasswordIsEncoded($user->reveal(), $password);
        $this->thenPasswordShouldBeResetForUser($user, $encodedPassword);
        $this->thenEventRegistryShouldDequeueAndRegister($user->reveal());
        $this->whenRequestPasswordResetForUserIsExecuted($user->reveal(), $password);
    }

    private function givenPasswordIsEncoded(User $user, string $password): string
    {
        $encodedPassword = uniqid();
        $this->encodePasswordForUser->execute($user, $password)->willReturn($encodedPassword);
        return $encodedPassword;
    }

    /**
     * @param ObjectProphecy|User $user
     */
    private function thenPasswordShouldBeResetForUser(ObjectProphecy $user, string $encodedPassword)
    {
        $user->resetPassword($encodedPassword)->shouldBeCalled();
    }

    private function whenRequestPasswordResetForUserIsExecuted(User $user, string $password)
    {
        $this->fixture->execute($user, $password);
    }
}
