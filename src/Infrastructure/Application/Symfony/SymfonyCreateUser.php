<?php declare(strict_types=1);

namespace Becklyn\Security\Infrastructure\Application\Symfony;

use Becklyn\Ddd\Commands\Domain\AbstractCommand;
use Becklyn\Ddd\Events\Domain\EventRegistry;
use Becklyn\Ddd\Transactions\Application\TransactionManager;
use Becklyn\Security\Infrastructure\Domain\Doctrine\DoctrineSymfonyUser;
use Becklyn\Security\Infrastructure\Domain\Symfony\SymfonyUserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SymfonyCreateUser extends AbstractCommand
{
    public function __construct(
        private readonly TransactionManager $transactionManager,
        private readonly EventRegistry $eventRegistry,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly SymfonyUserRepository $userRepository
    ) {
        parent::__construct();
    }

    public function execute(string $email, string $plainPassword) : void
    {
        $this->transactionManager->begin();

        try {
            $id = $this->userRepository->nextIdentity();
            $user = DoctrineSymfonyUser::create($id, $email, $plainPassword);
            $this->eventRegistry->dequeueProviderAndRegister($user, $this);
            $encodedPassword = $this->hasher->hashPassword($user, $plainPassword);
            $user->changePassword($encodedPassword);
            $user->dequeueEvents();
            $this->userRepository->add($user);
        } catch (\Exception $e) {
            $this->transactionManager->rollback();
            throw $e;
        }

        $this->transactionManager->commit();
    }
}
