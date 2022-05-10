<?php

namespace Becklyn\Security\Application;

use Becklyn\Ddd\Transactions\Application\TransactionManager;
use Becklyn\Security\Domain\ResetPasswordForUser;
use Becklyn\Security\Domain\UserNotFoundException;
use Becklyn\Security\Domain\UserRepository;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-29
 */
class ResetPassword
{
    private TransactionManager $transactionManager;
    private UserRepository $userRepository;
    private ResetPasswordForUser $resetPasswordForUser;

    public function __construct(
        TransactionManager $transactionManager,
        UserRepository $userRepository,
        ResetPasswordForUser $resetPasswordForUser
    ) {
        $this->transactionManager = $transactionManager;
        $this->userRepository = $userRepository;
        $this->resetPasswordForUser = $resetPasswordForUser;
    }

    /**
     * @throws UserNotFoundException
     */
    public function execute(string $email, string $newPlainPassword): void
    {
        $this->transactionManager->begin();

        try {
            $user = $this->userRepository->findOneByEmail($email);
            $this->resetPasswordForUser->execute($user, $newPlainPassword);
        } catch (\Exception $e) {
            $this->transactionManager->rollback();
            throw $e;
        }

        $this->transactionManager->commit();
    }
}
