<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Ip extends Executor
{
    public function __construct(string $name = "ip", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('ip.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§c/ip <playerName>"));
            return;
        }

        $player = $args[0];
        $xuid = Main::getInstance()->getDataManager()->getXuidByName($player);
        if ($xuid === null) {
            $sender->sendMessage(Messages::message("§cLe joueur n'existe pas."));
            return;
        }

        $ip = Main::getInstance()->getDataManager()->getIpByXuid($xuid);
        if ($ip === null) {
            $sender->sendMessage(Messages::message("§cLe joueur n'existe pas."));
            return;
        }

        $sender->sendMessage(Messages::message("§fL'ip du joueur §6" . $args[0] . " §f : §6$ip"));
    }

    public function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'Liste des joueurs', true, 'Joueurs', $this->getAllPlayersArrayForArgs());
        return parent::loadOptions($player);
    }
}