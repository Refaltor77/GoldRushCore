<?php

namespace core\items\powder;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class GoldWhitePowder extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Poudre d'or or blanc";


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('gold_white_powder', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f L'or blanc, un alliage précieux, marie la beauté\n de l'or à la pureté du platine, créant\n des armures d'une élégance incomparable.",
            "§6---",
            "§eRareté: " . TextFormat::GOLD . "LEGEND"
        ]);
    }
}