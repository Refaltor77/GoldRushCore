<?php

namespace core\items\sapling;

use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class SpectralSapling extends Item implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Pousse d'arbre spectral";


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_NATURE,
            CreativeInventoryInfo::GROUP_SAPLING,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('spectral_sapling', $inventory);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Le pousse d'arbre spectral\npousse uniquement dans la\ndimenssion spectral",
            "§6---",
            "§eRareté: " . TextFormat::LIGHT_PURPLE . "EPIC"
        ]);
    }

    public function getBlock(?int $clickedFace = null): Block
    {
        return CustomiesBlockFactory::getInstance()->get('goldrush:spectral_sapling_placer');
    }
}