<?php
// src/Controller/ReportController.php

namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Repository\UserRepository;
use App\Repository\RatingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{
    #[Route('/owner/reports', name: 'app_owner_reports')]
    public function index(
        EvenementRepository $evenementRepository,
        UserRepository $userRepository,
        RatingRepository $ratingRepository
    ): Response {
        // Event statistics
        $totalEvents = $evenementRepository->count([]);
        $eventsByType = $evenementRepository->countEventsByType();
        $upcomingEvents = $evenementRepository->countUpcomingEvents();
        
        // Participation statistics
        $totalParticipants = $userRepository->countParticipants();
        $participationsByEvent = $evenementRepository->getParticipationStats();
        
        // Rating statistics
        $averageRatings = $ratingRepository->getAverageRatings();
        $ratingDistribution = $ratingRepository->getRatingDistribution();
        
            return $this->render('dashboard/owner/reports.html.twig', [ 
            'totalEvents' => $totalEvents,
            'eventsByType' => $eventsByType,
            'upcomingEvents' => $upcomingEvents,
            'totalParticipants' => $totalParticipants,
            'participationsByEvent' => $participationsByEvent,
            'averageRatings' => $averageRatings,
            'ratingDistribution' => $ratingDistribution,
        ]);
    }
}