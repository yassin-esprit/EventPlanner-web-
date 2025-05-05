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
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to view your cart');
            return $this->redirectToRoute('app_login');
        }

        $cartItems = $this->cartItemRepository->findBy(['user' => $user]);
        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems
        ]);
    }

    #[Route('/add/{id}', name: 'add', methods: ['POST'])]
    public function add(Merchandise $merchandise, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // Get quantity from JSON request
        $content = json_decode($request->getContent(), true);
        $quantity = (int) ($content['quantity'] ?? 1);
        
        if ($quantity <= 0) {
            return new JsonResponse(['error' => 'Quantity must be greater than 0'], Response::HTTP_BAD_REQUEST);
        }

        if ($merchandise->getQuantity() < $quantity) {
            return new JsonResponse(['error' => 'Not enough stock available'], Response::HTTP_BAD_REQUEST);
        }

        // Check if item already exists in cart
        $existingCartItem = $this->cartItemRepository->findOneBy([
            'user' => $user,
            'merchandise' => $merchandise
        ]);

        if ($existingCartItem) {
            $newQuantity = $existingCartItem->getQuantity() + $quantity;
            if ($merchandise->getQuantity() < $newQuantity) {
                return new JsonResponse(['error' => 'Not enough stock available for the total quantity'], Response::HTTP_BAD_REQUEST);
            }
            $existingCartItem->setQuantity($newQuantity);
            $this->entityManager->flush();
            $data = $this->serializer->serialize($existingCartItem, 'json', ['groups' => ['cart_item']]);
            return new JsonResponse($data, Response::HTTP_OK, [], true);
        }

        try {
        $cartItem = new CartItem();
        $cartItem->setMerchandise($merchandise);
        $cartItem->setQuantity($quantity);
            $cartItem->setUser($user);

        $this->entityManager->persist($cartItem);
        $this->entityManager->flush();

        $data = $this->serializer->serialize($cartItem, 'json', ['groups' => ['cart_item']]);
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to add item to cart: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/remove/{id}', name: 'remove', methods: ['DELETE'])]
    public function remove(CartItem $cartItem): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        if ($cartItem->getUser() !== $user) {
            return new JsonResponse(['error' => 'Not authorized to remove this item'], Response::HTTP_FORBIDDEN);
        }

        try {
        $this->entityManager->remove($cartItem);
        $this->entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to remove item from cart'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/checkout', name: 'checkout', methods: ['POST'])]
    public function checkout(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $cartItems = $this->cartItemRepository->findBy(['user' => $user]);
        
        if (empty($cartItems)) {
            return new JsonResponse(['error' => 'Cart is empty'], Response::HTTP_BAD_REQUEST);
        }

        try {
        foreach ($cartItems as $cartItem) {
            $merchandise = $cartItem->getMerchandise();
            $merchandise->setQuantity($merchandise->getQuantity() - $cartItem->getQuantity());
            $this->entityManager->remove($cartItem);
        }

        $this->entityManager->flush();
        return new JsonResponse(['message' => 'Checkout successful'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to process checkout'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 