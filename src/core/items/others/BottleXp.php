<?php

namespace core\items\others;

use core\player\CustomPlayer;
use customiesdevs\customies\item\component\FoodComponent;
use customiesdevs\customies\item\component\UseAnimationComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\entity\Living;
use pocketmine\item\Food;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class BottleXp extends Food implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = "Bouteille d'expérience";


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('experience_bottle', $inventory);
        $this->addComponent(new FoodComponent(true));
        $this->addComponent(new UseAnimationComponent(UseAnimationComponent::ANIMATION_DRINK));

        $this->generateBaseNbt();

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Un récipient d'expérience enchantée prêt à être utilisé\npour améliorer vos enchantements ou échanger contre\nde nouvelles compétences.",
            "§6---",
            "§l§eExperience: §l" . $this->getNamedTag()->getString('total_xp') . "§r§axp",
            "§eRareté: " . TextFormat::GRAY . "COMMON"
        ]);
    }

    public function generateBaseNbt(): void
    {
        if ($this->getNamedTag()->getString('total_xp', 'none') === 'none') {
            $this->getNamedTag()->setString('total_xp', "10");
        }
    }

    public function onConsume(Living $consumer): void
    {
        $this->generateBaseNbt();
        $xp = $this->getNamedTag()->getString('total_xp');
        if ($consumer instanceof CustomPlayer) {
            $consumer->getXpManager()->addXp($xp);
        }
        parent::onConsume($consumer);
    }

    public function requiresHunger(): bool
    {
        return false;
    }

    public function getFoodRestore(): int
    {
        return 0;
    }

    public function getSaturationRestore(): float
    {
        return 0.0;
    }
}