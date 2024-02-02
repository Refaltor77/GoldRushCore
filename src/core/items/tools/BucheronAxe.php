<?php

namespace core\items\tools;

use customiesdevs\customies\item\component\DurabilityComponent;
use customiesdevs\customies\item\component\HandEquippedComponent;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\Axe;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;

class BucheronAxe extends Axe implements ItemComponents
{
    use ItemComponentsTrait;

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Hache du bucheron';

        $info = ToolTier::NETHERITE();

        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_AXE,
        );

        parent::__construct($identifier, $name, $info);

        $this->initComponent('bucheron_axe', $inventory);

        $this->addComponent(new DurabilityComponent($this->getMaxDurability()));
        $this->addComponent(new MaxStackSizeComponent(1));
        $this->addComponent(new HandEquippedComponent(true));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f Une hache plus ancienne que l'ère de §6GoldRush§f...\nUne hache ayant appartenu au démon le plus puissant,\ndu moins avant qu'il ne se dissimule parmi nous.",
            "§6---",
            "§eAttack: §f" . $this->getAttackPoints() . " ",
            "§eDurability: §f" . $this->getMaxDurability() . " ",
            "§eEfficiency: §f" . $this->getMiningEfficiency(true),
            "§6---",
            "§eRareté: " . TextFormat::GREEN . "RARE"
        ]);
    }

    public function getMaxDurability(): int
    {
        return 30000;
    }

    public function getAttackPoints(): int
    {
        return VanillaItems::DIAMOND_AXE()->getAttackPoints();
    }

    protected function getBaseMiningEfficiency(): float
    {
        return VanillaItems::DIAMOND_AXE()->getBaseMiningEfficiency() + 10;
    }
}