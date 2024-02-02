<?php

namespace core\items\ingots\raws;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class GoldRaw extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Or brute';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('gold_raw', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f L'or, un métal précieux étincelant\nsymbolisant la pureté et la richesse\nrenforce vos armures avec une élégance inégalée.",
            "§6---",
            "§eRareté: " . TextFormat::GOLD . "LEGEND"
        ]);
    }
}