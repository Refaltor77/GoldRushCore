<?php

namespace core\items\tools\lumberjack;

use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;

class BoneAxe_7 extends AbstractWoodenAxe
{

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Hache du bucheron §fNiveau §e§l' . $this->getLvl();
        $tier = ToolTier::DIAMOND();
        $textureName = 'bone_axe_6';
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
            ->setInt("level", 7)
            ->setInt("block_break", 0));
        $this->setNamedTag($nbt);

    }

    public function getLvl(): int
    {
        return 7;
    }

    public function getAttackPoints(): int
    {
        return VanillaItems::IRON_AXE()->getAttackPoints();
    }

    public function getMaxDurability(): int
    {
        return VanillaItems::IRON_AXE()->getMaxDurability();
    }

    protected function getBaseMiningEfficiency(): float
    {
        return VanillaItems::DIAMOND_AXE()->getBaseMiningEfficiency() + 70;
    }

}