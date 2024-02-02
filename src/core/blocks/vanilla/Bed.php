<?php

namespace core\blocks\vanilla;

use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Bed extends \pocketmine\block\Bed
{
    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($player instanceof CustomPlayer) {
            $player->sendMessage(Messages::message("§cLes lits sont désactivé pour le moment."));
            $player->sendErrorSound();
        }
        return false;
    }
}