<?php

namespace App\Controller;

use App\Entity\Donation;
use App\Repository\DonationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/donations')]
class DonationController extends AbstractController
{
    public function __construct(
        private DonationRepository $donationRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/my', name: 'app_donations_my', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myDonations(Request $request): Response
    {
        $user = $this->getUser();
        $status = $request->query->get('status');
        
        if ($status) {
            $donations = $this->donationRepository->findByUserAndStatus($user->getId(), $status);
        } else {
            $donations = $this->donationRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);
        }

        return $this->render('donation/my.html.twig', [
            'donations' => $donations,
            'currentStatus' => $status,
        ]);
    }

    #[Route('/admin', name: 'app_donations_admin', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDonations(Request $request): Response
    {
        $status = $request->query->get('status');
        
        if ($status) {
            $donations = $this->donationRepository->findByStatus($status);
        } else {
            $donations = $this->donationRepository->findBy([], ['createdAt' => 'DESC']);
        }

        return $this->render('donation/admin.html.twig', [
            'donations' => $donations,
            'currentStatus' => $status,
        ]);
    }

    #[Route('/admin/{id}/accept', name: 'app_donations_accept', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function acceptDonation(Donation $donation): Response
    {
        $donation->setStatus('accepted');
        $this->entityManager->flush();

        $this->addFlash('success', 'Donation accepted successfully.');
        return $this->redirectToRoute('app_donations_admin');
    }

    #[Route('/admin/{id}/deny', name: 'app_donations_deny', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function denyDonation(Donation $donation): Response
    {
        $donation->setStatus('denied');
        $this->entityManager->flush();

        $this->addFlash('success', 'Donation denied successfully.');
        return $this->redirectToRoute('app_donations_admin');
    }
}