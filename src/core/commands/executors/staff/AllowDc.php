<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\utils\Config;

class AllowDc extends Executor
{
    public function __construct(string $name = "allow_dc", string $description = "Accepter un nouveau double compte", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("allowdc.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if(count($args) < 2){
            $sender->sendMessage("§c/allowdc <ajouter/refuser> <joueur>");
            return;
        }


        $target = $args[1] ?? "404";
        $xuid = Main::getInstance()->getDataManager()->getXuidByName($target);
        if ($xuid === null) {
            $sender->sendMessage("§cCe joueur n'existe pas ! faite attention au majuscules");
            return;
        }

        if($args[0] === "ajouter"){
            Main::getInstance()->getDcManager()->addDc($xuid);
            $sender->sendMessage("§aVous avez accepté le double compte de §e" . $target);
        }else{
            Main::getInstance()->getDcManager()->removeDc($xuid);
            $sender->sendMessage("§cVous avez refusé le double compte de §e" . $target);
        }

    }


    public function getCommandData(): CommandData
    {
        $this->addOptionEnum(0,"string",false,"actions",["ajouter","refuser"]);
        $this->addOptionEnum(1,"string",false,"players",$this->getAllPlayersArrayForArgs());

        return parent::getCommandData();
    }
}