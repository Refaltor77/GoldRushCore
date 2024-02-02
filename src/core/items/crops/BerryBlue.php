<?php

namespace core\items\crops;

use core\settings\Ids;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\component\FoodComponent;
use customiesdevs\customies\item\component\UseAnimationComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\Block;
use pocketmine\item\Food;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class BerryBlue extends Food implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Baie bleue';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('berry_blue', $inventory);
        $this->addComponent(new FoodComponent(false));
        $this->addComponent(new UseAnimationComponent(UseAnimationComponent::ANIMATION_EAT));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Une délicieuse baie bleue, symbole de fraîcheur et de gourmandise.",
            "§6---",
            "§l§eUtilité:§r§f Idéale pour la production d'alcool de speed.",
            "§r§6---",
            "§eRareté: " . TextFormat::GRAY . "COMMUNE"
        ]);
    }

    public function getBlock(?int $clickedFace = null): Block
    {
        return CustomiesBlockFactory::getInstance()->get(Ids::BERRY_BLUE_CROPS_STAGE_0);
    }


    public function getFoodRestore(): int
    {
        return 2;
    }

    public function getSaturationRestore(): float
    {
        return 5.0;
    }
}