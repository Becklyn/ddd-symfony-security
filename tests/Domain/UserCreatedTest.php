<?php

namespace Becklyn\Security\Tests\Domain;

use Becklyn\Ddd\Events\Domain\DomainEventTestTrait;
use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserCreated;
use Becklyn\Security\Domain\UserTestTrait;
use PHPUnit\Framework\TestCase;

class UserCreatedTest extends TestCase
{
    use UserTestTrait;
    use DomainEventTestTrait;

    public function testAggregateIdReturnsUserIdPassedToConstructor(): void
    {
        $userId = $this->givenAUserId();
        $event = new UserCreated($this->givenAnEventId(), $this->givenARaisedTs(), $userId, $this->givenAnUserEmail());
        $this->assertSame($userId, $event->aggregateId());
    }

    public function testEmailReturnsEmailPassedToConstructor(): void
    {
        $email = $this->givenAnUserEmail();
        $event = new UserCreated($this->givenAnEventId(), $this->givenARaisedTs(), $this->givenAUserId(), $email);
        $this->assertEquals($email, $event->email());
    }

    public function testAggregateTypeReturnsUserClass(): void
    {
        $event = new UserCreated($this->givenAnEventId(), $this->givenARaisedTs(), $this->givenAUserId(), $this->givenAnUserEmail());
        $this->assertEquals(User::class, $event->aggregateType());
    }
}
