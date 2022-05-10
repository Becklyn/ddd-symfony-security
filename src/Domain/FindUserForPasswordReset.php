<?php

namespace Becklyn\Security\Domain;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-29
 */
class FindUserForPasswordReset
{
    private UserRepository $userRepository;
    private HashPasswordResetToken $hashPasswordResetToken;

    public function __construct(UserRepository $userRepository, HashPasswordResetToken $hashPasswordResetToken)
    {
        $this->userRepository = $userRepository;
        $this->hashPasswordResetToken = $hashPasswordResetToken;
    }

    /**
     * @throws PasswordResetExpiredException
     * @throws UserNotFoundException
     */
    public function execute(string $passwordResetToken, int $tokenExpirationMinutes): User
    {
        $hashedToken = $this->hashPasswordResetToken->execute($passwordResetToken);

        $user = $this->userRepository->findOneByPasswordResetToken($hashedToken);
        if (!$user->isPasswordResetValid($hashedToken, $tokenExpirationMinutes)) {
            throw new PasswordResetExpiredException();
        }

        return $user;
    }
}
