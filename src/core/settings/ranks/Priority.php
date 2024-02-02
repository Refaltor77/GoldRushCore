<?php

namespace core\settings\ranks;

final class Priority
{
    const PRIORITY = [
        'PIONNIER' => 0,
        'GUIDE' => 1,
        'MODERATOR' => 2,
        'ADMIN' => 1000,
    ];

    public static function getRankPriority(array $ranks): string {
        $i = -1;
        $get = 'PIONNIER';
        foreach ($ranks as $rank) {
            $priority = self::PRIORITY[$rank];
            if ($priority >= $i) {
                $i = $priority;
                $get = $rank;
            }
        }
        return $get;
    }
}