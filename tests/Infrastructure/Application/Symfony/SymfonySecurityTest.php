<?php

namespace Becklyn\Security\Tests\Infrastructure\Application\Symfony;

use Becklyn\Security\Infrastructure\Application\Symfony\SymfonySecurity;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Security\Core\Security as BaseSymfonySecurity;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-16
 */
class SymfonySecurityTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var BaseSymfonySecurity|ObjectProphecy
     */
    private $baseSecurity;

    private SymfonySecurity $fixture;

    protected function setUp(): void
    {
        $this->baseSecurity = $this->prophesize(BaseSymfonySecurity::class);
        $this->fixture = new SymfonySecurity($this->baseSecurity->reveal());
    }

    public function testGetUserReturnsResultOfBaseSymfonySecurityGetUser(): void
    {
        $expectedResult = null;
        $this->baseSecurity->getUser()->willReturn(null);
        $this->assertSame($expectedResult, $this->fixture->getUser());
    }
}
