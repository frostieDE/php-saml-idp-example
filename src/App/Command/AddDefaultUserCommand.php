<?php

namespace App\Command;

use App\Application;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddDefaultUserCommand extends Command {
    public function configure() {
        $this
            ->setName('app:create-admin')
            ->setDescription('Creates an admin user');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        /** @var Application $app */
        $app = $this->getSilexApplication();
        /** @var EntityManager $em */
        $em = $app['orm.em'];

        $user = (new User())
            ->setUsername('admin')
            ->setRoles(['ROLE_ADMIN'])
            ->setLastname('John')
            ->setFirstname('Doe')
            ->setEmail('email@localhost.tld');

        $user->setPassword($app->encodePassword($user, 'test1234'));
        $em->persist($user);
        $em->flush();

        $output->writeln('OK');
    }
}