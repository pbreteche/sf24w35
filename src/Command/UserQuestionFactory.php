<?php

namespace App\Command;

use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class UserQuestionFactory
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function createRoleQuestion(): Question
    {
        return (new ChoiceQuestion('User role', ['ROLE_USER', 'ROLE_DEVELOPER', 'ROLE_ADMIN']))
            ->setMultiselect(true);
    }

    public function createEmailQuestion(): Question
    {
        return (new Question('User email'))
            ->setValidator(
                Validation::createCallable(
                    $this->validator,
                    new Assert\NotBlank(),
                    new Assert\Email(),
                )
            )
            ->setMaxAttempts(10);
    }

    public function createPasswordQuestion(): Question
    {
        return (new Question('User password'))
            ->setHidden(true)
            ->setValidator(
                Validation::createCallable(
                    $this->validator,
                    new Assert\NotBlank(),
                    new Assert\NotCompromisedPassword(),
                    new Assert\PasswordStrength(minScore: Assert\PasswordStrength::STRENGTH_STRONG),
                )
            )
            ->setMaxAttempts(10);
    }

    public function createLocaleQuestion(): Question
    {
        return (new Question('Locale'))
            ->setValidator(
                Validation::createCallable(
                    $this->validator,
                    new Assert\NotBlank(),
                    new Assert\Regex(pattern: '/^[a-z]{2}$/', message: 'Locale should be 2 lowercase letters')
                )
            );
    }
}
