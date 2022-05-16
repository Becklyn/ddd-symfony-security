<?php declare(strict_types=1);

namespace Becklyn\Security\Infrastructure\Application\Symfony;

use Becklyn\Security\Application\Security;
use Becklyn\Security\Infrastructure\Domain\Symfony\SymfonyUser;
use Symfony\Component\Security\Core\Security as BaseSymfonySecurity;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-03-06
 */
class SymfonySecurity implements Security
{
    public function __construct(private readonly BaseSymfonySecurity $security)
    {
    }

    public function getUser() : ?SymfonyUser
    {
        /** @var SymfonyUser|null $user */
        $user = $this->security->getUser();
        return $user;
    }
}
