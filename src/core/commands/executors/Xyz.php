<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Xyz extends Executor
{
    public array $cache = [];


    public function __construct(string $name = 'xyz', string $description = "Activer les coordonées", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {

        $settingsManager = Main::getInstance()->getSettingsManager();

        $isOn = $settingsManager->getSetting($sender, "coordinates");

        $pk = new GameRulesChangedPacket();
        $pk->gameRules = [
            "showcoordinates" => new BoolGameRule((int)(!$isOn), false)
        ];
        $settingsManager->turnOnOffSetting($sender, "coordinates");
        $sender->getNetworkSession()->sendDataPacket($pk);
        $sender->sendNotification($isOn ? "§fCoordonnées désactivées !" : "§fCoordonnées activées !");
    }
}