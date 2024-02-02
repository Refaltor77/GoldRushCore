<?php

namespace core\items\crops;

use core\settings\Ids;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class SeedsObsidian extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Graine d'obsidienne";


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('seeds_wheat_obsidian', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f La graine d'obsidienne permet de cultiver\ndes blocs d'obsidienne, une ressource essentielle\ndans les bases claims.",
            "§6---",
            "§l§eUtilité:§r§f Idéale pour la production d'obsidienne",
            "§r§6---",
            "§eRareté: " . TextFormat::GRAY . "COMMUNE"
        ]);
    }

    public function getBlock(?int $clickedFace = null): Block
    {
        return CustomiesBlockFactory::getInstance()->get(Ids::WHEAT_OBSIDIAN_STAGE_0);
    }
}