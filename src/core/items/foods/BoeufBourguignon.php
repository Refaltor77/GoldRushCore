<?php

namespace core\items\foods;

use customiesdevs\customies\item\component\FoodComponent;
use customiesdevs\customies\item\component\UseAnimationComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Food;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\TextFormat;

class BoeufBourguignon extends Food implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Boeuf bourguignon préparé par §l§eTeamPanda9457';


        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::NONE,
        );

        parent::__construct($identifier, $name);

        $this->initComponent('boeuf_bourguignon', $inventory);
        $this->addComponent(new FoodComponent(false));
        $this->addComponent(new UseAnimationComponent(UseAnimationComponent::ANIMATION_EAT));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Un boeuf bourguignon cuisiné à la paysanne\ndes échalotes, du persil, tout ça\npréparé par TeamPanda9457, célèbre cuisinier\nqui a su se créer un public auprès\ndes pionniers de GoldRush.",
            "§6---",
            "§eRareté: " . TextFormat::GOLD . "LEGEND"
        ]);
    }

    public function getFoodRestore(): int
    {
        return 20;
    }

    public function requiresHunger(): bool
    {
        return true;
    }

    public function getSaturationRestore(): float
    {
        return 15.0;
    }
}