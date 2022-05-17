<?php declare(strict_types=1);

namespace Becklyn\Security\Application;

use Becklyn\Security\Domain\IsPasswordValidForUser;
use Becklyn\Security\Domain\UserRepository;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-24
 */
class IsPasswordValid
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly IsPasswordValidForUser $isPasswordValidForUser
    )
    {
    }

    public function execute(string $email, string $plainPasswordToVerify) : bool
    {
        return $this->isPasswordValidForUser->execute($this->userRepository->findOneByEmail($email), $plainPasswordToVerify);
    }
}
