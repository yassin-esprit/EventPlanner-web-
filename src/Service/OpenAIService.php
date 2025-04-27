<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\TransportException;

class OpenAIService
{
    private $httpClient;
    private $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function getSponsorInfo(string $sponsorName, string $secteurActivite): array
    {
        try {
            $this->logger->info('Tentative de récupération des informations du sponsor', [
                'sponsor' => $sponsorName,
                'secteur' => $secteurActivite
            ]);

            // Recherche sur Wikipedia
            $response = $this->httpClient->request('GET', 'https://fr.wikipedia.org/w/api.php', [
                'query' => [
                    'action' => 'query',
                    'format' => 'json',
                    'prop' => 'extracts',
                    'exintro' => true,
                    'explaintext' => true,
                    'titles' => $sponsorName,
                    'lang' => 'fr'
                ]
            ]);

            $data = $response->toArray();
            $pages = $data['query']['pages'] ?? [];
            $page = reset($pages);
            
            if (isset($page['extract']) && !empty($page['extract'])) {
                $info = $page['extract'];
            } else {
                // Si pas d'information sur Wikipedia, générer une description basique
                $info = "{$sponsorName} est une entreprise opérant dans le secteur {$secteurActivite}. ";
                $info .= "En tant que sponsor, ils apportent leur soutien à nos événements et contribuent à leur succès. ";
                $info .= "Leur expertise dans le domaine {$secteurActivite} est un atout précieux pour notre organisation.";
            }

            $this->logger->info('Informations récupérées avec succès');
            
            return [
                'success' => true,
                'info' => $info
            ];

        } catch (TransportException $e) {
            $this->logger->error('Erreur de transport', [
                'error' => $e->getMessage(),
                'sponsor' => $sponsorName
            ]);

            return [
                'success' => false,
                'error' => 'Erreur de connexion: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des informations', [
                'error' => $e->getMessage(),
                'sponsor' => $sponsorName
            ]);

            return [
                'success' => false,
                'error' => 'Erreur lors de la récupération des informations: ' . $e->getMessage()
            ];
        }
    }
} 