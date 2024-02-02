<?php

namespace core\items\fossils;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class FossilPterodactyle extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Fossile de pterodactyle';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('fossil_pterodactyle', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Un fossile de Pterodactyle, rappel \nsilencieux d'un prédateur des cieux \nqui a jadis dominé les cieux de Minecraft.",
            "§6---",
            "§eRareté: " . TextFormat::GREEN . "RARE"
        ]);
    }
}