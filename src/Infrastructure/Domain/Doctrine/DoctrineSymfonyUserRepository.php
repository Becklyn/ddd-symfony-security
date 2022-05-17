<?php declare(strict_types=1);

namespace Becklyn\Security\Infrastructure\Domain\Doctrine;

use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserId;
use Becklyn\Security\Domain\UserNotFoundException;
use Becklyn\Security\Infrastructure\Domain\Symfony\SymfonyUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-03-03
 */
class DoctrineSymfonyUserRepository implements SymfonyUserRepository
{
    private readonly EntityRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(DoctrineSymfonyUser::class);
    }

    public function nextIdentity() : UserId
    {
        return UserId::next();
    }

    /**
     * @inheritDoc
     *
     * @param DoctrineSymfonyUser $user
     */
    public function add(User $user) : void
    {
        $this->entityManager->persist($user);
    }

    /**
     * @inheritDoc
     */
    public function findOneByEmail(string $email) : DoctrineSymfonyUser
    {
        /** @var DoctrineSymfonyUser|null $user */
        $user = $this->repository->findOneBy(['email' => $email]);

        if (null === $user) {
            throw new UserNotFoundException("User {$email} not found");
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function findOneByPasswordResetToken(string $token) : DoctrineSymfonyUser
    {
        /** @var DoctrineSymfonyUser|null $user */
        $user = $this->repository->findOneBy(['passwordResetToken' => $token]);

        if (null === $user) {
            throw new UserNotFoundException("No user with password reset token {$token} found");
        }

        return $user;
    }
}
