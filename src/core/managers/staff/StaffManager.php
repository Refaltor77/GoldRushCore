<?php

namespace core\managers\staff;

use core\Main;
use core\managers\Manager;
use core\settings\Ids;
use core\sql\SQL;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\world\Position;

class StaffManager extends Manager
{
    public array $isInStaffMode = [];
    public array $inventories = [];
    public array $pos = [];

    public function __construct(Main $plugin)
    {
        parent::__construct($plugin);
    }


    public function isInStaffMode(Player $player): bool {
        return in_array($player->getXuid(), $this->isInStaffMode);
    }

    public function setStaffMode(Player $player): void {
        $player->setGamemode(GameMode::CREATIVE());
        $this->isInStaffMode[] = $player->getXuid();
        $this->pos[$player->getXuid()] = $player->getPosition();

        $items = [
            CustomiesItemFactory::getInstance()->get(Ids::MUTE),
            CustomiesItemFactory::getInstance()->get(Ids::BAN),
            CustomiesItemFactory::getInstance()->get(Ids::EYE),
            CustomiesItemFactory::getInstance()->get(Ids::FREEZE),
            VanillaItems::GUNPOWDER()->setCustomName("vanish - §cOFF"),
            CustomiesItemFactory::getInstance()->get(Ids::HOME_MANAGE),
            CustomiesItemFactory::getInstance()->get(Ids::RANDOM_TP),
            CustomiesItemFactory::getInstance()->get(Ids::SEE_INV),
            CustomiesItemFactory::getInstance()->get(Ids::TP_LIST),
        ];


        foreach ($items as $item) {
            $player->getInventory()->addItem($item);
        }
    }


    public function removeStaffMode(Player $player): void {
        if ($this->isInStaffMode($player)) {
            unset($this->isInStaffMode[array_search($player->getXuid(), $this->isInStaffMode)]);
            $player->setGamemode(GameMode::SURVIVAL());
            Main::getInstance()->getInventoryManager()->checkingDatabase($player);


            $pos = $this->pos[$player->getXuid()] ?? null;
            if ($pos instanceof Position) {
                $player->teleport($pos);
                unset($this->pos[$player->getXuid()]);
            }
        }
    }
}