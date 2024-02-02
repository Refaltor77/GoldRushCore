<?php

namespace core\settings\ranks;

use core\Main;
use core\player\CustomPlayer;

final class Permissions
{

    const PERM_MAINTENANCE = 'goldrush.maintenance.permission';

    const PERMISSIONS = [
        'PIONNIER' => [

        ],
        'GUIDE' => [

        ],
        'MODERATOR' => [

        ],
        'ADMIN' => [

        ]
    ];

    public static function reloadPermission(CustomPlayer $player): void {
        $ranks = $player->getRankStorageLocal();
        foreach ($ranks->attachment as $perm) {
            $player->removeAttachment($perm);
        }
        $ranks->attachment = [];
        foreach ($ranks->getRanks() as $rank) {
            $permissions = Permissions::PERMISSIONS[$rank];
            foreach ($permissions as $permission) {
                $ranks->attachment[] = $player->addAttachment(Main::getInstance(), $permission, true);
            }
        }
    }
}