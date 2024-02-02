<?php

namespace core\items\fossils;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class FossilTyrannosaureRex extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Fossile de tyrannosaure rex';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('fossil_tyrannosaure_rex', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Un fossile de Tyrannosaure Rex, \nune relique impressionnante\ndu redoutable prédateur \nqui a autrefois dominé\nles terres de Minecraft.",
            "§6---",
            "§eRareté: " . TextFormat::GOLD . "ULTRA LEGEND"
        ]);
    }
}