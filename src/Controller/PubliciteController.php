<?php

namespace App\Controller;

use App\Entity\Publicite;
use App\Form\PubliciteType;

use App\Repository\PubliciteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/publicite')]
final class PubliciteController extends AbstractController{
    #[Route(name: 'app_publicite_index', methods: ['GET'])]
    public function index(PubliciteRepository $publiciteRepository): Response
    {
        return $this->render('publicite/index.html.twig', [
            'publicites' => $publiciteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_publicite_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $publicite = new Publicite();
        $form = $this->createForm(PubliciteType::class, $publicite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($publicite);
            $entityManager->flush();

            return $this->redirectToRoute('app_publicite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('publicite/new.html.twig', [
            'publicite' => $publicite,
            'form' => $form,
            
        ]);
    }

    #[Route('/{id_pub}', name: 'app_publicite_show', methods: ['GET'])]
    public function show(Publicite $publicite): Response
    {
        return $this->render('publicite/show.html.twig', [
            'publicite' => $publicite,
        ]);
    }

    #[Route('/{id_pub}/edit', name: 'app_publicite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Publicite $publicite, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PubliciteType::class, $publicite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_publicite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('publicite/edit.html.twig', [
            'publicite' => $publicite,
            'form' => $form,
        ]);
    }

    #[Route('/{id_pub}', name: 'app_publicite_delete', methods: ['POST'])]
    public function delete(Request $request, Publicite $publicite, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$publicite->getId_pub(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($publicite);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_publicite_index', [], Response::HTTP_SEE_OTHER);
    }
}
