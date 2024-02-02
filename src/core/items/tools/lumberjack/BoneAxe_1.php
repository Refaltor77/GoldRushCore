<?php

namespace core\items\tools\lumberjack;

use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;


class BoneAxe_1 extends AbstractWoodenAxe
{

    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Hache du bucheron §fNiveau §e§l1';
        $tier = ToolTier::WOOD();
        $textureName = 'bone_axe_0';
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
        if (is_null($nbt->getTag("lumberjack"))) {
            $nbt->setTag("lumberjack", CompoundTag::create()
                ->setInt("level", 1)
                ->setInt("block_break", 0));
            $this->setNamedTag($nbt);
        }
    }


    public function getLvl(): int
    {
        return 1;
    }

    public function getAttackPoints(): int
    {
        return VanillaItems::WOODEN_AXE()->getAttackPoints();
    }

    public function getMaxDurability(): int
    {
        return VanillaItems::WOODEN_AXE()->getMaxDurability();
    }

    protected function getBaseMiningEfficiency(): float
    {
        return VanillaItems::DIAMOND_AXE()->getBaseMiningEfficiency() + 5;
    }

}