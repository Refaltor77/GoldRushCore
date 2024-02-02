<?php

namespace core\commands\executors\secret;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;

class CancelExhange extends Executor
{
    public function __construct(string $name = 'exchange_cancel_12457586', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!Main::getInstance()->getExchangeManager()->hasSaveInventory($sender)) {
            $sender->sendMessage(Messages::message("§cTu fou quoi ici toi ?"));
            return;
        }

        Main::getInstance()->getExchangeManager()->cancelSession($sender);

        $sender->teleport(new Position(57, 85, -52, Server::getInstance()->getWorldManager()->getDefaultWorld()));
        $sender->sendMessage(Messages::message("§fÉchange annulé"));
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}