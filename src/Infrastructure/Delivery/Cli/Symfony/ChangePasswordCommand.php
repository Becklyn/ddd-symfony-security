<?php

namespace Becklyn\Security\Infrastructure\Delivery\Cli\Symfony;

use Becklyn\Security\Application\ChangePassword;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Marko Vujnovic <mv@201created.de>
 * @since  2020-03-03
 */
class ChangePasswordCommand extends Command
{
    private ChangePassword $changePassword;

    protected function configure()
    {
        $this
            ->setName('becklyn:security:change-password')
            ->addArgument('email', InputArgument::REQUIRED, 'email of user whose password to change')
            ->addArgument('password', InputArgument::REQUIRED, 'new password to set')
        ;
    }

    public function __construct(ChangePassword $changePassword)
    {
        parent::__construct();
        $this->changePassword = $changePassword;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $this->changePassword->execute($email, $password);

        return 0;
    }
}
