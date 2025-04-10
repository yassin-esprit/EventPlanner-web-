<?php

namespace App\Controller;

use App\Entity\CampagnesPublicitaire;
use App\Form\CampagnesPublicitaireType;
use App\Repository\CampagnesPublicitaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/campagnes/publicitaire')]
final class CampagnesPublicitaireController extends AbstractController{
    #[Route(name: 'app_campagnes_publicitaire_index', methods: ['GET'])]
    public function index(CampagnesPublicitaireRepository $campagnesPublicitaireRepository): Response
    {
        return $this->render('campagnes_publicitaire/index.html.twig', [
            'campagnes_publicitaires' => $campagnesPublicitaireRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_campagnes_publicitaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $campagnesPublicitaire = new CampagnesPublicitaire();
        $form = $this->createForm(CampagnesPublicitaireType::class, $campagnesPublicitaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($campagnesPublicitaire);
            $entityManager->flush();

            return $this->redirectToRoute('app_campagnes_publicitaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('campagnes_publicitaire/new.html.twig', [
            'campagnes_publicitaire' => $campagnesPublicitaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id_camp}', name: 'app_campagnes_publicitaire_show', methods: ['GET'])]
    public function show(CampagnesPublicitaire $campagnesPublicitaire): Response
    {
        return $this->render('campagnes_publicitaire/show.html.twig', [
            'campagnes_publicitaire' => $campagnesPublicitaire,
        ]);
    }

    #[Route('/{id_camp}/edit', name: 'app_campagnes_publicitaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CampagnesPublicitaire $campagnesPublicitaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CampagnesPublicitaireType::class, $campagnesPublicitaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_campagnes_publicitaire_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('campagnes_publicitaire/edit.html.twig', [
            'campagnes_publicitaire' => $campagnesPublicitaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id_camp}', name: 'app_campagnes_publicitaire_delete', methods: ['POST'])]
    public function delete(Request $request, CampagnesPublicitaire $campagnesPublicitaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$campagnesPublicitaire->getIdCamp(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($campagnesPublicitaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_campagnes_publicitaire_index', [], Response::HTTP_SEE_OTHER);
    }
}
