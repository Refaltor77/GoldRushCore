<?php

namespace core\items\tools\lumberjack;

use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\TextFormat;

class BoneAxe_6 extends AbstractWoodenAxe
{

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Hache du bucheron §fNiveau §e§l' . $this->getLvl();
        $tier = ToolTier::IRON();
        $textureName = 'bone_axe_5';
        $lore = [
            "§6---",
            "§l§eDescription:§r§f La hache du bûcheron a été créée\ndans une forge mystique.\nUn nom revient souvent dans la\nlégende : §6Sylvanar.",
            "§6---",
            "§eAttack: §f" . $this->getAttackPoints() . " ",
            "§eDurability: §f" . $this->getMaxDurability() . " ",
            "§eEfficiency: §f" . $this->getMiningEfficiency(true),
            "§6---",
            "§eRareté: " . TextFormat::BLUE . "UNCOMMON"
        ];
        parent::__construct($identifier, $name, $tier, $textureName, $lore);

        $nbt = $this->getNamedTag();

        $nbt->setTag("lumberjack", CompoundTag::create()
            ->setInt("level", 6)
            ->setInt("block_break", 0));
        $this->setNamedTag($nbt);

    }


    public function getLvl(): int
    {
        return 6;
    }


    public function getAttackPoints(): int
    {
        return VanillaItems::IRON_AXE()->getAttackPoints() + 1;
    }

    public function getMaxDurability(): int
    {
        return VanillaItems::IRON_AXE()->getMaxDurability();
    }

    protected function getBaseMiningEfficiency(): float
    {
        return VanillaItems::DIAMOND_AXE()->getBaseMiningEfficiency() + 50;
    }

}