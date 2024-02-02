<?php

namespace core\items\fossils;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class FossilStegosaurus extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Fossile de stegosaurus';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('fossil_stegosaurus', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Un fossile de Stegosaurus, \nune relique énigmatique\nd'un herbivore cuirassé qui a autrefois arpenté\nle monde de Minecraft.",
            "§6---",
            "§eRareté: " . TextFormat::LIGHT_PURPLE . "EPIC"
        ]);
    }
}