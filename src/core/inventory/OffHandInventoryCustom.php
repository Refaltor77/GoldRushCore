<?php

namespace core\inventory;

use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\inventory\PlayerOffHandInventory;
use pocketmine\inventory\SimpleInventory;

class OffHandInventoryCustom extends SimpleInventory
{
    private Living $holder;

    public function __construct(Living $player){
        $this->holder = $player;
        parent::__construct(1);
    }

    public function getHolder() : Living{ return $this->holder; }
}