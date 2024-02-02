<?php

namespace core\listeners\types\events;

use core\listeners\BaseEvent;
use core\tasks\KothScheduler;
use pocketmine\event\player\PlayerMoveEvent;

class KothListeners extends BaseEvent
{

    public static array $isInAreaKoth = [];
    public static array $participateEvent = [];

    public function onMove(PlayerMoveEvent $event): void
    {
        if ($event->getTo()->distance($event->getFrom()) >= 0.1) {
            $player = $event->getPlayer();
            if (KothScheduler::$hasKoth) {
                $pos = $player->getPosition();
                if ((int)$pos->getX() >= 58 && (int)$pos->getX() <= 68) {
                    if ((int)$pos->getZ() >= -212 && (int)$pos->getZ() <= -202) {
                        if ($pos->getY() >= 87 && $pos->getY() <= 90) {
                            if (!isset(self::$isInAreaKoth[$player->getXuid()]) || !self::$isInAreaKoth[$player->getXuid()]) {
                                $player->sendPopup("§6Vous entrez dans la zone du koth !");
                                self::$isInAreaKoth[$player->getXuid()] = true;
                                if (!isset(self::$participateEvent[$player->getXuid()])) self::$participateEvent[$player->getXuid()] = true;
                            }
                        }
                    } else {
                        if (isset(self::$isInAreaKoth[$player->getXuid()]) && self::$isInAreaKoth[$player->getXuid()]) {
                            $player->sendPopup("§6Vous sortez de la zone du koth !");
                            unset(self::$isInAreaKoth[$player->getXuid()]);
                            if (!isset(self::$participateEvent[$player->getXuid()])) self::$participateEvent[$player->getXuid()] = true;
                        }
                    }
                } else {
                    if (isset(self::$isInAreaKoth[$player->getXuid()]) && self::$isInAreaKoth[$player->getXuid()]) {
                        $player->sendPopup("§6Vous sortez de la zone du koth !");
                        unset(self::$isInAreaKoth[$player->getXuid()]);
                        if (!isset(self::$participateEvent[$player->getXuid()])) self::$participateEvent[$player->getXuid()] = true;
                    }
                }
            }
        }
    }
}