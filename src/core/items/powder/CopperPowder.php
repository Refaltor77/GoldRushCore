<?php

namespace core\items\powder;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class CopperPowder extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Poudre en cuivre';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('copper_powder', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Le cuivre est un métal rare\nutiliser dans chez les nobles.",
            "§6---",
            "§eRareté: " . TextFormat::GRAY . "COMMON"
        ]);
    }
}