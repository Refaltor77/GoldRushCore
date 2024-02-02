<?php

namespace core\items\foods;

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

class SpectralRottenFlesh extends Food implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Chair de zombie spectral";


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('spectral_rotten_flesh', $inventory);
        $this->addComponent(new FoodComponent(false));
        $this->addComponent(new UseAnimationComponent(UseAnimationComponent::ANIMATION_EAT));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Une chair hautement toxique\nutilisé pour invoqué le démon de la dimension\nspectral...",
            "§6---",
            "§eRareté: " . TextFormat::GOLD . "LEGEND"
        ]);
    }

    public function getFoodRestore(): int
    {
        return 2;
    }

    public function requiresHunger(): bool
    {
        return true;
    }

    public function onConsume(Living $consumer): void
    {
        $consumer->getEffects()->add(new EffectInstance(VanillaEffects::WITHER(), 20 * 5, 2));
        parent::onConsume($consumer);
    }

    public function getSaturationRestore(): float
    {
        return 2.0;
    }
}