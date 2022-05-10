<?php

namespace Becklyn\Security\Tests\Infrastructure\Domain\Symfony;

use Becklyn\Security\Domain\User;
use Becklyn\Security\Domain\UserTestTrait;
use Becklyn\Security\Infrastructure\Domain\Symfony\SymfonyMailerNotifyPasswordReset;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SymfonyMailerNotifyPasswordResetTest extends TestCase
{
    use ProphecyTrait;
    use UserTestTrait;

    /**
     * @var ObjectProphecy|MailerInterface
     */
    private ObjectProphecy $mailer;
    /**
     * @var ObjectProphecy|UrlGeneratorInterface
     */
    private ObjectProphecy $urlGenerator;
    private string $passwordResetRoute;
    private string $resetEmailFrom;
    private string $resetEmailSubject;
    private SymfonyMailerNotifyPasswordReset $fixture;

    protected function setUp(): void
    {
        $this->mailer = $this->prophesize(MailerInterface::class);
        $this->urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
        $this->passwordResetRoute = uniqid();
        $this->resetEmailFrom = uniqid() . '@' . uniqid() . '.com';
        $this->resetEmailSubject = uniqid();
        $this->fixture = new SymfonyMailerNotifyPasswordReset(
            $this->mailer->reveal(),
            $this->urlGenerator->reveal(),
            $this->passwordResetRoute,
            $this->resetEmailFrom,
            $this->resetEmailSubject
        );
    }

    public function testEmailIsSentFromConfiguredAddress(): void
    {
        $token = $this->givenAPasswordResetToken();
        $email = $this->givenAnUserEmail();
        $user = $this->givenAUser();
        $this->givenUserHasEmail($user, $email);
        $this->givenResetUrlIsGeneratedWithToken($token);
        $this->thenEmailWillBeSentFromConfiguredAddress();
        $this->whenNotifyPasswordResetIsExecuted($user->reveal(), $token);
    }

    private function givenResetUrlIsGeneratedWithToken(string $token): string
    {
        $url = uniqid();
        $this->urlGenerator->generate($this->passwordResetRoute, ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL)->willReturn($url);
        return $url;
    }

    private function thenEmailWillBeSentFromConfiguredAddress(): void
    {
        $this->mailer->send(Argument::that(fn(Email $email) => $email->getFrom()[0]->getAddress() === $this->resetEmailFrom))->shouldBeCalled();
    }

    private function whenNotifyPasswordResetIsExecuted(User $user, string $token): void
    {
        $this->fixture->execute($user, $token);
    }

    public function testEmailIsSentToUsersEmail(): void
    {
        $token = $this->givenAPasswordResetToken();
        $email = $this->givenAnUserEmail();
        $user = $this->givenAUser();
        $this->givenUserHasEmail($user, $email);
        $this->givenResetUrlIsGeneratedWithToken($token);
        $this->thenEmailWillBeSentToUsersEmail($email);
        $this->whenNotifyPasswordResetIsExecuted($user->reveal(), $token);
    }

    private function thenEmailWillBeSentToUsersEmail(string $usersEmail): void
    {
        $this->mailer->send(Argument::that(fn(Email $email) => $email->getTo()[0]->getAddress() === $usersEmail))->shouldBeCalled();
    }

    public function testEmailIsSentWithConfiguredSubject(): void
    {
        $token = $this->givenAPasswordResetToken();
        $email = $this->givenAnUserEmail();
        $user = $this->givenAUser();
        $this->givenUserHasEmail($user, $email);
        $this->givenResetUrlIsGeneratedWithToken($token);
        $this->thenEmailWillBeSentWithConfiguredSubject();
        $this->whenNotifyPasswordResetIsExecuted($user->reveal(), $token);
    }

    private function thenEmailWillBeSentWithConfiguredSubject(): void
    {
        $this->mailer->send(Argument::that(fn(Email $email) => $email->getSubject() === $this->resetEmailSubject))->shouldBeCalled();
    }

    public function testEmailIsSentWithTextAndHtmlBodiesContainingResetUrl(): void
    {
        $token = $this->givenAPasswordResetToken();
        $email = $this->givenAnUserEmail();
        $user = $this->givenAUser();
        $this->givenUserHasEmail($user, $email);
        $resetUrl = $this->givenResetUrlIsGeneratedWithToken($token);
        $this->thenEmailWillBeSentWithTextAndHtmlBodiesContainingResetUrl($resetUrl);
        $this->whenNotifyPasswordResetIsExecuted($user->reveal(), $token);
    }

    private function thenEmailWillBeSentWithTextAndHtmlBodiesContainingResetUrl(string $resetUrl): void
    {
        $this->mailer->send(Argument::that(fn(Email $email) => strpos($email->getTextBody(), $resetUrl) !== false && strpos($email->getHtmlBody(), $resetUrl) !== false))->shouldBeCalled();
    }

    public function testExceptionIsThrownAndNoMailIsSentIfPasswordResetRouteIsNotConfigured(): void
    {
        $this->fixture = new SymfonyMailerNotifyPasswordReset(
            $this->mailer->reveal(),
            $this->urlGenerator->reveal(),
            '',
            $this->resetEmailFrom,
            $this->resetEmailSubject
        );

        $token = $this->givenAPasswordResetToken();
        $user = $this->givenAUser();
        $this->thenNoEmailShouldBeSent();
        $this->expectException(\Exception::class);
        $this->whenNotifyPasswordResetIsExecuted($user->reveal(), $token);
    }

    private function thenNoEmailShouldBeSent(): void
    {
        $this->mailer->send(Argument::any())->shouldNotBeCalled();
    }

    public function testExceptionIsThrownAndNoMailIsSentIfResetEmailFromIsNotConfigured(): void
    {
        $this->fixture = new SymfonyMailerNotifyPasswordReset(
            $this->mailer->reveal(),
            $this->urlGenerator->reveal(),
            $this->passwordResetRoute,
            '',
            $this->resetEmailSubject
        );

        $token = $this->givenAPasswordResetToken();
        $user = $this->givenAUser();
        $this->thenNoEmailShouldBeSent();
        $this->expectException(\Exception::class);
        $this->whenNotifyPasswordResetIsExecuted($user->reveal(), $token);
    }

    public function testExceptionIsThrownAndNoMailIsSentIfResetEmailSubjectIsNotConfigured(): void
    {
        $this->fixture = new SymfonyMailerNotifyPasswordReset(
            $this->mailer->reveal(),
            $this->urlGenerator->reveal(),
            $this->passwordResetRoute,
            $this->resetEmailFrom,
            ''
        );

        $token = $this->givenAPasswordResetToken();
        $user = $this->givenAUser();
        $this->thenNoEmailShouldBeSent();
        $this->expectException(\Exception::class);
        $this->whenNotifyPasswordResetIsExecuted($user->reveal(), $token);
    }
}
