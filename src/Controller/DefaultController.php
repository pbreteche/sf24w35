<?php

namespace App\Controller;

use App\Entity\Enum\IssueState;
use App\Entity\Issue;
use App\Entity\Post;
use App\Form\IssueType;
use App\Form\PostType;
use App\Repository\IssueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DefaultController extends AbstractController
{
    #[Route('/', methods: 'GET')]
    public function index(
        Request $request,
        IssueRepository $repository,
    ): Response {
        $search = $request->query->get('search');
        $issues = $repository->findByTitleLike($search);

        return $this->render('default/index.html.twig', [
            'issues' => $issues,
            'search' => $search,
        ]);
    }

    #[Route('/new')]
    #[IsGranted('IS_VERIFIED')]
    public function new(
        Request $request,
        EntityManagerInterface $manager,
    ): Response {
        $issue = new Issue();
        $issue->setCreatedBy($this->getUser());
        $form = $this->createForm(IssueType::class, $issue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($issue);
            $manager->flush();
            $this->addFlash('success', 'L\'issue a été enregistrée.');

            return $this->redirectToRoute('app_default_show', ['id' => $issue->getId()]);
        }

        return $this->render('default/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('IS_VERIFIED')]
    #[IsGranted(
        attribute: new Expression('user === subject.getCreatedBy()'),
        subject: 'issue',
    )]
    public function edit(
        Issue $issue,
        Request $request,
        EntityManagerInterface $manager,
    ): Response {
        $form = $this->createForm(IssueType::class, $issue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
            $this->addFlash('success', 'L\'issue a été mis à jour.');

            return $this->redirectToRoute('app_default_show', ['id' => $issue->getId()]);
        }

        return $this->render('default/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function show(
        Issue $issue,
        Request $request,
        EntityManagerInterface $manager,
    ): Response {
        $post = new Post();
        $post
            ->setCreatedBy($this->getUser())
            ->setIssue($issue)
        ;
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($post);
            $manager->flush();

            $this->addFlash('success', 'Message enregistré');

            return $this->redirectToRoute('app_default_show', ['id' => $issue->getId()]);
        }

        return $this->render('default/show.html.twig', [
            'issue' => $issue,
            'form' => $form,
        ]);
    }
}
