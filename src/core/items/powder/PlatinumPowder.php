<?php

namespace core\items\powder;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class PlatinumPowder extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Poudre en platine';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('platinum_powder', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Le platine est un métal précieux rare\nil est souvent utilisé dans des applications industrielles importantes.",
            "§6---",
            "§eRareté: " . TextFormat::GREEN . "RARE"
        ]);
    }
}