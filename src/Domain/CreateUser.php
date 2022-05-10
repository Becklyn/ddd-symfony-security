<?php

namespace Becklyn\Security\Domain;

use Becklyn\Ddd\Events\Domain\EventRegistry;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-30
 */
class CreateUser
{
    private EventRegistry $eventRegistry;
    private UserFactory $userFactory;
    private EncodePasswordForUser $encodePasswordForUser;
    private UserRepository $userRepository;

    public function __construct(
        EventRegistry $eventRegistry,
        UserFactory $userFactory,
        EncodePasswordForUser $encodePasswordForUser,
        UserRepository $userRepository
    ) {
        $this->eventRegistry = $eventRegistry;
        $this->userFactory = $userFactory;
        $this->encodePasswordForUser = $encodePasswordForUser;
        $this->userRepository = $userRepository;
    }

    public function execute(UserId $id, string $email, string $plainPassword): void
    {
        $user = $this->userFactory->create($id, $email, $plainPassword);
        $this->eventRegistry->dequeueProviderAndRegister($user);

        $encodedPassword = $this->encodePasswordForUser->execute($user, $plainPassword);
        $user->changePassword($encodedPassword);
        $user->dequeueEvents(); // we don't actually want the PasswordChanged event being registered

        $this->userRepository->add($user);
    }
}
