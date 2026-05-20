<?php

namespace Becklyn\Security\Tests\Application;

use Becklyn\Ddd\Transactions\Testing\TransactionManagerTestTrait;
use Becklyn\Security\Application\RequestPasswordReset;
use Becklyn\Security\Domain\GeneratePasswordResetToken;
use Becklyn\Security\Domain\NotifyPasswordReset;
use Becklyn\Security\Domain\RequestPasswordResetForUser;
use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class RequestPasswordResetTest extends TestCase
{
    use ProphecyTrait;
    use TransactionManagerTestTrait;
    use UserTestTrait;

    /**
     * @var ObjectProphecy|GeneratePasswordResetToken
     */
    private ObjectProphecy $generatePasswordResetToken;
    /**
     * @var ObjectProphecy|RequestPasswordResetForUser
     */
    private ObjectProphecy $requestPasswordResetForUser;
    /**
     * @var ObjectProphecy|NotifyPasswordReset
     */
    private ObjectProphecy $notifyPasswordReset;
    private RequestPasswordReset $fixture;

    protected function setUp(): void
    {
        $this->initTransactionManagerTestTrait();
        $this->initUserTestTrait();
        $this->generatePasswordResetToken = $this->prophesize(GeneratePasswordResetToken::class);
        $this->requestPasswordResetForUser = $this->prophesize(RequestPasswordResetForUser::class);
        $this->notifyPasswordReset = $this->prophesize(NotifyPasswordReset::class);
        $this->fixture = new RequestPasswordReset(
            $this->transactionManager->reveal(),
            $this->userRepository->reveal(),
            $this->generatePasswordResetToken->reveal(),
            $this->requestPasswordResetForUser->reveal(),
            $this->notifyPasswordReset->reveal()
        );
    }

    public function testTokenIsGeneratedResetIsRequestedForUserWithTokenTransactionIsCommittedAndUserIsNotifiedWithToken(): void
    {
        $email = $this->givenAnUserEmail();
        $this->givenTransactionIsBegun();
        $user = $this->givenAUserCanBeFoundByEmail($email);
        $token = $this->givenPasswordResetTokenIsGenerated();
        $this->thenPasswordResetShouldBeRequestedForUserWithToken($user, $token);
        $this->thenTransactionShouldBeCommitted();
        $this->thenTransactionShouldNotBeRolledBack();
        $this->thenUserShouldBeNotifiedWithToken($user, $token);
        $this->whenRequestPasswordResetIsExecutedForEmail($email);
    }

    private function givenPasswordResetTokenIsGenerated(): string
    {
        $token = $this->givenAPasswordResetToken();
        $this->generatePasswordResetToken->execute()->willReturn($token);
        return $token;
    }

    private function thenPasswordResetShouldBeRequestedForUserWithToken(ObjectProphecy $user, string $token): void
    {
        $this->requestPasswordResetForUser->execute($user->reveal(), $token, $this->fixture)->shouldBeCalled();
    }

    private function thenUserShouldBeNotifiedWithToken(ObjectProphecy $user, string $token): void
    {
        $this->notifyPasswordReset->execute($user->reveal(), $token)->shouldBeCalled();
    }

    private function thenPasswordResetShouldNotBeRequested(): void
    {
        $this->requestPasswordResetForUser->execute(Argument::type(User::class), Argument::type('string'), Argument::type(RequestPasswordReset::class))->shouldNotBeCalled();
    }

    private function thenPasswordResetShouldNotBeNotified(): void
    {
        $this->notifyPasswordReset->execute(Argument::type(User::class), Argument::type('string'))->shouldNotBeCalled();
    }

    private function whenRequestPasswordResetIsExecutedForEmail(string $email): void
    {
        $this->fixture->execute($email);
    }

    public function testTransactionIsRolledBackNoExceptionIsThrownResetIsNotRequestedOrNotifiedIfUserCanNotBeFound(): void
    {
        $email = $this->givenAnUserEmail();
        $this->givenTransactionIsBegun();
        $this->givenAUserCanNotBeFoundByEmail($email);
        $this->thenTransactionShouldBeRolledBack();
        $this->thenTransactionShouldNotBeCommitted();
        $this->thenPasswordResetShouldNotBeRequested();
        $this->thenPasswordResetShouldNotBeNotified();
        $this->whenRequestPasswordResetIsExecutedForEmail($email);
    }

    public function testTransactionIsRolledBackExceptionIsThrownResetIsNotNotifiedIfRequestingResetThrowsException(): void
    {
        $email = $this->givenAnUserEmail();
        $this->givenTransactionIsBegun();
        $user = $this->givenAUserCanBeFoundByEmail($email);
        $token = $this->givenPasswordResetTokenIsGenerated();
        $this->givenRequestingPasswordResetForUserWithTokenThrowsException($user, $token);
        $this->thenTransactionShouldBeRolledBack();
        $this->thenTransactionShouldNotBeCommitted();
        $this->thenPasswordResetShouldNotBeNotified();
        $this->expectException(\Exception::class);
        $this->whenRequestPasswordResetIsExecutedForEmail($email);
    }

    private function givenRequestingPasswordResetForUserWithTokenThrowsException(ObjectProphecy $user, string $token): \Exception
    {
        $e = new \Exception();
        $this->requestPasswordResetForUser->execute($user->reveal(), $token, $this->fixture)->willThrow($e);
        return $e;
    }
}
