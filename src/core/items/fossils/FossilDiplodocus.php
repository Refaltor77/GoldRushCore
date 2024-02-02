<?php

namespace core\items\fossils;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class FossilDiplodocus extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Fossile de diplodocus';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('fossil_diplodocus', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Un fossile de Diplodocus, un\ntémoignage silencieux du\nmajestueux géant qui a autrefois erré dans le\nmonde cubique de Minecraft.",
            "§6---",
            "§eRareté: " . TextFormat::GREEN . "RARE"
        ]);
    }
}