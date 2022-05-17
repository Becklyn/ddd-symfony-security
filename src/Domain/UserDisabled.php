<?php declare(strict_types=1);

namespace Becklyn\Security\Domain;

use Becklyn\Ddd\Events\Domain\AbstractDomainEvent;
use Becklyn\Ddd\Events\Domain\EventId;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-20
 */
class UserDisabled extends AbstractDomainEvent
{
    public function __construct(EventId $id, \DateTimeImmutable $raisedTs, private readonly UserId $userId)
    {
        parent::__construct($id, $raisedTs);
    }

    public function aggregateId() : UserId
    {
        return $this->userId;
    }

    public function aggregateType() : string
    {
        return User::class;
    }
}
