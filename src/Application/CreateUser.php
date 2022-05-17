<?php declare(strict_types=1);

namespace Becklyn\Security\Application;

use Becklyn\Ddd\Transactions\Application\TransactionManager;
use Becklyn\Security\Domain\CreateUser as DomainCreateUser;
use Becklyn\Security\Domain\UserId;
use Becklyn\Security\Domain\UserRepository;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-03-03
 */
class CreateUser
{
    public function __construct(
        private readonly TransactionManager $transactionManager,
        private readonly UserRepository $userRepository,
        private readonly DomainCreateUser $createUser
    )
    {
    }
    public function execute(string $email, string $plainPassword) : UserId
    {
        $this->transactionManager->begin();

        try {
            $id = $this->userRepository->nextIdentity();
            $this->createUser->execute($id, $email, $plainPassword);
        } catch (\Exception $e) {
            $this->transactionManager->rollback();
            throw $e;
        }

        $this->transactionManager->commit();

        return $id;
    }
}
