<?php

namespace Becklyn\Security\Tests\Domain;

use Becklyn\Security\Domain\FindUserForPasswordReset;
use Becklyn\Security\Domain\HashPasswordResetToken;
use Becklyn\Security\Domain\PasswordResetExpiredException;
use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class FindUserForPasswordResetTest extends TestCase
{
    use ProphecyTrait;
    use UserTestTrait;

    /**
     * @var ObjectProphecy|HashPasswordResetToken
     */
    private ObjectProphecy $hashPasswordResetToken;

    private FindUserForPasswordReset $fixture;

    protected function setUp(): void
    {
        $this->initUserTestTrait();
        $this->hashPasswordResetToken = $this->prophesize(HashPasswordResetToken::class);
        $this->fixture = new FindUserForPasswordReset($this->userRepository->reveal(), $this->hashPasswordResetToken->reveal());
    }

    public function testUserFoundByHashedTokenIsReturnedIfPasswordResetIsValidForUser(): void
    {
        $token = $this->givenAPasswordResetToken();
        $expirationMinutes = $this->givenAPasswordResetTokenExpirationInMinutes();

        $hashedToken = $this->givenThePasswordResetTokenIsHashed($token);
        $user = $this->givenAUserCanBeFoundByPasswordResetToken($hashedToken);
        $this->givenPasswordResetIsValidForUser($user, $hashedToken, $expirationMinutes);

        $this->thenUserShouldBeReturned(
            $user->reveal(),
            $this->whenFindUserForPasswordResetIsExecuted($token, $expirationMinutes)
        );
    }

    private function givenThePasswordResetTokenIsHashed(string $token): string
    {
        $hashedToken = uniqid();
        $this->hashPasswordResetToken->execute($token)->willReturn($hashedToken);
        return $hashedToken;
    }

    private function thenUserShouldBeReturned($expected, $actual): void
    {
        $this->assertSame($expected, $actual);
    }

    private function whenFindUserForPasswordResetIsExecuted(string $token, int $expirationMinutes): User
    {
        return $this->fixture->execute($token, $expirationMinutes);
    }

    public function testPasswordResetExpiredExceptionIsThrownIfPasswordResetIsNotValidForUser(): void
    {
        $token = $this->givenAPasswordResetToken();
        $expirationMinutes = $this->givenAPasswordResetTokenExpirationInMinutes();

        $hashedToken = $this->givenThePasswordResetTokenIsHashed($token);
        $user = $this->givenAUserCanBeFoundByPasswordResetToken($hashedToken);
        $this->givenPasswordResetIsNotValidForUser($user, $hashedToken, $expirationMinutes);

        $this->thenPasswordResetExpiredExceptionShouldBeThrown();
        $this->whenFindUserForPasswordResetIsExecuted($token, $expirationMinutes);
    }

    private function thenPasswordResetExpiredExceptionShouldBeThrown(): void
    {
        $this->expectException(PasswordResetExpiredException::class);
    }
}
