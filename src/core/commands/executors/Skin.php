<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\entity\Skin as SkinEntity;
use pocketmine\Server;

class Skin extends Executor
{

    public function __construct(string $name = "skin", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("skin.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (empty($args[0])) {
            $sender->sendMessage("§cUsage: /skin <reset|set>");
            return;
        }

        $skinManager = Main::getInstance()->getSkinManager();

        if ($args[0] === "reset") {
            $skinManager->resetSkin($sender);
            $sender->sendMessage("§aVous avez bien remis votre skin par défaut");
            return;
        }
        if ($args[0] == "set") {
            if (empty($args[1])) {
                $sender->sendMessage("§cUsage: /skin set <player>");
                return;
            }
            $namedTag = Server::getInstance()->getOfflinePlayerData($args[1]);
            if ($namedTag === null) {
                $sender->sendMessage("§cLe joueur n'existe pas");
                return;
            }
            $skinTag = $namedTag->getCompoundTag("Skin");
            if (is_null($skinTag)) {
                $skinData = null;
            } else {
                $skinData = $skinTag->getByteArray("Data");
            }
            $sender->setSkin(new SkinEntity($sender->getSkin()->getSkinId(), $skinData));
            return;
        }
    }

}