<?php declare(strict_types=1);

namespace Becklyn\Security\Domain;

use Becklyn\Ddd\Events\Domain\EventRegistry;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-30
 */
class CreateUser
{
    public function __construct(
        private readonly EventRegistry $eventRegistry,
        private readonly UserFactory $userFactory,
        private readonly EncodePasswordForUser $encodePasswordForUser,
        private readonly UserRepository $userRepository
    )
    {
    }

    public function execute(UserId $id, string $email, string $plainPassword) : void
    {
        $user = $this->userFactory->create($id, $email, $plainPassword);
        $this->eventRegistry->dequeueProviderAndRegister($user);

        $encodedPassword = $this->encodePasswordForUser->execute($user, $plainPassword);
        $user->changePassword($encodedPassword);
        $user->dequeueEvents(); // we don't actually want the PasswordChanged event being registered

        $this->userRepository->add($user);
    }
}
