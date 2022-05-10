<?php

namespace Becklyn\Security\Domain;

use Becklyn\Ddd\Events\Domain\AbstractDomainEvent;
use Becklyn\Ddd\Events\Domain\EventId;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-27
 */
class PasswordResetRequested extends AbstractDomainEvent
{
    private UserId $userId;
    private string $token;
    private \DateTimeImmutable $requestTs;

    public function __construct(EventId $id, \DateTimeImmutable $raisedTs, UserId $userId, string $token, \DateTimeImmutable $requestTs)
    {
        parent::__construct($id, $raisedTs);
        $this->userId = $userId;
        $this->token = $token;
        $this->requestTs = $requestTs;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function requestTs(): \DateTimeImmutable
    {
        return $this->requestTs;
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
