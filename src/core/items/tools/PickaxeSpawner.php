<?php

namespace core\items\tools;

use core\utils\Utils;
use customiesdevs\customies\item\component\DurabilityComponent;
use customiesdevs\customies\item\component\HandEquippedComponent;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Axe;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\Pickaxe;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

class PickaxeSpawner extends Pickaxe implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Pioche à spawner';

        $info = ToolTier::NETHERITE();

        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_AXE,
        );

        parent::__construct($identifier, $name, $info);

        $this->initComponent('pickaxe_spawner', $inventory);

        $this->addComponent(new DurabilityComponent($this->getMaxDurability()));
        $this->addComponent(new MaxStackSizeComponent(1));
        $this->addComponent(new HandEquippedComponent(true));
        $component = Utils::getDiggerComponent($this, $this->getBaseMiningEfficiency());
        if (!is_null($component)) $this->addComponent($component);

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Une pioche qui provient des démons.\nConceptualisée avec de l'or et\ndu platine, elle fait partie des\nartefacts les plus recherchés\nde Sylvanar.",
            "§6---",
            "§eDurability: §f" . $this->getMaxDurability() . " ",
            "§6---",
            "§eRareté: " . TextFormat::GREEN . "RARE"
        ]);
    }

    public function getMaxDurability(): int
    {
        return 3;
    }

    public function getAttackPoints(): int
    {
        return VanillaItems::DIAMOND_AXE()->getAttackPoints();
    }

    protected function getBaseMiningEfficiency(): float
    {
        return 2;
    }
}