<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Vote;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VoteController extends AbstractController
{
    #[Route('/vote/for/{id}', requirements: ['id' => '\d+'], methods: 'POST')]
    #[IsGranted(
        attribute: new Expression('user !== subject.getCreatedBy()'),
        subject: 'post',
    )]
    public function vote(
        Post $post,
        Request $request,
        EntityManagerInterface $manager,
        ValidatorInterface $validator,
    ): Response {
        $choice = $request->request->get('choice');
        $vote = (new Vote())
            ->setVoter($this->getUser())
            ->setMessage($post)
            ->setAgainst('down' === $choice)
        ;
        $violations = $validator->validate($vote);
        $redirectResponse= $this->redirectToRoute('app_default_show', ['id' => $post->getIssue()->getId()]);

        if (0 === $violations->count()) {
            $manager->persist($vote);
            $manager->flush();
            $this->addFlash('success', 'Vote pris en compte.');

            return $redirectResponse;
        }

        foreach ($violations as $violation) {
            $this->addFlash('danger', $violation->getMessage());
        }

        return $redirectResponse;
    }
}