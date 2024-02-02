<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\Server;
use pocketmine\world\sound\XpCollectSound;

class Heal extends Executor
{
    public function __construct(string $name = "heal", string $description = "Permet de vous soigner", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("heal.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if(isset($args[0])){
            $player = Server::getInstance()->getPlayerByPrefix($args[0]);
            if($player instanceof CustomPlayer){
                $player->setHealth($player->getMaxHealth());
                $player->sendMessage(Messages::message("§aVous avez été soigné"));
                $sender->sendMessage(Messages::message("§aVous avez soigné §e" . $player->getName()));
                $sender->getWorld()->addSound($sender->getLocation(), new XpCollectSound(), [$sender]);
                $player->getWorld()->addSound($player->getLocation(), new XpCollectSound(), [$player]);
            }else{
                $sender->sendMessage("§cLe joueur n'est pas connecté");
            }
        }else{
            $sender->setHealth($sender->getMaxHealth());
            $sender->getWorld()->addSound($sender->getLocation(), new XpCollectSound(), [$sender]);
            $sender->sendMessage(Messages::message("§aVous avez été soigné"));
        }
    }
}