<?php

namespace core\items\others\keypad;

use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class Keypad extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, "Keypad");
        $this->initComponent('keypad', $inventory);
        $this->addComponent(new MaxStackSizeComponent(1));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Le keypad vous permet de \nsécuriser votre coffre moddé !",
            "§6---",
            "§l§eExperience: §l10§r§axp",
            "§eRareté: " . TextFormat::GRAY . "COMMON"
        ]);
    }

    public function getMaxStackSize(): int
    {
        return 1;
    }
}