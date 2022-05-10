<?php

namespace Becklyn\Security\Application;

use Becklyn\Security\Domain\UserRepository;
use Becklyn\Security\Domain\IsPasswordValidForUser;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-24
 */
class IsPasswordValid
{
    private UserRepository $userRepository;

    private IsPasswordValidForUser $isPasswordValidForUser;

    public function __construct(UserRepository $userRepository, IsPasswordValidForUser $isPasswordValidForUser)
    {
        $this->userRepository = $userRepository;
        $this->isPasswordValidForUser = $isPasswordValidForUser;
    }

    public function execute(string $email, string $plainPasswordToVerify): bool
    {
        return $this->isPasswordValidForUser->execute($this->userRepository->findOneByEmail($email), $plainPasswordToVerify);
    }
}
