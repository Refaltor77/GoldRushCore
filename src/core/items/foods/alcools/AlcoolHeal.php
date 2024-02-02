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

class AlcoolHeal extends Food implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Alcool de soin";


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('alcool_heal', $inventory);
        $this->addComponent(new FoodComponent(true));
        $this->addComponent(new UseAnimationComponent(UseAnimationComponent::ANIMATION_DRINK));
        $this->addComponent(new MaxStackSizeComponent($this->getMaxStackSize()));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f L'alcool de soin\nsoigne et donne de le la rigueur,\nmais à consommer avec modération.",
            "§6---",
            "§l§eEffets:§r§f Régéneration §l2§r pendant §l5 secondes§r et Absorption §l1§r pendant §l60 secondes",
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
        $heal = $consumer->getHealth();
        $maxHeal = $consumer->getMaxHealth();
        if ($heal + 2 > $maxHeal) {
            $consumer->setHealth($maxHeal);
        } else {
            $consumer->setHealth($consumer->getHealth() + 2);
        }


        $consumer->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 20 * 5, 1, false));
        $consumer->getEffects()->add(new EffectInstance(VanillaEffects::ABSORPTION(), 20 * 60 * 2, 0, false));
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