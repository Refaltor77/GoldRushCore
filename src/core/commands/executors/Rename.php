<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\SoundTrait;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class Rename extends Executor
{
    use SoundTrait;

    public function __construct(string $name = 'rename', string $description = "Permet de changer le nom de votre item", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("rename.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§c/rename <itemName>"));
            return;
        }

        $itemInHand = $sender->getInventory()->getItemInHand();
        $itemInHand->setCustomName($args[0]);
        $sender->getInventory()->setItemInHand($itemInHand);
        $sender->sendSuccessSound();
        $sender->sendMessage(Messages::message("§Votre item vient d'être rename."));
    }
}