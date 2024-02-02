<?php

namespace core\items\others;

use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class Rtp extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, "rtp");
        $this->initComponent('rtp', $inventory);
        $this->addComponent(new MaxStackSizeComponent(1));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Le ticket de RTP doit être remis\nau téléporteur, qui vous téléportera\ndans le monde Faction.",
            "§6---",
            "§eRareté: " . TextFormat::GOLD . "LEGEND"
        ]);
    }

    public function getMaxStackSize(): int
    {
        return 1;
    }
}