<?php

namespace App\Controller;

use App\Service\AIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ChatbotController extends AbstractController
{

    

    #[Route('/chatbot', name: 'app_chatbot', methods: ['POST'])]
    public function chatbot(Request $request, AIService $aiService): JsonResponse
    {
        $userMessage = $request->request->get('message');
        
        if (empty($userMessage)) {
            return $this->json(['error' => 'Message is required'], 400);
        }
        
        try {
            $response = $aiService->generateChatbotResponse($userMessage);
            return $this->json(['response' => $response]);
        } catch (\Exception $e) {
            // Return error message in the response for debugging
            return $this->json(['response' => 'Error: ' . $e->getMessage()], 200);
        }
    }
    
    #[Route('/chatbot', name: 'app_chatbot_page', methods: ['GET'])]
    public function chatbotPage(): Response
    {
        return $this->render('chatbot/index.html.twig');
    }
    
    #[Route('/chatbot-test', name: 'app_chatbot_test')]
    public function testChatbot(AIService $aiService): Response
    {
        try {
            $result = $aiService->testConnection();
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'exception' => get_class($e)
            ]);
        }
    }
}