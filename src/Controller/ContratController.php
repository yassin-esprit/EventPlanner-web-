<?php

namespace App\Controller;

use App\Entity\Contrat;
use App\Form\ContratType;
use App\Repository\ContratRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/contrat')]
final class ContratController extends AbstractController
{
    private $emailService;
    private $logger;

    public function __construct(EmailService $emailService, \Psr\Log\LoggerInterface $logger)
    {
        $this->emailService = $emailService;
        $this->logger = $logger;
    }

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
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contrat);
            $entityManager->flush();
    
            // Préparer les données du contrat
            $sponsor = $contrat->getSponsor();
            $contratDetails = [
                'id_contrat' => $contrat->getId_contrat(),
                'type_contrat' => $contrat->getTypeContrat(),
                'montant' => $contrat->getMontant(),
                'description' => $contrat->getDescription(),
                'date_debut' => $contrat->getDateDebut(),
                'date_fin' => $contrat->getDateFin(),
                'statut' => $contrat->getStatut(),
                'mode_paiement' => $contrat->getModePaiement()
            ];
    
            try {
                $this->logger->debug('Avant envoi email notification contrat', [
                    'sponsorEmail' => $sponsor->getContactemail(),
                    'sponsorName' => $sponsor->getNom(),
                    'contratDetails' => $contratDetails
                ]);
                $emailSent = $this->emailService->sendContratNotification(
                    $sponsor->getContactemail(),
                    $sponsor->getNom(),
                    $contratDetails
                );
                $this->logger->debug('Après envoi email notification contrat', [
                    'emailSent' => $emailSent
                ]);
    
                if ($emailSent) {
                    $this->addFlash('success', 'Le contrat a été créé avec succès et une notification a été envoyée au sponsor.');
                } else {
                    $this->addFlash('warning', 'Le contrat a été créé, mais l\'envoi de la notification a échoué (retour FALSE).');
                    // Log ici si besoin
                }
            } catch (\Exception $e) {
                // En cas d'exception lors de l'envoi
                $this->addFlash('danger', 'Le contrat a été créé mais une erreur est survenue lors de l\'envoi de la notification : ' . $e->getMessage());
                
                // Si tu veux logger l'erreur :
                $this->logger->error('Erreur d\'envoi d\'email de notification pour le contrat ID '.$contrat->getId_contrat().': '.$e->getMessage());
            }
    
            return $this->redirectToRoute('app_contrat_index', [], Response::HTTP_SEE_OTHER);
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

    #[Route('/{id_contrat}/print', name: 'app_contrat_print')]
    public function print(Contrat $contrat): Response
    {
        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        // Generate HTML content
        $html = $this->renderView('contrat/print.html.twig', [
            'contrat' => $contrat,
        ]);

        // Load HTML into Dompdf
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render PDF
        $dompdf->render();

        // Generate filename
        $filename = sprintf('contrat_%s_%s.pdf', 
            $contrat->getSponsor()->getNom(),
            date('Y-m-d')
        );

        // Return PDF as response
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]
        );
    }
}
