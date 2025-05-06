<?php

namespace App\Controller;
use App\Service\SponsorInfoService;
use Psr\Log\LoggerInterface;
use App\Entity\Sponsor;
use App\Form\Sponsor1Type;
use App\Repository\SponsorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sponsor/c')]
final class SponsorCController extends AbstractController
{
    private $sponsorInfoService;
    private $logger;
    public function __construct(SponsorInfoService $sponsorInfoService, LoggerInterface $logger)
    {
        $this->sponsorInfoService = $sponsorInfoService;
        $this->logger = $logger;
    }
    #[Route('/', name: 'app_sponsor_c_index', methods: ['GET'])]
    public function index(Request $request, SponsorRepository $sponsorRepository): Response
    {
        // Nombre de sponsors par page
        $limit = 6;
        
        // Récupérer la page actuelle depuis les paramètres de requête
        $page = $request->query->getInt('page', 1);
    
        // Compter le nombre total de sponsors
        $totalSponsors = $sponsorRepository->count([]);
        
        // Compter les sponsors actifs et inactifs
        $activeSponsors = $sponsorRepository->count(['statut' => 'Actif']);
        $inactiveSponsors = $sponsorRepository->count(['statut' => 'Inactif']);
        
        // Compter le nombre total de contrats
        $totalContrats = $sponsorRepository->createQueryBuilder('s')
            ->select('COUNT(c.id_contrat)')
            ->leftJoin('s.contrats', 'c')
            ->getQuery()
            ->getSingleScalarResult();
        
        // Calculer le nombre total de pages
        $totalPages = max(ceil($totalSponsors / $limit), 1);
    
        // Calculer l'offset en fonction de la page courante
        $offset = ($page - 1) * $limit;
    
        // Récupérer les sponsors pour la page actuelle
        $sponsors = $sponsorRepository->findBy([], null, $limit, $offset);
        
        return $this->render('sponsor_c/index.html.twig', [
            'sponsors' => $sponsors,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalSponsors' => $totalSponsors,
            'activeSponsors' => $activeSponsors,
            'inactiveSponsors' => $inactiveSponsors,
            'totalContrats' => $totalContrats
        ]);
    }
    

#[Route('/info/{id_sponsor}', name: 'app_sponsor_info', methods: ['GET'], requirements: ['id_sponsor' => '\d+'])]
public function getSponsorInfo(int $id_sponsor, SponsorRepository $sponsorRepository): JsonResponse
    {
        $sponsor = $sponsorRepository->find($id_sponsor);
        
        if (!$sponsor) {
            return $this->json([
                'success' => false,
                'error' => 'Sponsor non trouvé'
            ], 404);
        }

        $result = $this->sponsorInfoService->getSponsorInfo(
            $sponsor->getNom(),
            $sponsor->getSecteuractivite() ?? 'Non spécifié'
        );

        return $this->json($result);
    }

    #[Route('/new', name: 'app_sponsor_c_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sponsor = new Sponsor();
        $form = $this->createForm(Sponsor1Type::class, $sponsor);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
                return $this->render('sponsor_c/new.html.twig', [
                    'sponsor' => $sponsor,
                    'form' => $form,
                ]);
            }

            // Handle logo file upload
            $logoFile = $form->get('logo')->getData();
            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                if (function_exists('transliterator_transliterate')) {
                    $safeFilename = \transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                } else {
                    // Fallback if intl extension is not enabled
                    $safeFilename = preg_replace('/[^A-Za-z0-9_]/', '', strtolower($originalFilename));
                }
                $newFilename = $safeFilename.'-'.uniqid().'.'.$logoFile->guessExtension();

                try {
                    $logoFile->move(
                        $this->getParameter('sponsors_directory'),
                        $newFilename
                    );
                    $sponsor->setLogo($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du logo.');
                }
            }

            try {
                $entityManager->persist($sponsor);
                $entityManager->flush();
                $this->addFlash('success', 'Le sponsor a été créé avec succès.');
                return $this->redirectToRoute('app_sponsor_c_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création du sponsor.');
            }
        }

        return $this->render('sponsor_c/new.html.twig', [
            'sponsor' => $sponsor,
            'form' => $form,
        ]);
    }

    #[Route('/{id_sponsor}', name: 'app_sponsor_c_show', methods: ['GET'])]
    public function show(Sponsor $sponsor): Response
    {
        return $this->render('sponsor_c/show.html.twig', [
            'sponsor' => $sponsor,
        ]);
    }

    #[Route('/{id_sponsor}/edit', name: 'app_sponsor_c_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sponsor $sponsor, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Sponsor1Type::class, $sponsor);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
                return $this->render('sponsor_c/edit.html.twig', [
                    'sponsor' => $sponsor,
                    'form' => $form,
                ]);
            }

            // Handle logo file upload
            $logoFile = $form->get('logo')->getData();
            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = \transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$logoFile->guessExtension();

                try {
                    $logoFile->move(
                        $this->getParameter('sponsors_directory'),
                        $newFilename
                    );
                    $sponsor->setLogo($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du logo.');
                }
            }

            try {
                $entityManager->flush();
                $this->addFlash('success', 'Le sponsor a été mis à jour avec succès.');
                return $this->redirectToRoute('app_sponsor_c_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour du sponsor.');
            }
        }

        return $this->render('sponsor_c/edit.html.twig', [
            'sponsor' => $sponsor,
            'form' => $form,
        ]);
    }

    #[Route('/{id_sponsor}', name: 'app_sponsor_c_delete', methods: ['POST'])]
    public function delete(Request $request, Sponsor $sponsor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sponsor->getId_sponsor(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sponsor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sponsor_c_index', [], Response::HTTP_SEE_OTHER);
    }
}
