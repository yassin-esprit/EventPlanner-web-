<?php

namespace App\Service;

use App\Repository\EquipementRepository;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AIService
{
    private string $apiKey;
    private string $apiUrl;
    private LoggerInterface $logger;
    private EquipementRepository $equipementRepository;

    public function __construct(
        LoggerInterface $logger,
        EquipementRepository $equipementRepository,
        ParameterBagInterface $params
    ) {
        // Get API key from params or environment
        $this->apiKey = $params->has('app.gemini_api_key') 
            ? $params->get('app.gemini_api_key') 
            : $_ENV['GEMINI_API_KEY'] ?? '';
            
        $this->logger = $logger;
        $this->equipementRepository = $equipementRepository;
        $this->apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key=".$this->apiKey;
        
        // Log API key status (don't log the actual key)
        $this->logger->info('AIService initialized', [
            'api_key_set' => !empty($this->apiKey)
        ]);
    }

    public function generateChatbotResponse(string $userMessage): string
    {
        try {
            // First try a simple response without equipment data to isolate the issue
            $prompt = sprintf(
                "You are a helpful chatbot for an equipment management system. " .
                "Respond to the user's query: '%s'\n" .
                "Keep the response concise (max 200 characters), natural, and accurate. If the query is unclear or unrelated, provide a polite fallback response.",
                $userMessage
            );

            $this->logger->info('Generating simple chatbot response for debugging', [
                'user_message' => $userMessage,
                'prompt' => $prompt
            ]);

            $response = $this->getGeminiResponse($prompt);
            return substr($response, 0, 200);
        } catch (\Exception $e) {
            $this->logger->error('Failed to generate simple chatbot response', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_message' => $userMessage
            ]);
            return "Debug error: " . $e->getMessage();
        }
    }

    // Once the simple response works, you can uncomment this more complex version
    /*
    public function generateChatbotResponse(string $userMessage): string
    {
        // Fetch equipment data
        try {
            $equipements = $this->equipementRepository->findAll();
            $equipmentContext = [];

            foreach ($equipements as $equipement) {
                $equipmentContext[] = sprintf(
                    "Equipment: %s, Status: %s, Available Quantity: %d",
                    $equipement->getNom(),
                    $equipement->getStatut(),
                    $equipement->getQuantiteDispo()
                );
            }

            $context = implode("\n", $equipmentContext);
        } catch (\Exception $e) {
            $this->logger->error('Failed to fetch equipment data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return "Database error: " . $e->getMessage();
        }

        // Construct prompt
        $prompt = sprintf(
            "You are a helpful chatbot for an equipment management system. Based on the following equipment data:\n%s\n" .
            "Respond to the user's query: '%s'\n" .
            "Keep the response concise (max 200 characters), natural, and accurate. If the query is unclear or unrelated, provide a polite fallback response.",
            $context,
            $userMessage
        );

        $this->logger->info('Generating chatbot response', [
            'user_message' => $userMessage,
            'context' => $context,
            'prompt' => $prompt
        ]);

        // Call Gemini API
        try {
            $response = $this->getGeminiResponse($prompt);
            return substr($response, 0, 200);
        } catch (\Exception $e) {
            $this->logger->error('Failed to generate chatbot response', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_message' => $userMessage
            ]);
            return "API error: " . $e->getMessage();
        }
    }
    */

    public function getGeminiResponse(string $prompt): string
    {
        $client = HttpClient::create();

        try {
            $requestBody = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 1024,
                    'temperature' => 0.7
                ]
            ];
            
            $this->logger->debug('Sending request to Gemini API', [
                'url' => $this->apiUrl,
                'body' => json_encode($requestBody, JSON_PRETTY_PRINT)
            ]);

            $response = $client->request('POST', $this->apiUrl, [
                'json' => $requestBody,
                'timeout' => 60,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $contentString = $response->getContent(false);
            
            $this->logger->debug('Raw API Response', [
                'status' => $statusCode,
                'raw' => $contentString
            ]);
            
            $content = json_decode($contentString, true);
            
            if ($statusCode !== 200) {
                $this->logger->error('API Error Response', [
                    'status' => $statusCode,
                    'content' => $contentString
                ]);
                throw new \RuntimeException("API returned non-200 status code: $statusCode");
            }
            
            if (isset($content['error'])) {
                $this->logger->error('Gemini API Error', [
                    'code' => $content['error']['code'] ?? 'unknown',
                    'message' => $content['error']['message'] ?? 'No message',
                    'status' => $content['error']['status'] ?? 'unknown',
                    'details' => $content['error']['details'] ?? []
                ]);
                throw new \RuntimeException('API Error: ' . ($content['error']['message'] ?? 'Unknown error'));
            }
            
            if (!isset($content['candidates']) || empty($content['candidates'])) {
                $this->logger->error('No candidates in response', ['response' => $content]);
                throw new \RuntimeException('No response candidates from the API');
            }
            
            if (!isset($content['candidates'][0]['content']['parts'][0]['text'])) {
                $this->logger->error('Unexpected response format', ['response' => $content]);
                throw new \RuntimeException('Unexpected API response format');
            }

            return $content['candidates'][0]['content']['parts'][0]['text'];
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Gemini API Connection Error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \RuntimeException('Could not connect to the API service: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('Gemini API Error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \RuntimeException('Failed to generate description: ' . $e->getMessage());
        }
    }

    public function testConnection(): array
    {
        $client = HttpClient::create();
        try {
            $testPrompt = "Hello, this is a connection test. Please respond with 'Connection successful.'";
            $requestBody = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $testPrompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 1024,
                    'temperature' => 0.7
                ]
            ];
            
            $response = $client->request('POST', $this->apiUrl, [
                'json' => $requestBody,
                'timeout' => 30,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            $contentString = $response->getContent(false);
            $content = json_decode($contentString, true);
            
            return [
                'success' => $statusCode === 200,
                'status' => $statusCode,
                'response' => $content
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'exception' => get_class($e)
            ];
        }
    }
}