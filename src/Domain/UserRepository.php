<?php declare(strict_types=1);

namespace Becklyn\Security\Domain;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-03-03
 */
interface UserRepository
{
    public function nextIdentity() : UserId;

    public function add(User $user) : void;

    /**
     * @throws UserNotFoundException
     */
    public function findOneByEmail(string $email) : User;

    /**
     * @throws UserNotFoundException
     */
    public function findOneByPasswordResetToken(string $token) : User;
}
