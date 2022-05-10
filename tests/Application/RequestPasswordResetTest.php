<?php

namespace Becklyn\Security\Tests\Application;

use Becklyn\Ddd\Transactions\Application\TransactionManagerTestTrait;
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
        $this->thenPasswordResetShouldBeRequestedForUserWithToken($user->reveal(), $token);
        $this->thenTransactionShouldBeCommitted();
        $this->thenTransactionShouldNotBeRolledBack();
        $this->thenUserShouldBeNotifiedWithToken($user->reveal(), $token);
        $this->whenRequestPasswordResetIsExecutedForEmail($email);
    }

    private function givenPasswordResetTokenIsGenerated(): string
    {
        $token = $this->givenAPasswordResetToken();
        $this->generatePasswordResetToken->execute()->willReturn($token);
        return $token;
    }

    private function thenPasswordResetShouldBeRequestedForUserWithToken(User $user, string $token): void
    {
        $this->requestPasswordResetForUser->execute($user, $token)->shouldBeCalled();
    }

    private function thenUserShouldBeNotifiedWithToken(User $user, string $token): void
    {
        $this->notifyPasswordReset->execute($user, $token)->shouldBeCalled();
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

    private function thenPasswordResetShouldNotBeRequested(): void
    {
        $this->requestPasswordResetForUser->execute(Argument::any(), Argument::any())->shouldNotBeCalled();
    }

    private function thenPasswordResetShouldNotBeNotified(): void
    {
        $this->notifyPasswordReset->execute(Argument::any(), Argument::any())->shouldNotBeCalled();
    }

    public function testTransactionIsRolledBackExceptionIsThrownResetIsNotNotifiedIfRequestingResetThrowsException(): void
    {
        $email = $this->givenAnUserEmail();
        $this->givenTransactionIsBegun();
        $user = $this->givenAUserCanBeFoundByEmail($email);
        $token = $this->givenPasswordResetTokenIsGenerated();
        $this->givenRequestingPasswordResetForUserWithTokenThrowsException($user->reveal(), $token);
        $this->thenTransactionShouldBeRolledBack();
        $this->thenTransactionShouldNotBeCommitted();
        $this->thenPasswordResetShouldNotBeNotified();
        $this->expectException(\Exception::class);
        $this->whenRequestPasswordResetIsExecutedForEmail($email);
    }

    private function givenRequestingPasswordResetForUserWithTokenThrowsException(User $user, string $token): \Exception
    {
        $e = new \Exception();
        $this->requestPasswordResetForUser->execute($user, $token)->willThrow($e);
        return $e;
    }
}
