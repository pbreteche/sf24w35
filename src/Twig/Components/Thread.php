<?php

namespace App\Twig\Components;

use App\Entity\Issue;
use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\VoteRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Thread
{
    public Issue $issue;

    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly VoteRepository $voteRepository,
        private readonly Security $security,
    ) {
    }

    /**
     * @return Post[]
     */
    public function getPosts(): array
    {
        if (!isset($this->issue)) {
            throw new \InvalidArgumentException('Issue parameter should not be empty');
        }

        return $this->postRepository->findBy(['issue' => $this->issue], ['createdAt' => 'ASC']);
    }

    public function countVote(Post $post, bool $against = false): int
    {
        return $this->voteRepository->count(['message' => $post, 'against' => $against]);
    }

    public function canVote(Post $post): bool
    {
        if (!$this->security->isGranted('IS_VERIFIED')) {
            return false;
        }

        $user = $this->security->getUser();
        // Users cannot vote for their own message
        if ($post->getCreatedBy() === $user) {
            return false;
        }

        // Users cannot vote twice for the same message
        $existingVote = $this->voteRepository->count(['voter' => $user, 'message' => $post]);
        if ($existingVote) {
            return false;
        }

        return true;
    }
}
