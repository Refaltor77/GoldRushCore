<?php

namespace core\items\powder;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class AmethystPowder extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Poudre en améthyste';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_ORE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('amethyst_powder', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f L'améthyste, une gemme violette d'une beauté envoûtante,\nréputée pour sa capacité à canaliser la magie et renforcer \nnos armures.",
            "§6---",
            "§eRareté: " . TextFormat::LIGHT_PURPLE . "EPIC"
        ]);
    }
}