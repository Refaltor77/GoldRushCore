<?php

namespace core\items\foods\alcools;

use customiesdevs\customies\item\component\FoodComponent;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
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

class AlcoolForce extends Food implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Alcool de force';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('alcool_force', $inventory);
        $this->addComponent(new FoodComponent(true));
        $this->addComponent(new UseAnimationComponent(UseAnimationComponent::ANIMATION_DRINK));
        $this->addComponent(new MaxStackSizeComponent($this->getMaxStackSize()));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f L'alcool de force\nrevitalise et donne du courage,\nmais à consommer avec sagesse.",
            "§6---",
            "§l§eEffets:§r§f Force §l1§r pendant §l30 secondes",
            "§6---",
            "§eRareté: " . TextFormat::GRAY . "COMMON"
        ]);
    }

    public function getMaxStackSize(): int
    {
        return 64;
    }

    public function onConsume(Living $consumer): void
    {
        $consumer->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * 30, 0, false));
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