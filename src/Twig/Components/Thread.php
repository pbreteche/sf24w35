<?php

namespace App\Twig\Components;

use App\Entity\Issue;
use App\Repository\PostRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Thread
{
    public Issue $issue;

    public function __construct(
        private readonly PostRepository $postRepository,
    ) {
    }

    public function getPosts(): array
    {
        return $this->postRepository->findBy(['issue' => $this->issue], ['createdAt' => 'ASC']);
    }
}
