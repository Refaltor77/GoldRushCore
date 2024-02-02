<?php

namespace core\inventory;


use pocketmine\player\Player;
use pocketmine\world\sound\ChestCloseSound;
use pocketmine\world\sound\ChestOpenSound;
use tedo0627\inventoryui\CustomInventory;

class PlatinumChestInventory extends CustomInventory {


    public function onClose(Player $who): void
    {
        $who->getWorld()->addSound($who->getEyePos(), new ChestCloseSound());
        parent::onClose($who);
    }


    public function onOpen(Player $who): void
    {
        $who->getWorld()->addSound($who->getEyePos(), new ChestOpenSound());
        parent::onOpen($who);
    }
}