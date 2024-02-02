<?php

namespace core\items\crops;

use core\settings\BlockIds;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class FlowerPercent extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Fleur de Camouflage';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('flower_percent_item', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Une délicieuse fleur violette,\nsymbole de fraîcheur et de discrétion.",
            "§6---",
            "§l§eUtilité:§r§f Cache 1% dans votre chunk",
            "§r§6---",
            "§eRareté: " . TextFormat::GOLD . "LEGEND"
        ]);
    }

    public function getBlock(?int $clickedFace = null): Block
    {
        return CustomiesBlockFactory::getInstance()->get(BlockIds::FLOWER_PERCENT);
    }
}