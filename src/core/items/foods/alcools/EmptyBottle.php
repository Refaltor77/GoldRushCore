<?php

namespace core\items\foods\alcools;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class EmptyBottle extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Bouteille vide';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('bottle_empty', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Une bouteille vide pour\ncrée de l'alcool",
            "§6---",
            "§l§eUtilité:§r§f Crée de l'alcool avec le barril a raisin puis mélangé cette\nalcool a des baies pour crée des alcools\nà effets.",
            "§6---",
            "§eRareté: " . TextFormat::GRAY . "COMMON"
        ]);
    }
}