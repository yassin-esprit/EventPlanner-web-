<?php

namespace App\Controller;

use App\Repository\MerchandiseRepository;
use App\Entity\Merchandise;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MerchandiseController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MerchandiseRepository $merchandiseRepository
    ) {
    }

    #[Route('/user/merchandise', name: 'user_merchandise_list')]
    public function userIndex(): Response
    {
        // Get all available merchandise (quantity > 0)
        $merchandise = $this->merchandiseRepository->findAvailable();

        return $this->render('merchandise/user/list.html.twig', [
            'merchandise' => $merchandise,
        ]);
    }

    #[Route('/user/merchandise/buy/{id}', name: 'user_buy_merchandise', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function buyMerchandise(Request $request, Merchandise $merchandise): Response
    {
        // Check if merchandise is available
        if ($merchandise->getQuantity() <= 0) {
            $this->addFlash('error', 'This item is out of stock.');
            return $this->redirectToRoute('user_merchandise_list');
        }

        // Get quantity to buy from request
        $quantity = $request->request->getInt('quantity', 1);
        
        // Validate quantity
        if ($quantity <= 0) {
            $this->addFlash('error', 'Please select a valid quantity.');
            return $this->redirectToRoute('user_merchandise_list');
        }

        if ($quantity > $merchandise->getQuantity()) {
            $this->addFlash('error', 'Not enough items in stock.');
            return $this->redirectToRoute('user_merchandise_list');
        }

        // Update merchandise quantity
        $merchandise->setQuantity($merchandise->getQuantity() - $quantity);
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('Successfully purchased %d %s', $quantity, $merchandise->getName()));
        return $this->redirectToRoute('user_merchandise_list');
    }
} 