<?php

namespace Becklyn\Security\Domain;

use Becklyn\Ddd\Events\Domain\AbstractDomainEvent;
use Becklyn\Ddd\Events\Domain\EventId;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-20
 */
class RoleAddedToUser extends AbstractDomainEvent
{
    private UserId $userId;

    private string $role;

    public function __construct(EventId $id, \DateTimeImmutable $raisedTs, UserId $userId, string $role)
    {
        parent::__construct($id, $raisedTs);
        $this->userId = $userId;
        $this->role = $role;
    }

    public function role(): string
    {
        return $this->role;
    }

    public function aggregateId(): UserId
    {
        return $this->userId;
    }

    public function aggregateType(): string
    {
        return User::class;
    }
}
