<?php

namespace core\items\tools\lumberjack;

use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;


class BoneAxe_2 extends AbstractWoodenAxe
{

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Hache du bucheron §fNiveau §e§l' . $this->getLvl();
        $tier = ToolTier::WOOD();
        $textureName = 'bone_axe_1';
        $lore = [
            "§6---",
            "§l§eDescription:§r§f La hache du bûcheron a été créée\ndans une forge mystique.\nUn nom revient souvent dans la\nlégende : §6Sylvanar.",
            "§6---",
            "§e§lNiveau: §r§f" . $this->getLvl() . " ",
            "§eXP: §f" . '' . " ",
            "§6---",
            "§eRareté: §f§l§eCOMMUN"
        ];
        parent::__construct($identifier, $name, $tier, $textureName, $lore);

        $nbt = $this->getNamedTag();
        $nbt->setTag("lumberjack", CompoundTag::create()
            ->setInt("level", 2)
            ->setInt("block_break", 0));
    }

    public function getLvl(): int
    {
        return 2;
    }

    public function getAttackPoints(): int
    {
        return VanillaItems::WOODEN_AXE()->getAttackPoints() + 1;
    }

    public function getMaxDurability(): int
    {
        return VanillaItems::WOODEN_AXE()->getMaxDurability();
    }

    protected function getBaseMiningEfficiency(): float
    {
        return VanillaItems::DIAMOND_AXE()->getBaseMiningEfficiency() + 10;
    }

}