<?php

namespace App\Controller;

use App\Entity\DonationRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

class AdminDonationController extends AbstractController
{
    #[Route('/admin/donations', name: 'admin_donations')]
    public function listDonations(EntityManagerInterface $entityManager): Response
    {
        $donations = $entityManager->getRepository(DonationRequest::class)->findBy(['status' => 'pending']);

        return $this->render('admin/donations.html.twig', [
            'donations' => $donations,
        ]);
    }

    #[Route('/admin/donations/accept/{id}', name: 'admin_accept_donation')]
    public function acceptDonation(DonationRequest $donation, EntityManagerInterface $entityManager): Response
    {
        $donation->setStatus('accepted');
        $entityManager->flush();

        // Generate PDF confirmation
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        $html = $this->renderView('admin/donation_confirmation.html.twig', [
            'donation' => $donation,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        file_put_contents('confirmation_donation_' . $donation->getId() . '.pdf', $output);

        $this->addFlash('success', 'Donation accepted and confirmation PDF generated.');

        return $this->redirectToRoute('admin_donations');
    }

    #[Route('/admin/donations/deny/{id}', name: 'admin_deny_donation')]
    public function denyDonation(DonationRequest $donation, EntityManagerInterface $entityManager): Response
    {
        $donation->setStatus('denied');
        $entityManager->flush();

        $this->addFlash('error', 'Donation denied.');

        return $this->redirectToRoute('admin_donations');
    }
}