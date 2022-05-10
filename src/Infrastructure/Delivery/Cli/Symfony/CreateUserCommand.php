<?php

namespace Becklyn\Security\Infrastructure\Delivery\Cli\Symfony;

use Becklyn\Security\Application\CreateUser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-03-02
 */
class CreateUserCommand extends Command
{
    private CreateUser $createUser;

    protected function configure()
    {
        $this
            ->setName('becklyn:security:create-user')
            ->addArgument('email', InputArgument::REQUIRED, "user's email")
            ->addArgument('password', InputArgument::REQUIRED, "user's password")
        ;
    }

    public function __construct(CreateUser $createUser)
    {
        parent::__construct();
        $this->createUser = $createUser;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $this->createUser->execute($email, $password);

        return 0;
    }
}
