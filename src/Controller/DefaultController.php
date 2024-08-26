<?php

namespace App\Controller;

use App\Entity\Enum\IssueState;
use App\Entity\Issue;
use App\Form\IssueType;
use App\Repository\IssueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', methods: 'GET')]
    public function index(
        IssueRepository $repository,
    ): Response {
        $issues = $repository->findBy(['state' => IssueState::open], ['createdAt' => 'DESC'], 20);

        return $this->render('default/index.html.twig', [
            'issues' => $issues,
        ]);
    }

    #[Route('/new')]
    public function new(

    ): Response {
        $issue = new Issue();
        $form = $this->createForm(IssueType::class, $issue);

        return $this->render('default/new.html.twig', [
            'form' => $form,
        ]);
    }
}
