<?php

namespace Becklyn\Security\Tests\Domain;

use Becklyn\Ddd\Events\Domain\DomainEventTestTrait;
use Becklyn\Security\Domain\RoleAddedToUser;
use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserTestTrait;
use PHPUnit\Framework\TestCase;

class RoleAddedToUserTest extends TestCase
{
    use UserTestTrait;
    use DomainEventTestTrait;

    public function testRoleReturnsRolePassedToConstructor(): void
    {
        $role = 'ROLE_TEST';
        $event = new RoleAddedToUser($this->givenAnEventId(), $this->givenARaisedTs(), $this->givenAUserId(), $role);
        $this->assertEquals($role, $event->role());
    }

    public function testAggregateIdReturnsUserIdPassedToConstructor(): void
    {
        $userId = $this->givenAUserId();
        $event = new RoleAddedToUser($this->givenAnEventId(), $this->givenARaisedTs(), $userId, uniqid());
        $this->assertSame($userId, $event->aggregateId());
    }

    public function testAggregateTypeReturnsUserClass(): void
    {
        $event = new RoleAddedToUser($this->givenAnEventId(), $this->givenARaisedTs(), $this->givenAUserId(), uniqid());
        $this->assertEquals(User::class, $event->aggregateType());
    }
}
