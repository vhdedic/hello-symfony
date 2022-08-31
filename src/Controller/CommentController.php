<?php

namespace App\Controller;

use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    public function __construct(EntityManagerInterface $em, CommentRepository $commentRepository)
    {
        $this->em = $em;
        $this->commentRepository = $commentRepository;
    }

    #[Route('/comment/edit/{id}', name: 'app_comment_edit')]
    public function edit($id, Request $request): Response
    {
        $comment = $this->commentRepository->find($id);

        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setBody($form->get('body')->getData());
            $comment->setPost($comment->getPost());
            $comment->setUser($comment->getUser());

            $this->em->persist($comment);
            $this->em->flush();

            return $this->redirect($this->generateUrl('app_post_show', [
                'id' => $comment->getPost()->getId()
            ]));
        }

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView()
        ]);
    }

    #[Route('/comment/delete/{id}', methods: ['GET', 'DELETE'], name: 'app_comment_delete')]
    public function delete($id): Response
    {
        $comment = $this->commentRepository->find($id);

        $this->em->remove($comment);
        $this->em->flush();

        return $this->redirect($this->generateUrl('app_post_show', [
            'id' => $comment->getPost()->getId()
        ]));
    }
}
