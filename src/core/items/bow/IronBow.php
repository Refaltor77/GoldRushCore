<?php

namespace core\items\bow;

use customiesdevs\customies\item\component\ChargeableComponent;
use customiesdevs\customies\item\component\DurabilityComponent;
use customiesdevs\customies\item\component\UseAnimationComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Bow;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\Releasable;
use pocketmine\utils\TextFormat;

class IronBow extends Bow implements ItemComponents, Releasable
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Arc en fer';

        parent::__construct($identifier, $name);

        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_ITEMS,
            CreativeInventoryInfo::NONE
        );

        $this->initComponent('bow_standby', $inventory);

        $this->addComponent(new DurabilityComponent($this->getMaxDurability()));
        $this->addComponent(new ChargeableComponent(1.5));
        $this->addComponent(new UseAnimationComponent(4));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Un arc en fer, une arme de base pour les archers.",
            "§6---",
            "§eDurability: §f" . $this->getMaxDurability() . " ",
            "§6---",
            "§eRareté: " . TextFormat::WHITE . "COMMUN"
        ]);
    }
}