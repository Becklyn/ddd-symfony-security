<?php

namespace Becklyn\Security\Infrastructure\Domain\Symfony;

use Becklyn\Security\Domain\NotifyPasswordReset;
use Becklyn\Security\Domain\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-27
 */
class SymfonyMailerNotifyPasswordReset implements NotifyPasswordReset
{
    private MailerInterface $mailer;
    private UrlGeneratorInterface $router;
    private string $passwordResetRoute;
    private string $resetEmailFrom;
    private string $resetEmailSubject;

    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $router, string $passwordResetRoute, string $resetEmailFrom, string $resetEmailSubject)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->passwordResetRoute = $passwordResetRoute;
        $this->resetEmailFrom = $resetEmailFrom;
        $this->resetEmailSubject = $resetEmailSubject;
    }

    public function execute(User $user, string $passwordResetToken): void
    {
        $errors = [];
        if (empty($this->passwordResetRoute)) {
            $errors[] = 'reset_password.route';
        }
        if (empty($this->resetEmailFrom)) {
            $errors[] = 'reset_password.email_from';
        }
        if (empty($this->resetEmailSubject)) {
            $errors[] = 'reset_password.email_subject';
        }
        if (!empty($errors)) {
            throw new \Exception('The following configuration options are not set in becklyn_security.yaml: ' . implode(', ', $errors));
        }

        $route = $this->router->generate($this->passwordResetRoute, ['token' => $passwordResetToken], UrlGeneratorInterface::ABSOLUTE_URL);

        $email = (new Email())
            ->from($this->resetEmailFrom)
            ->to($user->email())
            ->subject($this->resetEmailSubject)
            // TODO insert proper text here and extract it into a twig/inky template
            ->text("Sie haben ein Passwort-Reset angefordert. Bitte besuchen sie folgendes URL: $route")
            ->html("<p>Sie haben ein Passwort-Reset angefordert. Bitte besuchen sie folgendes URL:</p><p><a href='$route'>$route</a></p>");

        $this->mailer->send($email);
    }
}
