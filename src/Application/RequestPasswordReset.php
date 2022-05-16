<?php declare(strict_types=1);

namespace Becklyn\Security\Application;

use Becklyn\Ddd\Transactions\Application\TransactionManager;
use Becklyn\Security\Domain\GeneratePasswordResetToken;
use Becklyn\Security\Domain\NotifyPasswordReset;
use Becklyn\Security\Domain\RequestPasswordResetForUser;
use Becklyn\Security\Domain\UserNotFoundException;
use Becklyn\Security\Domain\UserRepository;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-27
 */
class RequestPasswordReset
{
    public function __construct(
        private readonly TransactionManager $transactionManager,
        private readonly UserRepository $userRepository,
        private readonly GeneratePasswordResetToken $generatePasswordResetToken,
        private readonly RequestPasswordResetForUser $requestPasswordResetForUser,
        private readonly NotifyPasswordReset $notifyPasswordReset
    )
    {
    }

    public function execute(string $email) : void
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
