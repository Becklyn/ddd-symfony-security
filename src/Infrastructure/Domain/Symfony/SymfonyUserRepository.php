<?php declare(strict_types=1);

namespace Becklyn\Security\Infrastructure\Domain\Symfony;

use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserRepository;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-02
 */
interface SymfonyUserRepository extends UserRepository
{
    /**
     * @inheritDoc
     *
     * @param SymfonyUser $user
     */
    public function add(User $user) : void;

    /**
     * @inheritDoc
     */
    public function findOneByEmail(string $email) : SymfonyUser;
}
