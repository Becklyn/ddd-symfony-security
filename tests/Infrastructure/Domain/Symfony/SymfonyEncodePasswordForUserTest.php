<?php

namespace Becklyn\Security\Tests\Infrastructure\Domain\Symfony;

use Becklyn\Security\Domain\UserTestTrait;
use Becklyn\Security\Infrastructure\Domain\Symfony\SymfonyEncodePasswordForUser;
use Becklyn\Security\Infrastructure\Domain\Symfony\SymfonyUserTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SymfonyEncodePasswordForUserTest extends TestCase
{
    use ProphecyTrait;
    use UserTestTrait;
    use SymfonyUserTestTrait;

    /**
     * @var ObjectProphecy|UserPasswordEncoderInterface
     */
    private ObjectProphecy $encoder;

    private SymfonyEncodePasswordForUser $fixture;

    protected function setUp(): void
    {
        $this->encoder = $this->prophesize(UserPasswordEncoderInterface::class);
        $this->fixture = new SymfonyEncodePasswordForUser($this->encoder->reveal());
    }

    public function testEncodedPasswordIsReturned(): void
    {
        $plainPassword = $this->givenAUserPassword();
        $encodedPassword = $this->givenAUserPassword();
        $user = $this->givenASymfonyUser();
        $this->givenEncoderEncodesPasswordForUser($user, $plainPassword, $encodedPassword);
        $this->thenEncodedPasswordShouldBeReturned(
            $encodedPassword,
            $this->whenSymfonyEncodePasswordForUserIsExecuted($user, $plainPassword)
        );
    }

    private function givenEncoderEncodesPasswordForUser(ObjectProphecy $user, string $plainPassword, string $encodedPassword): void
    {
        $this->encoder->encodePassword($user->reveal(), $plainPassword)->willReturn($encodedPassword);
    }

    private function thenEncodedPasswordShouldBeReturned(string $expected, string $actual): void
    {
        $this->assertEquals($expected, $actual);
    }

    private function whenSymfonyEncodePasswordForUserIsExecuted(ObjectProphecy $user, string $plainPassword): string
    {
        return $this->fixture->execute($user->reveal(), $plainPassword);
    }
}
