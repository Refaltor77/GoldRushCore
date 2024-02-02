<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class Reply extends Executor
{
    public function __construct(string $name = "r", string $description = "Repondre a un message privé", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if(!isset(Main::$TELL[$sender->getName()])){
            $sender->sendMessage("§cVous n'avez personne à qui répondre.");
            return true;
        }else{
            if(count($args) < 1){
                throw new InvalidCommandSyntaxException();
            }
            $player = Main::$TELL[$sender->getName()];
            $player = Server::getInstance()->getPlayerByPrefix($player);
            if(!$player instanceof Player || !$player->isConnected()){
                $sender->sendMessage("§cLe joueur n'est plus connecté.");
                return true;
            }
            if(!Main::getInstance()->getSettingsManager()->getSetting($player,"private-chat")){
                $sender->sendMessage("§cLe joueur a désactivé les messages privés.");
                return true;
            }
            $message = implode(" ", $args);
            $sender->sendMessage("§7[§6Moi §7-> §6{$player->getName()}§7] §f{$message}");
            $player->sendMessage("§7[§6{$sender->getName()} §7-> §6Moi§7] §f{$message}");
            Main::$TELL[$player->getName()] = $sender->getName();
            return true;
        }
    }
}