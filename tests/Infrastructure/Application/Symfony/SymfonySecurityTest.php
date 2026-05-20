<?php

namespace Becklyn\Security\Tests\Infrastructure\Application\Symfony;

use Becklyn\Security\Infrastructure\Application\Symfony\SymfonySecurity;
use Becklyn\Security\Infrastructure\Domain\Symfony\SymfonyUser;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SymfonySecurityTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var TokenStorageInterface|ObjectProphecy
     */
    private $tokenStorage;

    private SymfonySecurity $fixture;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->prophesize(TokenStorageInterface::class);
        $this->fixture = new SymfonySecurity($this->tokenStorage->reveal());
    }

    public function testGetUserReturnsNullIfNoTokenExists(): void
    {
        $this->tokenStorage->getToken()->willReturn(null);
        $this->assertNull($this->fixture->getUser());
    }

    public function testGetUserReturnsSymfonyUserIfTokenContainsSymfonyUser(): void
    {
        $user = $this->prophesize(SymfonyUser::class)->reveal();
        $token = $this->prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);
        $this->tokenStorage->getToken()->willReturn($token->reveal());
        $this->assertSame($user, $this->fixture->getUser());
    }

    public function testGetUserReturnsNullIfTokenUserIsNotSymfonyUser(): void
    {
        $nonSymfonyUser = $this->prophesize(UserInterface::class)->reveal();
        $token = $this->prophesize(TokenInterface::class);
        $token->getUser()->willReturn($nonSymfonyUser);
        $this->tokenStorage->getToken()->willReturn($token->reveal());
        $this->assertNull($this->fixture->getUser());
    }
}
