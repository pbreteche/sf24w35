<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[UniqueEntity(
    fields: ['message', 'voter'],
    message: 'Vous ne pouvez voter deux fois pour le même message',
)]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $message = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $voter = null;

    #[ORM\Column]
    private ?bool $against = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?Post
    {
        return $this->message;
    }

    public function setMessage(?Post $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getVoter(): ?User
    {
        return $this->voter;
    }

    public function setVoter(?User $voter): static
    {
        $this->voter = $voter;

        return $this;
    }

    public function isAgainst(): ?bool
    {
        return $this->against;
    }

    public function setAgainst(bool $against): static
    {
        $this->against = $against;

        return $this;
    }
}
