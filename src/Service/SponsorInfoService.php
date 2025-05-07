<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SponsorInfoService
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getSponsorInfo(string $sponsorName, string $secteurActivite): array
    {
        $wikiData = $this->fetchWikipediaSummary($sponsorName);

        if (!$wikiData) {
            // Fallback si pas trouvé
            return [
                'success' => true,
                'info' => [
                    'title' => $sponsorName,
                    'description' => "Entreprise spécialisée dans le secteur {$secteurActivite}.",
                    'summary' => "{$sponsorName} est une entreprise opérant dans le secteur {$secteurActivite}. En tant que sponsor, elle soutient nos événements et contribue à leur succès.",
                    'image' => null,
                    'url' => null,
                ]
            ];
        }

        return [
            'success' => true,
            'info' => $wikiData
        ];
    }

    private function fetchWikipediaSummary(string $title): ?array
    {
        $url = 'https://en.wikipedia.org/api/rest_v1/page/summary/' . urlencode($title);

        try {
            $response = $this->httpClient->request('GET', $url);
            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                if (isset($data['extract']) && !empty($data['extract'])) {
                    return [
                        'title' => $data['title'] ?? '',
                        'description' => $data['description'] ?? '',
                        'summary' => $data['extract'],
                        'image' => $data['thumbnail']['source'] ?? null,
                        'url' => $data['content_urls']['desktop']['page'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log('Error fetching Wikipedia summary: ' . $e->getMessage());
        }

        return null;
    }
}
