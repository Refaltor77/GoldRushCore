<?php

namespace core\items\box;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class BlackKey extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = '§6- §rClé black gold §6-';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('key_black_gold', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f La clé black gold permet d'ouvrir une box black gold.",
            "§6---",
            "§eRareté: " . TextFormat::GOLD . "ULTRA LEGEND"
        ]);
    }
}