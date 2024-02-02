<?php

namespace core\listeners\types\horse;

use core\entities\horse\Horse;
use core\listeners\BaseEvent;
use core\messages\Messages;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;

class HorseEvent extends BaseEvent
{
    public static array $playerMount = [];

    const max_x = 9800;
    const max_z = 9800;
    const min_x = -9800;
    const min_z = -9800;

    public function onRide(DataPacketReceiveEvent $event): void
    {
        $packet = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();

        if ($packet instanceof PlayerAuthInputPacket) {
            if (isset(self::$playerMount[$player->getName()])) {
                $entity = $player->getWorld()->getEntity((int)self::$playerMount[$player->getName()]);
                if ($entity instanceof Horse) {
                    if ($entity->isClosed() || $entity->isFlaggedForDespawn()) {
                        return;
                    }

                    $pos = $entity->getPosition();
                    if ($pos->getX() <= self::min_x || $pos->getX() >= self::max_x) {
                        $player->sendMessage(Messages::message("§cLimite de la map atteinte."));
                        $event->cancel();
                        $player->resetFallDistance();
                        $entity->flagForDespawn();
                        if (isset(self::$playerMount[$player->getName()])) {
                            unset(self::$playerMount[$player->getName()]);
                        }
                        return;
                    }
                    if ($pos->getZ() <= self::min_z || $pos->getZ() >= self::max_z) {
                        $player->sendMessage(Messages::message("§cLimite de la map atteinte."));
                        $event->cancel();
                        $player->resetFallDistance();
                        $entity->flagForDespawn();
                        if (isset(self::$playerMount[$player->getName()])) {
                            unset(self::$playerMount[$player->getName()]);
                        }
                        return;
                    }


                    $entity->doRidingMovement($player, $packet->getMoveVecX(), $packet->getMoveVecZ());
                }
            }
        } elseif ($packet instanceof InteractPacket) {
            if ($packet->action === InteractPacket::ACTION_LEAVE_VEHICLE) {
                $player = $event->getOrigin()->getPlayer();
                if (isset(self::$playerMount[$player->getName()])) {
                    $entity = $player->getWorld()->getEntity((int)self::$playerMount[$player->getName()]);
                    unset(self::$playerMount[$player->getName()]);
                    $player->resetFallDistance();
                    $entity->flagForDespawn();
                }
            }
        }
    }




    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();

        if (isset(self::$playerMount[$player->getName()])) {
            $entity = $player->getWorld()->getEntity((int)self::$playerMount[$player->getName()]);
            if (is_null($entity)) return;
            if (!$entity->isFlaggedForDespawn()) $entity->flagForDespawn();
            unset(self::$playerMount[$player->getName()]);
        }
    }
}