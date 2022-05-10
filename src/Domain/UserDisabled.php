<?php

namespace Becklyn\Security\Domain;

use Becklyn\Ddd\Events\Domain\AbstractDomainEvent;
use Becklyn\Ddd\Events\Domain\EventId;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-20
 */
class UserDisabled extends AbstractDomainEvent
{
    private UserId $userId;

    public function __construct(EventId $id, \DateTimeImmutable $raisedTs, UserId $userId)
    {
        parent::__construct($id, $raisedTs);
        $this->userId = $userId;
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
