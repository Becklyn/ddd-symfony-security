<?php

namespace Becklyn\Security\Tests\Infrastructure\Domain\Symfony;

use Becklyn\Security\Domain\UserTestTrait;
use Becklyn\Security\Infrastructure\Domain\Symfony\SymfonyIsPasswordValidForUser;
use Becklyn\Security\Infrastructure\Domain\Symfony\SymfonyUser;
use Becklyn\Security\Infrastructure\Domain\Symfony\SymfonyUserTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SymfonyIsPasswordValidForUserTest extends TestCase
{
    use ProphecyTrait;
    use UserTestTrait;
    use SymfonyUserTestTrait;

    /**
     * @var ObjectProphecy|UserPasswordEncoderInterface
     */
    private ObjectProphecy $encoder;

    private SymfonyIsPasswordValidForUser $fixture;

    protected function setUp(): void
    {
        $this->encoder = $this->prophesize(UserPasswordEncoderInterface::class);
        $this->fixture = new SymfonyIsPasswordValidForUser($this->encoder->reveal());
    }

    public function testExecute(): void
    {
        $user = $this->givenASymfonyUser();
        $password = $this->givenAUserPassword();
        $isPasswordValid = $this->givenIsPasswordValidCalledOnEncoderReturnsResult($user->reveal(), $password);
        $this->thenResultOfIsPasswordValidCalledOnEncoderShouldBeReturned(
            $isPasswordValid,
            $this->whenSymfonyIsPasswordValidForUserIsExecuted($user->reveal(), $password)
        );
    }

    private function givenIsPasswordValidCalledOnEncoderReturnsResult(SymfonyUser $user, string $password): bool
    {
        $result = (bool)random_int(0, 1);
        $this->encoder->isPasswordValid($user, $password)->willReturn($result);
        return $result;
    }

    private function thenResultOfIsPasswordValidCalledOnEncoderShouldBeReturned(bool $expected, bool $actual): void
    {
        $this->assertEquals($expected, $actual);
    }

    private function whenSymfonyIsPasswordValidForUserIsExecuted(SymfonyUser $user, string $password): bool
    {
        return $this->fixture->execute($user, $password);
    }
}
