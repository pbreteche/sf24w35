<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validation;
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
            $violationList = $this->validator->validate($emailArg, [
                new Email(),
            ]);

            if (0 < $violationList->count()) {
                foreach ($violationList as $violation) {
                    $io->error($violation->getMessage());
                }
            } else {
                $email = $emailArg;
            }
        }

        if (!isset($email)) {
            $emailQuestion = new Question('User email');
            $emailValidation = Validation::createCallable(
                $this->validator,
                new NotBlank(),
                new Email(),
            );
            $emailQuestion->setValidator($emailValidation);
            $emailQuestion->setMaxAttempts(10);
            $email = $io->askQuestion($emailQuestion);
        }

        $passwordQuestion = new Question('User password');
        $passwordQuestion->setHidden(true);
        $passwordValidation = Validation::createCallable(
            $this->validator,
            new NotBlank(),
            new NotCompromisedPassword(),
            new PasswordStrength(minScore: PasswordStrength::STRENGTH_STRONG),
        );
        $passwordQuestion->setValidator($passwordValidation);
        $passwordQuestion->setMaxAttempts(10);
        $password = $io->askQuestion($passwordQuestion);

        $roleQuestion = new ChoiceQuestion('User role', ['ROLE_USER', 'ROLE_DEVELOPER', 'ROLE_ADMIN']);
        $roleQuestion->setMultiselect(true);
        $roles = $io->askQuestion($roleQuestion);

        $localeQuestion = new Question('Locale');
        $localeValidation = Validation::createCallable(
            $this->validator,
            new NotBlank(),
            new Regex(pattern: '/^[a-z]{2}$/', message: 'Locale should be 2 lowercase letters')
        );
        $localeQuestion->setValidator($localeValidation);
        $locale = $io->askQuestion($localeQuestion);


        $user = (new User())
            ->setEmail($email)
            ->setPassword($password)
            ->setRoles($roles)
            ->setLocale($locale)
        ;

        $violations = $this->validator->validate($user);
        foreach ($violations as $violation) {
            $io->error(sprintf('The field %s is invalid. Message: %s.',
                $violation->getPropertyPath(), $violation->getMessage()
            ));
        }

        if (0 === $violations->count()) {
            $this->manager->persist($user);
            $this->manager->flush();
            $io->success(sprintf('User %s has been inserted', $user->getEmail()));
        }

        return Command::SUCCESS;
    }
}
