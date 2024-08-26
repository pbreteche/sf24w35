<?php

namespace App\Controller;

use App\Entity\Enum\IssueState;
use App\Entity\Issue;
use App\Form\IssueType;
use App\Repository\IssueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
        Request $request,
        EntityManagerInterface $manager,
    ): Response {
        $issue = new Issue();
        $form = $this->createForm(IssueType::class, $issue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($issue);
            $manager->flush();
            $this->addFlash('success', 'L\'issue a été enregistrée.');

            return $this->redirectToRoute('app_default_index');
        }

        return $this->render('default/new.html.twig', [
            'form' => $form,
        ]);
    }
}
