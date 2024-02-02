<?php

namespace core\tasks\staff;

use core\commands\executors\staff\Vanish;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class VanishTask extends Task
{
    public function onRun(): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $p) {
            if ($p->spawned) {
                if (in_array($p->getXuid(), Vanish::$inVanish)) {
                    $p->setSilent(true);
                    $p->getXpManager()->setCanAttractXpOrbs(false);
                    $p->getEffects()->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), null, 0, false));
                    foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                        if ($player->hasPermission("vanish.use") || Server::getInstance()->isOp($player->getName())) {
                            $player->showPlayer($p);
                        } else {
                            $player->hidePlayer($p);
                            $player->getNetworkSession()->sendDataPacket(PlayerListPacket::remove([PlayerListEntry::createRemovalEntry($p->getUniqueId())]));
                        }
                    }
                }
            }
        }
    }
}