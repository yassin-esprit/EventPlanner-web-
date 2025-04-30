<?php

namespace App\Service;

use App\Entity\Equipement;
use App\Entity\ReservationEquipement;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class QrCodeService
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Generate a QR code containing equipment details directly
     */
    public function getEquipmentQrCode(Equipement $equipment, int $size = 300)
    {
        // Create JSON data with equipment details
        $equipmentData = json_encode([
            'id' => $equipment->getIdEq(),
            'name' => $equipment->getNom(),
            'description' => $equipment->getDescription(),
            'Quantite' => $equipment->getQuantite_dispo() ,
            'status' => $equipment->getStatut(),
            // Add any other equipment details you want to include
        ], JSON_UNESCAPED_UNICODE);
        
        return $this->generateQrCode($equipmentData, $size);
    }

    /**
     * Generate a QR code for equipment checkout
     */
    public function getEquipmentCheckoutQrCode(Equipement $equipment, int $size = 300)
    {
        // Similar to above but with checkout-specific information
        $checkoutData = json_encode([
            'action' => 'checkout',
            'equipment_id' => $equipment->getIdEq(),
            'equipment_name' => $equipment->getNom(),
            'timestamp' => (new \DateTime())->format('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE);
        
        return $this->generateQrCode($checkoutData, $size);
    }

    /**
     * Generate a QR code for reservation confirmation
     */
    public function getReservationQrCode(ReservationEquipement $reservation, int $size = 300)
    {
        // Create JSON data with reservation details
        $reservationData = json_encode([
            'id' => $reservation->getId(),
            'equipment' => $reservation->getEquipement()->getNom(),
            'date' => $reservation->getDateReservation()->format('Y-m-d'),
            'user' => $reservation->getUser()->getUsername(),
            // Add any other reservation details you want to include
        ], JSON_UNESCAPED_UNICODE);
        
        return $this->generateQrCode($reservationData, $size);
    }

    /**
     * Generic method to generate QR codes as SVG (no GD dependency)
     */
    private function generateQrCode(string $data, int $size = 300)
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 10),
            new SvgImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($data);
        
        return new QrCodeResult($qrCodeSvg);
    }
}

/**
 * Simple wrapper class to maintain the original API
 */
class QrCodeResult
{
    private $svgContent;
    
    public function __construct(string $svgContent)
    {
        $this->svgContent = $svgContent;
    }
    
    public function getString(): string
    {
        return $this->svgContent;
    }
    
    public function getDataUri(): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode($this->svgContent);
    }
}