<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Entity\Merchandise;
use App\Repository\CartItemRepository;
use App\Repository\MerchandiseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/shop/cart', name: 'shop_cart_')]
class CartController extends AbstractController
{
    public function __construct(
        private CartItemRepository $cartItemRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private MerchandiseRepository $merchandiseRepository,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $cartItems = $this->cartItemRepository->findAll();
        $data = $this->serializer->serialize($cartItems, 'json', ['groups' => ['cart_item']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/add/{id}', name: 'add', methods: ['POST'])]
    public function add(Merchandise $merchandise, Request $request): JsonResponse
    {
        $quantity = (int) $request->request->get('quantity', 1);
        
        if ($quantity <= 0) {
            return new JsonResponse(['error' => 'Quantity must be greater than 0'], Response::HTTP_BAD_REQUEST);
        }

        if ($merchandise->getQuantity() < $quantity) {
            return new JsonResponse(['error' => 'Not enough stock available'], Response::HTTP_BAD_REQUEST);
        }

        $cartItem = new CartItem();
        $cartItem->setMerchandise($merchandise);
        $cartItem->setQuantity($quantity);

        $this->entityManager->persist($cartItem);
        $this->entityManager->flush();

        $data = $this->serializer->serialize($cartItem, 'json', ['groups' => ['cart_item']]);
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    #[Route('/remove/{id}', name: 'remove', methods: ['DELETE'])]
    public function remove(CartItem $cartItem): JsonResponse
    {
        $this->entityManager->remove($cartItem);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/checkout', name: 'checkout', methods: ['POST'])]
    public function checkout(): JsonResponse
    {
        $cartItems = $this->cartItemRepository->findAll();
        
        if (empty($cartItems)) {
            return new JsonResponse(['error' => 'Cart is empty'], Response::HTTP_BAD_REQUEST);
        }

        foreach ($cartItems as $cartItem) {
            $merchandise = $cartItem->getMerchandise();
            $merchandise->setQuantity($merchandise->getQuantity() - $cartItem->getQuantity());
            $this->entityManager->remove($cartItem);
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Checkout successful'], Response::HTTP_OK);
    }
} 