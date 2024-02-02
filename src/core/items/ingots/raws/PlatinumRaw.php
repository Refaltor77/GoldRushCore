<?php

namespace core\items\ingots\raws;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class PlatinumRaw extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Platine brute';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('platinum_raw', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Le platine est un métal précieux rare\nil est souvent utilisé dans des applications industrielles importantes.",
            "§6---",
            "§eRareté: " . TextFormat::GREEN . "RARE"
        ]);
    }
}