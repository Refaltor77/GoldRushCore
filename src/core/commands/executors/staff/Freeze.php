<?php

namespace core\commands\executors\staff;

use core\api\timings\TimingsSystem;
use core\commands\Executor;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\Server;

class Freeze extends Executor
{
    public function __construct(string $name = 'freeze', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('freeze.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§c/freeze §4<playerName>"));
            return;
        }


        $playerName = $args[0];
        $player = Server::getInstance()->getPlayerByPrefix($playerName);
        if (!$player instanceof CustomPlayer) {
            $sender->sendMessage(Messages::message("§cLe joueur §4" . $playerName . " §cn'est pas en ligne."));
            return;
        }

        if ($player->getXuid() === $sender->getXuid()) {
            $player->sendErrorSound();
            $player->sendMessage(Messages::message("§cPourquoi tu te freeze toi ?"));
            return;
        }


        if ($player->hasFreeze()) {
            $player->setFreeze(false);
            $player->sendMessage(Messages::message("§fVous pouvez désormais vous déplacer normalement."));
            $player->sendSuccessSound();
        } else {
            $player->setFreeze(true);
            $player->sendErrorSound();


            $timing = new TimingsSystem();
            $timing->createTiming(function (TimingsSystem $timingsSystem, int $second) use ($player) : void {
                if (!$player->isConnected()) {
                    $timingsSystem->stopTiming();
                    return;
                }
                if ($player->hasFreeze()) {
                    $player->sendTitle("§cUn Modérateur vous a gelé !", "§4(§cDeconnexion §4= §cban automatique§4)", 0, 1);
                } else $timingsSystem->stopTiming();
            });
        }
    }
}