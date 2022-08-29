<?php

namespace App\Controller;

use App\Entity\Post;
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
        return $this->render('post/index.html.twig');
    }

    #[Route('/post/create', name: 'app_post_create')]
    public function create(Request $request): Response
    {
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
}
