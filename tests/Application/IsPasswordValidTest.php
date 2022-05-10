<?php

namespace Becklyn\Security\Tests\Application;

use Becklyn\Security\Application\IsPasswordValid;
use Becklyn\Security\Domain\IsPasswordValidForUser;
use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class IsPasswordValidTest extends TestCase
{
    use ProphecyTrait;
    use UserTestTrait;

    /**
     * @var ObjectProphecy|IsPasswordValidForUser
     */
    private ObjectProphecy $isPasswordValidForUser;

    private IsPasswordValid $fixture;

    protected function setUp(): void
    {
        $this->initUserTestTrait();
        $this->isPasswordValidForUser = $this->prophesize(IsPasswordValidForUser::class);
        $this->fixture = new IsPasswordValid($this->userRepository->reveal(), $this->isPasswordValidForUser->reveal());
    }

    public function testExecute(): void
    {
        $email = $this->givenAnUserEmail();
        $password = $this->givenAUserPassword();
        $user = $this->givenAUserCanBeFoundByEmail($email);
        $isPasswordValidForUser = $this->givenIsPasswordValidForUserReturnsResultForUserAndPassword($user->reveal(), $password);
        $this->thenResultOfIsPasswordValidForUserShouldBeReturned(
            $isPasswordValidForUser,
            $this->whenIsPasswordValidIsExecuted($email, $password)
        );
    }

    private function givenIsPasswordValidForUserReturnsResultForUserAndPassword(User $user, string $password): bool
    {
        $result = (bool)random_int(0, 1);
        $this->isPasswordValidForUser->execute($user, $password)->willReturn($result);
        return $result;
    }

    private function thenResultOfIsPasswordValidForUserShouldBeReturned(bool $expected, bool $actual): void
    {
        $this->assertEquals($expected, $actual);
    }

    private function whenIsPasswordValidIsExecuted(string $email, string $password): bool
    {
        return $this->fixture->execute($email, $password);
    }
}
