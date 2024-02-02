<?php

namespace core\items\tools\lumberjack;

use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\TextFormat;

class BoneAxe_9 extends AbstractWoodenAxe
{

    public function __construct(ItemIdentifier $identifier)
    {
        $name = '§r§fHache en os';
        $tier = ToolTier::NETHERITE();
        $textureName = 'bone_axe_6';
        $lore = [
            "§6---",
            "§l§eDescription:§r§f Une hache en os, elle est très efficace pour couper du bois.",
            "§6---",
            "§eAttack: §f" . $this->getAttackPoints() . " ",
            "§eDurability: §f" . $this->getMaxDurability() . " ",
            "§eEfficiency: §f" . $this->getMiningEfficiency(true),
            "§6---",
            "§eRareté: " . TextFormat::LIGHT_PURPLE . "EPIC"
        ];
        parent::__construct($identifier, $name, $tier, $textureName, $lore);

        $nbt = $this->getNamedTag();
        if (is_null($nbt->getTag("lumberjack"))) {
            $nbt->setTag("lumberjack", CompoundTag::create()
                ->setInt("level", 9)
                ->setInt("block_break", 0));
            $this->setNamedTag($nbt);
        }
    }

    public function getAttackPoints(): int
    {
        return VanillaItems::NETHERITE_AXE()->getAttackPoints();
    }

    public function getMaxDurability(): int
    {
        return VanillaItems::NETHERITE_AXE()->getMaxDurability();
    }

    protected function getBaseMiningEfficiency(): float
    {
        return VanillaItems::NETHERITE_AXE()->getBaseMiningEfficiency();
    }

}