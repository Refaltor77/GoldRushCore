<?php

namespace core\inventory\type;

use pocketmine\item\Item;
use pocketmine\player\Player;

interface RecipeInventory{

    public function getResultItem(Player $player, int $netId): ?Item;
}
