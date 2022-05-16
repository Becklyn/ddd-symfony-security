<?php declare(strict_types=1);

namespace Becklyn\Security\Application;

use Becklyn\Ddd\Transactions\Application\TransactionManager;
use Becklyn\Security\Domain\ResetPasswordForUser;
use Becklyn\Security\Domain\UserNotFoundException;
use Becklyn\Security\Domain\UserRepository;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-29
 */
class ResetPassword
{
    public function __construct(
        private readonly TransactionManager $transactionManager,
        private readonly UserRepository $userRepository,
        private readonly ResetPasswordForUser $resetPasswordForUser
    )
    {
    }

    /**
     * @throws UserNotFoundException
     */
    public function execute(string $email, string $newPlainPassword) : void
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
