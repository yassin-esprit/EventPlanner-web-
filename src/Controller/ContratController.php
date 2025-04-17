<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Form\ContratType;
use App\Repository\ContratRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contrat')]
final class ContratController extends AbstractController
{
    #[Route('/', name: 'app_contrat_index', methods: ['GET'])]
    public function index(ContratRepository $contratRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 6;

        $contrats = $contratRepository->findPaginated($page, $limit);
        $totalContrats = $contratRepository->count([]);
        $totalPages = ceil($totalContrats / $limit);

        // Calcul des statistiques
        $activeContrats = $contratRepository->countActiveContrats();
        $inactiveContrats = $contratRepository->countInactiveContrats();
        $totalMontant = $contratRepository->getTotalMontant();

        return $this->render('contrat/index.html.twig', [
            'contrats' => $contrats,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalContrats' => $totalContrats,
            'activeContrats' => $activeContrats,
            'inactiveContrats' => $inactiveContrats,
            'totalMontant' => $totalMontant,
        ]);
    }

    #[Route('/new', name: 'app_contrat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contrat = new Contrat();
        $form = $this->createForm(ContratType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
                return $this->render('contrat/new.html.twig', [
                    'contrat' => $contrat,
                    'form' => $form,
                ]);
            }

            try {
                $entityManager->persist($contrat);
                $entityManager->flush();
                $this->addFlash('success', 'Le contrat a été créé avec succès.');
                return $this->redirectToRoute('app_contrat_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création du contrat.');
            }
        }

        return $this->render('contrat/new.html.twig', [
            'contrat' => $contrat,
            'form' => $form,
        ]);
    }

    #[Route('/{id_contrat}', name: 'app_contrat_show', methods: ['GET'])]
    public function show(Contrat $contrat): Response
    {
        return $this->render('contrat/show.html.twig', [
            'contrat' => $contrat,
        ]);
    }
   // src/Controller/ContratController.php


    #[Route('/contrat/stats', name: 'app_contrat_stats')]
    public function statistiques(ContratRepository $contratRepository): Response
    {
        $contrats = $contratRepository->findAll();

        $sponsorsBySecteur = [];
        $contractIntervals = [];

        foreach ($contrats as $contrat) {
            $sponsor = $contrat->getSponsor();
            $secteur = $sponsor->getSecteurActivite();
            $nomSponsor = $sponsor->getNom();

            // Graph 1
            if (!isset($sponsorsBySecteur[$secteur])) {
                $sponsorsBySecteur[$secteur] = [];
            }
            if (!in_array($nomSponsor, $sponsorsBySecteur[$secteur])) {
                $sponsorsBySecteur[$secteur][] = $nomSponsor;
            }

            // Graph 2
            $contractIntervals[] = [
                'sponsor' => $nomSponsor,
                'start' => $contrat->getDateDebut()->format('Y-m-d'),
                'end' => $contrat->getDateFin()->format('Y-m-d'),
            ];
        }

        return $this->render('contrat/chart.html.twig', [
            'sponsorsBySecteur' => $sponsorsBySecteur,
            'contractIntervals' => $contractIntervals,
        ]);
    }


    
    #[Route('/{id_contrat}/edit', name: 'app_contrat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Contrat $contrat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContratType::class, $contrat);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
                return $this->render('contrat/edit.html.twig', [
                    'contrat' => $contrat,
                    'form' => $form,
                ]);
            }

            try {
                $entityManager->flush();
                $this->addFlash('success', 'Le contrat a été mis à jour avec succès.');
                return $this->redirectToRoute('app_contrat_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour du contrat.');
            }
        }

        return $this->render('contrat/edit.html.twig', [
            'contrat' => $contrat,
            'form' => $form,
        ]);
    }

    #[Route('/{id_contrat}', name: 'app_contrat_delete', methods: ['POST'])]
    public function delete(Request $request, Contrat $contrat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contrat->getId_contrat(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($contrat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_contrat_index', [], Response::HTTP_SEE_OTHER);
    }
}
