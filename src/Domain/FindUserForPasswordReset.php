<?php declare(strict_types=1);

namespace Becklyn\Security\Domain;

/**
 * @author Marko Vujnovic <mv@201created.de>
 *
 * @since  2020-04-29
 */
class FindUserForPasswordReset
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly HashPasswordResetToken $hashPasswordResetToken
    )
    {
    }

    /**
     * @throws PasswordResetExpiredException
     * @throws UserNotFoundException
     */
    public function execute(string $passwordResetToken, int $tokenExpirationMinutes) : User
    {
        $hashedToken = $this->hashPasswordResetToken->execute($passwordResetToken);

        $user = $this->userRepository->findOneByPasswordResetToken($hashedToken);

        if (!$user->isPasswordResetValid($hashedToken, $tokenExpirationMinutes)) {
            throw new PasswordResetExpiredException();
        }

        return $user;
    }
}
