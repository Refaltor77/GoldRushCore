<?php

namespace core\items\ingots;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class CompressedCobbleStone extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_ITEMS,
            CreativeInventoryInfo::CATEGORY_ITEMS,
        );

        parent::__construct($identifier, "Pierre compressée");


        $this->initComponent('compressed_cobblestone', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Une simple pierre compressée.",
            "§6---",
            "§eRareté: " . TextFormat::GRAY . "COMMUN",
        ]);
    }
}