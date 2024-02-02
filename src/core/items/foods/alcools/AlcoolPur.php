<?php

namespace core\items\foods\alcools;

use customiesdevs\customies\item\component\FoodComponent;
use customiesdevs\customies\item\component\UseAnimationComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Living;
use pocketmine\item\Food;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class AlcoolPur extends Food implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Alcool pur";


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('alcool_pur', $inventory);
        $this->addComponent(new FoodComponent(true));
        $this->addComponent(new UseAnimationComponent(UseAnimationComponent::ANIMATION_DRINK));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f L'alcool pur,\nun goût fort, il est fais pour\nles guerriers",
            "§6---",
            "§eRareté: " . TextFormat::GRAY . "COMMON"
        ]);
    }


    public function onConsume(Living $consumer): void
    {
        $consumer->getEffects()->add(new EffectInstance(VanillaEffects::NAUSEA(), 20 * 10, 2, false));
        parent::onConsume($consumer);
    }

    public function getFoodRestore(): int
    {
        return 0;
    }

    public function requiresHunger(): bool
    {
        return false;
    }

    public function getSaturationRestore(): float
    {
        return 0.0;
    }
}