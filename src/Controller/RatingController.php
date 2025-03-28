<?php

// src/Controller/RatingController.php
namespace App\Controller;

use App\Entity\Rating;
use App\Entity\Evenement;
use App\Form\RatingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/rating')]
class RatingController extends AbstractController
{
    #[Route('/event/{id}/rate', name: 'app_event_rate', methods: ['GET', 'POST'])]
    public function rateEvent(
        Request $request,
        Evenement $event,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $user = $security->getUser();
        
        if (!$user) {
            $this->addFlash('warning', 'You need to be logged in to rate events.');
            return $this->redirectToRoute('app_login');
        }

        // Check if user already rated this event
        $existingRating = $entityManager->getRepository(Rating::class)->findOneBy([
            'user' => $user,
            'evenement' => $event
        ]);

        $rating = $existingRating ?? new Rating();
        $form = $this->createForm(RatingType::class, $rating);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$existingRating) {
                $rating->setUser($user);
                $rating->setEvenement($event);
                $rating->setCreatedAt(new \DateTimeImmutable());
                $entityManager->persist($rating);
            }
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Thank you for your rating!');
            return $this->redirectToRoute('app_evenement_show', ['id' => $event->getId()]);
        }

        return $this->render('rating/rate_event.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'existingRating' => $existingRating
        ]);
    }

    // ... keep your existing CRUD methods ...
}
