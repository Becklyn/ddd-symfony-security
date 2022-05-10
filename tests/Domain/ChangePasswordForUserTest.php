<?php

namespace Becklyn\Security\Tests\Domain;

use Becklyn\Ddd\Events\Domain\DomainEventTestTrait;
use Becklyn\Security\Domain\ChangePasswordForUser;
use Becklyn\Security\Domain\EncodePasswordForUser;
use Becklyn\Security\Domain\UserTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ChangePasswordForUserTest extends TestCase
{
    use ProphecyTrait;
    use DomainEventTestTrait;
    use UserTestTrait;

    /**
     * @var ObjectProphecy|EncodePasswordForUser
     */
    private ObjectProphecy $encodePasswordForUser;

    private ChangePasswordForUser $fixture;

    protected function setUp(): void
    {
        $this->initDomainEventTestTrait();
        $this->encodePasswordForUser = $this->prophesize(EncodePasswordForUser::class);
        $this->fixture = new ChangePasswordForUser($this->eventRegistry->reveal(), $this->encodePasswordForUser->reveal());
    }

    public function testPasswordIsEncodedChangedForUserAndEventsAreDequeuedAndRegisteredForUser(): void
    {
        $user = $this->givenAUser();
        $password = $this->givenAUserPassword();
        $encodedPassword = $this->givenPasswordIsEncodedForUser($user, $password);
        $this->thenPasswordShouldBeChangedForUser($user, $encodedPassword);
        $this->thenEventRegistryShouldDequeueAndRegister($user->reveal());
        $this->whenChangePasswordForUserIsExecuted($user, $password);
    }

    private function givenPasswordIsEncodedForUser(ObjectProphecy $user, string $password): string
    {
        $encodedPassword = uniqid();
        $this->encodePasswordForUser->execute($user->reveal(), $password)->willReturn($encodedPassword);
        return $encodedPassword;
    }

    private function whenChangePasswordForUserIsExecuted(ObjectProphecy $user, string $password): void
    {
        $this->fixture->execute($user->reveal(), $password);
    }
}
