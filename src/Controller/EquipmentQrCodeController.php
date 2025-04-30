<?php

namespace App\Controller;

use App\Entity\Equipement;
use App\Service\QrCodeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class EquipmentQrCodeController extends AbstractController
{
    private $qrCodeService;
    private $logger;

    public function __construct(QrCodeService $qrCodeService, LoggerInterface $logger)
    {
        $this->qrCodeService = $qrCodeService;
        $this->logger = $logger;
    }

    #[Route('/equipment/{id}/qr-code', name: 'app_equipment_qr_code')]
    public function showQrCode(Equipement $equipment): Response
    {
        try {
            $qrCode = $this->qrCodeService->getEquipmentQrCode($equipment);
            
            // Return the QR code as an SVG response
            return new Response(
                $qrCode->getString(),
                Response::HTTP_OK,
                ['Content-Type' => 'image/svg+xml']
            );
        } catch (\Exception $e) {
            $this->logger->error('QR Code generation failed: ' . $e->getMessage());
            return new Response('Error generating QR code: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/equipment/{id}/qr-code-card', name: 'app_equipment_qr_code_card')]
    public function displayQrCodeCard(Equipement $equipment): Response
    {
        try {
            $qrCode = $this->qrCodeService->getEquipmentQrCode($equipment);
            $qrCodeDataUri = $qrCode->getDataUri();
        } catch (\Exception $e) {
            $this->logger->error('QR Code generation failed: ' . $e->getMessage());
            $qrCodeDataUri = null;
        }

        return $this->render('equipement/qr_code_card.html.twig', [
            'equipement' => $equipment,  // Changed to match your naming conventions
            'qrCodeDataUri' => $qrCodeDataUri,
            'error' => isset($e) ? $e->getMessage() : null
        ]);
    }
}