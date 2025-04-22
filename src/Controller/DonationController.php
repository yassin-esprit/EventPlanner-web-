<?php

namespace App\Controller;

use App\Entity\DonationRequest;
use App\Repository\DonationRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DonationController extends AbstractController
{
    #[Route('/donate', name: 'user_donate')]
    public function donate(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $amount = $request->request->get('amount');
            $user = $this->getUser();

            if ($amount > 0 && $amount <= $user->getBalance()) {
                $donationRequest = new DonationRequest();
                $donationRequest->setUser($user);
                $donationRequest->setAmount($amount);
                $donationRequest->setStatus('pending');

                $entityManager->persist($donationRequest);
                $entityManager->flush();

                $this->addFlash('success', 'Your donation request has been submitted.');
            } else {
                $this->addFlash('error', 'Invalid donation amount.');
            }

            return $this->redirectToRoute('user_donate');
        }

        return $this->render('user/donate.html.twig');
    }
}