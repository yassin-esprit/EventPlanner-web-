<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Equipement;
use App\Repository\EvenementRepository;
use App\Repository\EquipementRepository;
use App\Repository\ReservationEquipementRepository;
use App\Repository\PubliciteRepository;
use App\Repository\CampagnesPublicitaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
<<<<<<< HEAD
use App\Entity\Evenement;
use App\Repository\SponsorRepository;
use App\Repository\ContratRepository;
=======
use Symfony\Component\HttpFoundation\Request;
use App\Service\BadgeService;
>>>>>>> d78921272f638abc0f384ea72aad8824ef2cbcd9

class DashboardController extends AbstractController
{
    #[Route('/dashboard/admin', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
<<<<<<< HEAD
    public function adminDashboard(EvenementRepository $evenementRepository): Response
    {
        $evenements = $evenementRepository->findAll();
=======
    public function adminDashboard(
        EvenementRepository $evenementRepository,
        EquipementRepository $equipementRepository,
        PubliciteRepository $publiciteRepository,
        ReservationEquipementRepository $reservationEquipementRepository,
        CampagnesPublicitaireRepository $campagneRepository
    ): Response {
        $evenements   = $evenementRepository->findAll();
        $equipements  = $equipementRepository->findAll();
        $publicites   = $publiciteRepository->findAll();
        $reservationEquipements = $reservationEquipementRepository->findAll();
        $campagnes    = $campagneRepository->findAll();

        // ✅ Statistiques équipements par statut
        $stats = [
            'disponible'   => $equipementRepository->count(['statut' => 'disponible']),
            'maintenance'  => $equipementRepository->count(['statut' => 'maintenance']),
            'indisponible' => $equipementRepository->count(['statut' => 'indisponible']),
        ];

>>>>>>> d78921272f638abc0f384ea72aad8824ef2cbcd9
        return $this->render('dashboard/admin/admin.html.twig', [
            'controller_name'        => 'DashboardController',
            'evenements'             => $evenements,
            'equipements'            => $equipements,
            'publicites'             => $publicites,
            'campagnes'              => $campagnes,
            'reservation_equipements'=> $reservationEquipements,
            'stats'                  => $stats,
        ]);
    }

    #[Route('/dashboard/owner', name: 'owner_dashboard')]
    #[IsGranted('ROLE_OWNER')]
<<<<<<< HEAD
    public function ownerDashboard(EvenementRepository $evenementRepository): Response
    {
        $evenements = $evenementRepository->findAll();
=======
    public function ownerDashboard(
        EvenementRepository $evenementRepository,
        EquipementRepository $equipementRepository,
        ReservationEquipementRepository $reservationEquipementRepository,
        PubliciteRepository $publiciteRepository,
        CampagnesPublicitaireRepository $campagneRepository
    ): Response {
        $evenements            = $evenementRepository->findAll();
        $equipements           = $equipementRepository->findAll();
        $publicites            = $publiciteRepository->findAll();
        $campagnes             = $campagneRepository->findAll();
        
        // Get the current user
        $user = $this->getUser();

>>>>>>> d78921272f638abc0f384ea72aad8824ef2cbcd9
        return $this->render('dashboard/owner/owner.html.twig', [
            'controller_name'        => 'DashboardController',
            'evenements'             => $evenements,
            'equipements'            => $equipements,
            'reservation_equipements' => $reservationEquipementRepository->findBy(['user' => $user]),
            'publicites'             => $publicites,
            'campagnes'              => $campagnes,
            'userId'                => $user->getUserId()
        ]);
    }

    #[Route('/dashboard/participant', name: 'participant_dashboard')]
    #[IsGranted('ROLE_PARTICIPANT')]
    public function participantDashboard(
        EvenementRepository $evenementRepository,
        PubliciteRepository $publiciteRepository,

        Request $request,
        BadgeService $badgeService
    ): Response {
        $evenements = $evenementRepository->findAll();
<<<<<<< HEAD
=======
        $publicites   = $publiciteRepository->findAll();


        // Example of handling search and sort functionality
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'name'); // Default sort field
        $direction = $request->query->get('direction', 'asc'); // Default sort direction
>>>>>>> d78921272f638abc0f384ea72aad8824ef2cbcd9

        return $this->render('dashboard/participant/participant.html.twig', [
            'evenements' => $evenements,
            'publicites' => $publicites,

            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'badge_service' => $badgeService,
        ]);
    }

    #[Route('/dashboard/participant/event/{id}', name: 'app_evenement_participant_show', methods: ['GET'])]
    public function showParticipant(Evenement $evenement): Response
    {
        return $this->render('evenement/showEVparticipant.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/dashboard/stats', name: 'dashboard_stats')]
    public function stats(SponsorRepository $sponsorRepository, ContratRepository $contratRepository): Response
    {
        // Number of sponsors by secteur_activite
        $sponsorsBySector = $sponsorRepository->createQueryBuilder('s')
            ->select('s.secteur_activite AS sector', 'COUNT(s.id_sponsor) AS count')
            ->groupBy('s.secteur_activite')
            ->getQuery()
            ->getResult();

        // Number of contracts by statut
        $contractsByStatus = $contratRepository->createQueryBuilder('c')
            ->select('c.statut AS status', 'COUNT(c.id_contrat) AS count')
            ->groupBy('c.statut')
            ->getQuery()
            ->getResult();

        // Total montant of contracts by type_contrat
        $totalMontantByType = $contratRepository->createQueryBuilder('c')
            ->select('c.type_contrat AS type', 'SUM(c.montant) AS totalMontant')
            ->groupBy('c.type_contrat')
            ->getQuery()
            ->getResult();

        return $this->render('dashboard/stats.html.twig', [
            'sponsorsBySector' => $sponsorsBySector,
            'contractsByStatus' => $contractsByStatus,
            'totalMontantByType' => $totalMontantByType,
        ]);
    }

    #[Route('/dashboard/renewals', name: 'dashboard_renewals')]
    public function renewals(ContratRepository $contratRepository): Response
    {
        $today = new \DateTime();
        $thresholdDate = (clone $today)->modify('+30 days');

        $expiringContracts = $contratRepository->createQueryBuilder('c')
            ->where('c.date_fin BETWEEN :today AND :thresholdDate')
            ->setParameter('today', $today->format('Y-m-d'))
            ->setParameter('thresholdDate', $thresholdDate->format('Y-m-d'))
            ->orderBy('c.date_fin', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('dashboard/renewals.html.twig', [
            'expiringContracts' => $expiringContracts,
        ]);
    }
}
