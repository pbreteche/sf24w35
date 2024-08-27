<?php

namespace App\Twig\Components;

use App\Entity\Issue;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\VoteRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Thread
{
    public Issue $issue;

    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly VoteRepository $voteRepository,
    ) {
    }

    /**
     * @return Post[]
     */
    public function getPosts(): array
    {
        return $this->postRepository->findBy(['issue' => $this->issue], ['createdAt' => 'ASC']);
    }

    public function countVote(Post $post, bool $against = false): int
    {
        return $this->voteRepository->count(['message' => $post, 'against' => $against]);
    }
}
