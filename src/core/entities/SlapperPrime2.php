<?php

namespace core\entities;

use core\Main;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class SlapperPrime2 extends Slapper
{

    public int $tickUpdate = 60;


    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        return false;
    }

    public function onUpdate(int $currentTick): bool
    {
        if ($this->tickUpdate >= 60) $this->update();
        $this->tickUpdate++;
        return parent::onUpdate($currentTick);
    }


    public function update(): void {
        $this->tickUpdate = 0;
        $xuidWanted = Main::getInstance()->getPrimeManager()->getPrimeDeux();
        $skin = Main::getInstance()->getSkinManager2()->getSkinPlayer($xuidWanted);
        if (!is_null($skin)) {
            $this->setSkin($skin);
        }

        $name = Main::getInstance()->getDataManager()->getNameByXuid($xuidWanted);
        if ($name !== null) {
            $this->name = "§6WANTED : " . $name;
            $prime = Main::getInstance()->getPrimeManager()->getPrimePriceXuid($xuidWanted);
            $this->setScoreTag("§6PRIME : §f" . $prime . "§6$");
        }
    }
}