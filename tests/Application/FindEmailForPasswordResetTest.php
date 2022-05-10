<?php

namespace Becklyn\Security\Tests\Application;

use Becklyn\Security\Application\FindEmailForPasswordReset;
use Becklyn\Security\Domain\FindUserForPasswordReset;
use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class FindEmailForPasswordResetTest extends TestCase
{
    use ProphecyTrait;
    use UserTestTrait;

    /**
     * @var ObjectProphecy|FindUserForPasswordReset
     */
    private ObjectProphecy $findUserForPasswordReset;

    private int $tokenExpirationMinutes;

    private FindEmailForPasswordReset $fixture;

    public function setUp(): void
    {
        $this->findUserForPasswordReset = $this->prophesize(FindUserForPasswordReset::class);
        $this->tokenExpirationMinutes = random_int(1, 10000);
        $this->fixture = new FindEmailForPasswordReset($this->findUserForPasswordReset->reveal(), $this->tokenExpirationMinutes);
    }

    public function testEmailFromUserFoundForPasswordResetIsReturned(): void
    {
        $token = $this->givenAPasswordResetToken();
        $email = $this->givenAnUserEmail();

        $user = $this->givenUserCanBeFoundForPasswordReset($token, $this->tokenExpirationMinutes);
        $this->givenUserHasEmail($user, $email);
        $this->thenEmailShouldBeReturned(
            $email,
            $this->whenFindEmailForPasswordResetIsExecuted($token)
        );
    }

    /**
     * @return ObjectProphecy|User
     */
    private function givenUserCanBeFoundForPasswordReset(string $token, int $tokenExpirationMinutes): ObjectProphecy
    {
        $user = $this->prophesize(User::class);
        $this->findUserForPasswordReset->execute($token, $tokenExpirationMinutes)->willReturn($user->reveal());
        return $user;
    }

    private function thenEmailShouldBeReturned(string $expected, string $actual): void
    {
        $this->assertEquals($expected, $actual);
    }

    private function whenFindEmailForPasswordResetIsExecuted(string $token): string
    {
        return $this->fixture->execute($token);
    }
}
