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
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\utils\TextFormat;

class AlcoolForcePuissant extends Food implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Alcool de force puissant';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('alcool_force1', $inventory);
        $this->addComponent(new FoodComponent(true));
        $this->addComponent(new UseAnimationComponent(UseAnimationComponent::ANIMATION_DRINK));
        $this->addComponent(new MaxStackSizeComponent($this->getMaxStackSize()));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f L'alcool de force\nrevitalise et donne du courage,\nmais à consommer avec sagesse.",
            "§6---",
            "§l§eEffets:§r§f Force §l2§r pendant §l30 secondes",
            "§6---",
            "§eRareté: " . TextFormat::GRAY . "COMMON"
        ]);
    }

    public function getMaxStackSize(): int
    {
        return 64;
    }

    public function deserializeCompoundTag(CompoundTag $tag): void
    {
        parent::deserializeCompoundTag($tag);

        $enchTag = $tag->getListTag(Item::TAG_ENCH);
        if ($enchTag !== null && count($enchTag->getValue()) === 0) {
            $tag->removeTag(Item::TAG_ENCH);
        }
    }

    public function onConsume(Living $consumer): void
    {
        $consumer->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * 30, 1, false));
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

    protected function serializeCompoundTag(CompoundTag $tag): void
    {
        parent::serializeCompoundTag($tag);
        $tag->setTag(Item::TAG_ENCH, new ListTag([], NBT::TAG_Compound));
    }
}