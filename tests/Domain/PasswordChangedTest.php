<?php

namespace Becklyn\Security\Tests\Domain;

use Becklyn\Ddd\Events\Domain\DomainEventTestTrait;
use Becklyn\Security\Domain\PasswordChanged;
use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserTestTrait;
use PHPUnit\Framework\TestCase;

class PasswordChangedTest extends TestCase
{
    use UserTestTrait;
    use DomainEventTestTrait;

    public function testAggregateIdReturnsUserIdPassedToConstructor(): void
    {
        $userId = $this->givenAUserId();
        $event = new PasswordChanged($this->givenAnEventId(), $this->givenARaisedTs(), $userId);
        $this->assertSame($userId, $event->aggregateId());
    }

    public function testAggregateTypeReturnsUserClass(): void
    {
        $event = new PasswordChanged($this->givenAnEventId(), $this->givenARaisedTs(), $this->givenAUserId());
        $this->assertEquals(User::class, $event->aggregateType());
    }
}
