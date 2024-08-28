<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a new user.',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $manager,
        private readonly UserQuestionFactory $factory,
        private readonly UserPasswordHasherInterface $hasher,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Email will be the unique display identifier')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $emailArg = $input->getArgument('email');

        if ($emailArg) {
            $violationList = $this->validator->validate($emailArg, new Email());
            if (0 < $violationList->count()) {
                $io->error($violationList);
            } else {
                $email = $emailArg;
            }
        }

        if (!isset($email)) {
            $email = $io->askQuestion($this->factory->createEmailQuestion());
        }
        $password = $io->askQuestion($this->factory->createPasswordQuestion());
        $roles = $io->askQuestion($this->factory->createRoleQuestion());
        $locale = $io->askQuestion($this->factory->createLocaleQuestion());

        $user = (new User())
            ->setEmail($email)
            ->setPassword($this->hasher->hashPassword($password))
            ->setRoles($roles)
            ->setLocale($locale);

        $violations = $this->validator->validate($user);
        if (0 < $violations->count()) {
            $io->error($violations);
        } else {
            $this->manager->persist($user);
            $this->manager->flush();
            $io->success(sprintf('User %s has been inserted', $user->getEmail()));
        }

        return Command::SUCCESS;
    }
}
