<?php

namespace core\items\foods\alcools;

use customiesdevs\customies\item\component\FoodComponent;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\component\UseAnimationComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\entity\Living;
use pocketmine\item\Food;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class BottleJobs extends Food implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Bouteille des métiers";


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('bottle_jobs', $inventory);
        $this->addComponent(new FoodComponent(true));
        $this->addComponent(new UseAnimationComponent(UseAnimationComponent::ANIMATION_DRINK));
        $this->addComponent(new MaxStackSizeComponent($this->getMaxStackSize()));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f La bouteille des métiers\nvous revitalise l'esprit\net vous donne de la force",
            "§6---",
            "§l§eEffets:§r§f Xp métiers §6X2§f pendant 30 minutes",
            "§6---",
            "§cATTENTION: Fonctionne uniquement si\naucun event x2 est en cours.",
            "§6---",
            "§eRareté: " . TextFormat::LIGHT_PURPLE . "EPIC"
        ]);
    }

    public function getMaxStackSize(): int
    {
        return 64;
    }

    public function onConsume(Living $consumer): void
    {
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