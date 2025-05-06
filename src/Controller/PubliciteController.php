<?php

namespace App\Controller;

use Twilio\Rest\Client;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

use App\Entity\Publicite;
use App\Form\PubliciteType;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Repository\PubliciteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/publicite')]
final class PubliciteController extends AbstractController{
    #[Route(name: 'app_publicite_index', methods: ['GET'])]
    public function index(PubliciteRepository $publiciteRepository): Response
    {
        return $this->render('publicite/index.html.twig', [
            'publicites' => $publiciteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_publicite_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $publicite = new Publicite();
        $form = $this->createForm(PubliciteType::class, $publicite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($publicite);
            $entityManager->flush();

            return $this->redirectToRoute('app_publicite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('publicite/new.html.twig', [
            'publicite' => $publicite,
            'form' => $form,
            
        ]);
    }

    #[Route('/{id_pub}', name: 'app_publicite_show', methods: ['GET'])]
    public function show(Publicite $publicite): Response
    {
        return $this->render('publicite/show.html.twig', [
            'publicite' => $publicite,
        ]);
    }
    
    #[Route('/{id_pub}', name: 'app_publicite_show2', methods: ['GET'])]
    public function show2(Publicite $publicite): Response
    {
        return $this->render('publicite/show2.html.twig', [
            'publicite' => $publicite,
        ]);
    }
    #[Route('/{id_pub}/edit', name: 'app_publicite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Publicite $publicite, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PubliciteType::class, $publicite);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            // Send SMS notification
            $this->sendEditNotification($publicite);
    
            return $this->redirectToRoute('app_publicite_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('publicite/edit.html.twig', [
            'publicite' => $publicite,
            'form' => $form,
        ]);
    }
    
    private function sendEditNotification(Publicite $publicite): void
    {
        // Your Twilio credentials - better to put these in .env
        $accountSid = 'AC78169c8b47cd1773189246897ab8e39f';
        $authToken = 'e835bdd0f13cdb75811922db5673c80c';
        $twilioNumber = '+19125720626';
        
        // The recipient's phone number - should be from configuration
        $toNumber = '+21620370798'; // Replace with actual number to notify
        
        try {
            // Now this will work with the use statement
            $client = new Client($accountSid, $authToken);
            
            $message = $client->messages->create(
                $toNumber,
                [
                    'from' => $twilioNumber,
                    'body' => sprintf(
                        "Advertisement '%s' has been updated.\nType: %s\nLocation: %s\nStatus: %s",
        
                        $publicite->getTitrePub(),
                        $publicite->getTypePub(),
                        $publicite->getEmplacementPub(),
                        $publicite->getStatutPub()
                    )
                ]
            );
            
            // Optional: Log the message SID for reference
            // $this->logger->info('Twilio message sent: ' . $message->sid);
            
        } catch (\Exception $e) {
            // Log the error but don't interrupt the user flow
            $this->addFlash('warning', 'Advertisement was updated but notification failed to send.');
            // $this->logger->error('Twilio error: ' . $e->getMessage());
        }
    }

    #[Route('/{id_pub}', name: 'app_publicite_delete', methods: ['POST'])]
    public function delete(Request $request, Publicite $publicite, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$publicite->getId_pub(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($publicite);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_publicite_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id_pub}/pdf', name: 'app_publicite_pdf' ,methods: ['GET'])] 
    public function generatePdf(int $id_pub, PubliciteRepository $publiciteRepository): Response
    {
        // 1. Get the advertisement
        $publicite = $publiciteRepository->find($id_pub);
        if (!$publicite) {
            throw $this->createNotFoundException('Advertisement not found');
        }

        // 2. Configure Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set([
            'defaultFont' => 'Arial',
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'tempDir' => sys_get_temp_dir(),
            'dpi' => 96,
            'isPhpEnabled' => false,
            'isJavascriptEnabled' => false
        ]);

        // 3. Initialize Dompdf
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->setHttpContext([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);

        // 4. Handle logo
        $logoPath = $this->getParameter('kernel.project_dir').'/public/images/mythosplanner1.png';
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }

        // 5. Generate HTML
        $html = $this->renderView('publicite/pdf_template.html.twig', [
            'publicite' => $publicite,
            'logo_base64' => $logoBase64
        ]);

        // 6. Generate PDF with timeout protection
        try {
            set_time_limit(60); // Set max execution time to 60 seconds
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
        } catch (\Exception $e) {
            throw new \RuntimeException('PDF generation failed: '.$e->getMessage());
        }

        // 7. Return the PDF
        $filename = "publicite-".$publicite->getIdPub()."-".date('Y-m-d').".pdf";
        
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename)
            ]
        );
    }

// In PubliciteController.php
#[Route('/{id_pub}/map', name: 'app_publicite_map', methods: ['GET'])]
public function showMap(Publicite $publicite): Response
{
    return $this->render('publicite/map.html.twig', [
        'publicite' => $publicite,
        'location_query' => urlencode($publicite->getEmplacementPub()),
    ]);
}
}