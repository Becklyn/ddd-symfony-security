<?php declare(strict_types=1);

namespace Becklyn\Security\Application;

use Becklyn\Ddd\Transactions\Application\TransactionManager;
use Becklyn\Security\Domain\ChangePasswordForUser;
use Becklyn\Security\Domain\UserNotFoundException;
use Becklyn\Security\Domain\UserRepository;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-03-03
 */
class ChangePassword
{
    public function __construct(
        private readonly TransactionManager $transactionManager,
        private readonly UserRepository $userRepository,
        private readonly ChangePasswordForUser $changePasswordForUser
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
            $this->changePasswordForUser->execute($user, $newPlainPassword);
        } catch (\Exception $e) {
            $this->transactionManager->rollback();
            throw $e;
        }

        $this->transactionManager->commit();
    }
}
