<?php

namespace core\items\tools\lumberjack;

use core\settings\Ids;
use core\utils\Utils;
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
use pocketmine\nbt\tag\CompoundTag;

class AbstractWoodenAxe extends Axe implements ItemComponents
{
    use ItemComponentsTrait;

    const ALL_LVL = [
        2 => 384 * 2,
        3 => 768 * 2,
        4 => 1152 * 2,
        5 => 1408 * 2,
        6 => 1792 * 2,
        7 => 2048 * 2,
        8 => 3072 * 2,
    ];
    const LVL_ITEM = [
        2 => Ids::BONE_AXE_2,
        3 => Ids::BONE_AXE_3,
        4 => Ids::BONE_AXE_4,
        5 => Ids::BONE_AXE_5,
        6 => Ids::BONE_AXE_6,
        7 => Ids::BONE_AXE_7,
        8 => Ids::BONE_AXE_8,
    ];

    public function __construct(ItemIdentifier $identifier, string $name, ToolTier $tier, string $textureName, array $lore)
    {

        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_AXE,
        );

        parent::__construct($identifier, $name, $tier);

        $nbt = $this->getNamedTag();
        if (is_null($nbt->getTag("lumberjack"))) {
            $nbt->setTag("lumberjack", CompoundTag::create()
                ->setInt("level", 1)
                ->setInt("block_break", 0));
            $this->setNamedTag($nbt);
        }

        $this->initComponent($textureName, $inventory);


        $this->addComponent(new DurabilityComponent($this->getMaxDurability()));
        $this->addComponent(new MaxStackSizeComponent(1));
        $this->addComponent(new HandEquippedComponent(true));
        $component = Utils::getDiggerComponent($this, $this->getBaseMiningEfficiency());
        if (!is_null($component)) $this->addComponent($component);

        $this->setLore($lore);
    }

    public function getMaxDurability(): int
    {
        return VanillaItems::WOODEN_AXE()->getMaxDurability();
    }

    protected function getBaseMiningEfficiency(): float
    {
        return VanillaItems::WOODEN_AXE()->getBaseMiningEfficiency();
    }

    public function getLvl(): int
    {
        return 1;
    }

    public function calculBarXp(): string
    {
        /** @var CompoundTag $tag */
        $tag = $this->getNamedTag()->getTag("lumberjack");
        if (!is_null($tag)) {
            $break = $tag->getInt("block_break");
            $lvl = $tag->getInt("level");
            if ($lvl === 8) return "§e|||||||||||||||||||| - NIVEAU MAX";

            $xpRequis = self::ALL_LVL[$lvl + 1];
            $xpActuel = $break;


            if ($xpActuel <= 0) {
                $barresRemplies = 0;
                $barresRestantes = 20 - $barresRemplies;
            } else {
                $barresRemplies = intval(($xpActuel / $xpRequis) * 20);
                $barresRestantes = 20 - $barresRemplies;
            }

            // Construisez la chaîne de barre d'XP en utilisant str_repeat
            $barreXP = "§a" . str_repeat("|", $barresRemplies) . "§c" . str_repeat("|", $barresRestantes);

            return $barreXP;
        } else {
            return "404";
        }
    }

    public function getAttackPoints(): int
    {
        return VanillaItems::WOODEN_AXE()->getAttackPoints();
    }

    public function isUnbreakable(): bool
    {
        return true;
    }
}