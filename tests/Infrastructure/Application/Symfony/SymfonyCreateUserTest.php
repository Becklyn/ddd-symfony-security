<?php

namespace Becklyn\Security\Tests\Infrastructure\Application\Symfony;

use Becklyn\Ddd\Events\Domain\DomainEvent;
use Becklyn\Ddd\Events\Domain\EventProvider;
use Becklyn\Ddd\Events\Domain\DomainEventTestTrait;
use Becklyn\Ddd\Transactions\Application\TransactionManagerTestTrait;
use Becklyn\Security\Domain\UserCreated;
use Becklyn\Security\Domain\UserId;
use Becklyn\Security\Infrastructure\Application\Symfony\SymfonyCreateUser;
use Becklyn\Security\Infrastructure\Domain\Symfony\SymfonyUser;
use Becklyn\Security\Domain\UserTestTrait;
use Becklyn\Security\Infrastructure\Domain\Symfony\SymfonyUserTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Tightenco\Collect\Support\Collection;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-04-15
 */
class SymfonyCreateUserTest extends TestCase
{
    use ProphecyTrait;
    use TransactionManagerTestTrait;
    use DomainEventTestTrait;
    use SymfonyUserTestTrait;
    use UserTestTrait;

    /**
     * @var UserPasswordEncoderInterface|ObjectProphecy
     */
    private $encoder;

    private SymfonyCreateUser $fixture;

    protected function setUp(): void
    {
        $this->initTransactionManagerTestTrait();
        $this->initDomainEventTestTrait();
        $this->initSymfonyUserTestTrait();
        $this->encoder = $this->prophesize(UserPasswordEncoderInterface::class);

        $this->fixture = new SymfonyCreateUser(
            $this->transactionManager->reveal(),
            $this->eventRegistry->reveal(),
            $this->encoder->reveal(),
            $this->symfonyUserRepository->reveal()
        );
    }

    public function testUserWithPassedEmailAndEncodedPasswordIsAddedToRepositoryUserCreatedEventIsDequeuedAndTransactionCommitted(): void
    {
        $email = $this->givenAnUserEmail();
        $plaintextPassword = $this->givenAPlaintextPassword();
        $userId = $this->givenSymfonyUserRepositoryGeneratesAUserId();
        $encodedPassword = $this->givenThePlaintextPasswordIsEncoded($plaintextPassword);
        $this->givenTransactionIsBegun();;
        $this->thenUserCreatedEventWithUserIdAndEmailShouldBeDequeuedAndRegistered($userId, $email);
        $this->thenUserWithPassedEmailEncodedPasswordAndGeneratedIdShouldBeAddedToRepository($userId, $email, $encodedPassword);
        $this->thenTransactionShouldBeCommitted();
        $this->thenTransactionShouldNotBeRolledBack();
        $this->whenSymfonyCreateUserIsExecuted($email, $plaintextPassword);
    }

    private function givenAPlaintextPassword(): string
    {
        return uniqid();
    }

    private function givenThePlaintextPasswordIsEncoded(string $plaintextPassword): string
    {
        $encodedPassword = uniqid();
        $this->encoder->encodePassword(Argument::any(), $plaintextPassword)->willReturn($encodedPassword);
        return $encodedPassword;
    }

    private function thenUserCreatedEventWithUserIdAndEmailShouldBeDequeuedAndRegistered(UserId $userId, string $email): void
    {
        $this->thenEventRegistryShouldDequeueAndRegister(Argument::that(
            fn(EventProvider $provider) => Collection::make($provider->dequeueEvents())->contains(
                fn(DomainEvent $event) => $event instanceof UserCreated && $event->aggregateId()->equals($userId) && $event->email() === $email
            )
        ));
    }

    private function thenUserWithPassedEmailEncodedPasswordAndGeneratedIdShouldBeAddedToRepository(UserId $userId, string $email, string $encodedPassword): void
    {
        $this->thenSymfonyUserShouldBeAddedToRepository(Argument::that(
            fn(SymfonyUser $user) => $user->id()->equals($userId) && $user->email() === $email && $user->getPassword() === $encodedPassword
        ));
    }

    private function whenSymfonyCreateUserIsExecuted(string $email, string $plaintextPassword): void
    {
        $this->fixture->execute($email, $plaintextPassword);
    }

    public function testTransactionIsRolledBackAndExceptionIsThrownIfGeneratingUserIdThrowsException(): void
    {
        $this->givenTransactionIsBegun();
        $exception = $this->givenExceptionIsThrownWhileGeneratingUserId();
        $this->thenTransactionShouldBeRolledBack();
        $this->thenTransactionShouldNotBeCommitted();
        $this->expectException(get_class($exception));
        $this->whenSymfonyCreateUserIsExecuted($this->givenAnUserEmail(), $this->givenAPlaintextPassword());
    }

    private function givenExceptionIsThrownWhileGeneratingUserId(): \Exception
    {
        $exception = new \Exception();
        $this->symfonyUserRepository->nextIdentity()->willThrow($exception);
        return $exception;
    }
}
