<?php

namespace Becklyn\Security\Tests\Domain;

use Becklyn\Ddd\Events\Testing\DomainEventTestTrait;
use Becklyn\Security\Application\RequestPasswordReset;
use Becklyn\Security\Domain\HashPasswordResetToken;
use Becklyn\Security\Domain\RequestPasswordResetForUser;
use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class RequestPasswordResetForUserTest extends TestCase
{
    use ProphecyTrait;
    use DomainEventTestTrait;
    use UserTestTrait;

    /**
     * @var ObjectProphecy|HashPasswordResetToken
     */
    private ObjectProphecy $hashPasswordResetToken;

    private RequestPasswordResetForUser $fixture;

    protected function setUp(): void
    {
        $this->initDomainEventTestTrait();
        $this->hashPasswordResetToken = $this->prophesize(HashPasswordResetToken::class);
        $this->fixture = new RequestPasswordResetForUser($this->eventRegistry->reveal(), $this->hashPasswordResetToken->reveal());
    }

    public function testTokenIsHashedResetRequestedWithHashedTokenAndEventsDequeuedFromUser(): void
    {
        $user = $this->givenAUser();
        $token = $this->givenAPasswordResetToken();

        $command = $this->prophesize(RequestPasswordReset::class)->reveal();
        $hashedToken = $this->givenTokenIsHashed($token);
        $this->thenPasswordResetShouldBeRequestedForUser($user, $hashedToken);
        $this->thenEventRegistryShouldDequeueAndRegister($user->reveal());
        $this->whenRequestPasswordResetForUserIsExecuted($user->reveal(), $token, $command);
    }

    private function givenTokenIsHashed(string $token): string
    {
        $hashedToken = uniqid();
        $this->hashPasswordResetToken->execute($token)->willReturn($hashedToken);
        return $hashedToken;
    }

    /**
     * @param ObjectProphecy|User $user
     */
    private function thenPasswordResetShouldBeRequestedForUser(ObjectProphecy $user, string $hashedToken): void
    {
        $user->requestPasswordReset($hashedToken)->shouldBeCalled();
    }

    private function whenRequestPasswordResetForUserIsExecuted(User $user, string $token, RequestPasswordReset $command)
    {
        $this->fixture->execute($user, $token, $command);
    }
}
