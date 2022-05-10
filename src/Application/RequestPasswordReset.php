<?php

namespace Becklyn\Security\Application;

use Becklyn\Ddd\Transactions\Application\TransactionManager;
use Becklyn\Security\Domain\GeneratePasswordResetToken;
use Becklyn\Security\Domain\NotifyPasswordReset;
use Becklyn\Security\Domain\RequestPasswordResetForUser;
use Becklyn\Security\Domain\UserNotFoundException;
use Becklyn\Security\Domain\UserRepository;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-27
 */
class RequestPasswordReset
{
    private TransactionManager $transactionManager;
    private UserRepository $userRepository;
    private GeneratePasswordResetToken $generatePasswordResetToken;
    private RequestPasswordResetForUser $requestPasswordResetForUser;
    private NotifyPasswordReset $notifyPasswordReset;

    public function __construct(
        TransactionManager $transactionManager,
        UserRepository $userRepository,
        GeneratePasswordResetToken $generatePasswordResetToken,
        RequestPasswordResetForUser $requestPasswordResetForUser,
        NotifyPasswordReset $notifyPasswordReset
    ) {
        $this->transactionManager = $transactionManager;
        $this->userRepository = $userRepository;
        $this->generatePasswordResetToken = $generatePasswordResetToken;
        $this->requestPasswordResetForUser = $requestPasswordResetForUser;
        $this->notifyPasswordReset = $notifyPasswordReset;
    }

    public function execute(string $email): void
    {
        $this->transactionManager->begin();

        try {
            $user = $this->userRepository->findOneByEmail($email);
            $token = $this->generatePasswordResetToken->execute();
            $this->requestPasswordResetForUser->execute($user, $token);
        } catch (UserNotFoundException $e) {
            $this->transactionManager->rollback();
            return;
        } catch (\Exception $e) {
            $this->transactionManager->rollback();
            throw $e;
        }

        $this->transactionManager->commit();

        $this->notifyPasswordReset->execute($user, $token);
    }
}
