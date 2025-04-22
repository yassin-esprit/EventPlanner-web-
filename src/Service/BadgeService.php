<?php
namespace App\Service;

use App\Entity\Evenement;

class BadgeService
{
    private const BADGE_LEVELS = [
        0 => ['name' => 'New Event', 'color' => 'secondary', 'icon' => 'bi-star'],
        5 => ['name' => 'Popular', 'color' => 'primary', 'icon' => 'bi-people'],
        10 => ['name' => 'Hot', 'color' => 'danger', 'icon' => 'bi-fire'],
        20 => ['name' => 'Viral', 'color' => 'warning', 'icon' => 'bi-lightning'],
        50 => ['name' => 'Legendary', 'color' => 'success', 'icon' => 'bi-trophy']
    ];

    public function getBadgeForEvent(Evenement $event): array
    {
        $participantCount = $event->getUsers()->count();
        
        $badge = self::BADGE_LEVELS[0]; // Default badge
        
        foreach (self::BADGE_LEVELS as $threshold => $badgeData) {
            if ($participantCount >= $threshold) {
                $badge = $badgeData;
            } else {
                break;
            }
        }
        
        return $badge;
    }
}