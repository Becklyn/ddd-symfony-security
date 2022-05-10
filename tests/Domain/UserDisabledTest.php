<?php

namespace Becklyn\Security\Tests\Domain;

use Becklyn\Ddd\Events\Domain\DomainEventTestTrait;
use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserDisabled;
use Becklyn\Security\Domain\UserTestTrait;
use PHPUnit\Framework\TestCase;

class UserDisabledTest extends TestCase
{
    use UserTestTrait;
    use DomainEventTestTrait;

    public function testAggregateIdReturnsUserIdPassedToConstructor(): void
    {
        $userId = $this->givenAUserId();
        $event = new UserDisabled($this->givenAnEventId(), $this->givenARaisedTs(), $userId);
        $this->assertSame($userId, $event->aggregateId());
    }

    public function testAggregateTypeReturnsUserClass(): void
    {
        $event = new UserDisabled($this->givenAnEventId(), $this->givenARaisedTs(), $this->givenAUserId());
        $this->assertEquals(User::class, $event->aggregateType());
    }
}
