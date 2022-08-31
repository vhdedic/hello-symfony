<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentFormType;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    public function __construct(EntityManagerInterface $em, PostRepository $postRepository)
    {
        $this->em = $em;
        $this->postRepository = $postRepository;
    }
    
    #[Route('/', name: 'app_post_index')]
    public function index(): Response
    {
        $posts = $this->postRepository->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/create', name: 'app_post_create')]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $post = new Post;

        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $post->setTitle($form->get('title')->getData());
            $post->setBody($form->get('body')->getData());
            $post->setUser($this->getUser());
            
            $this->em->persist($post);
            $this->em->flush();

            return $this->redirectToRoute('app_post_index');
        } 

        return $this->render('post/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/post/show/{id}', name: 'app_post_show')]
    public function show($id, Request $request): Response
    {
        $post = $this->postRepository->find($id);
        $comments = $post->getComments();

        $newComment = new Comment;

        $form = $this->createForm(CommentFormType::class, $newComment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newComment->setBody($form->get('body')->getData());
            $newComment->setPost($post);
            $newComment->setUser($this->getUser());

            $this->em->persist($newComment);
            $this->em->flush();

            return $this->redirect($this->generateUrl('app_post_show', [
                    'id' => $id
                ])
            );
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $comments,
            'form' => $form->createView()
        ]);

    }

    #[Route('/post/edit/{id}', name: 'app_post_edit')]
    public function edit($id, Request $request): Response
    {
        $post = $this->postRepository->find($id);

        if ($post->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $post->setTitle($form->get('title')->getData());
            $post->setBody($form->get('body')->getData());
            $post->setUser($this->getUser());
            
            $this->em->persist($post);
            $this->em->flush();

            return $this->redirect($this->generateUrl('app_post_show', [
                'id' => $id
            ]));
        } 
        
        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }

    #[Route('/post/delete/{id}', methods: ['GET', 'DELETE'], name: 'app_post_delete')]
    public function delete($id): Response
    {
        $post = $this->postRepository->find($id);

        if ($post->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        
        $this->em->remove($post);
        $this->em->flush();

        return $this->redirectToRoute('app_post_index');
    }
}
