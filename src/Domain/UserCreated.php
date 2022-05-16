<?php declare(strict_types=1);

namespace Becklyn\Security\Domain;

use Becklyn\Ddd\Events\Domain\AbstractDomainEvent;
use Becklyn\Ddd\Events\Domain\EventId;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-14
 */
class UserCreated extends AbstractDomainEvent
{
    public function __construct(
        EventId $id,
        \DateTimeImmutable $raisedTs,
        private readonly UserId $userId,
        private readonly string $email
    )
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

    public function email() : string
    {
        return $this->email;
    }
}
