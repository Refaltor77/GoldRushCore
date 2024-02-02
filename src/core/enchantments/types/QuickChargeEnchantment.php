<?php

namespace core\enchantments\types;

use core\enchantments\EnchantmentTrait;
use core\enchantments\ItemFlags;
use core\enchantments\VanillaEnchantment;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ItemFlags as PMItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\lang\KnownTranslationFactory;

class QuickChargeEnchantment extends Enchantment implements VanillaEnchantment{
    use EnchantmentTrait;

    public function __construct(){
        parent::__construct(KnownTranslationFactory::enchantment_crossbowPiercing(), Rarity::UNCOMMON, ItemFlags::CROSSBOW, PMItemFlags::NONE, 3);
    }

    public function getId(): string{
        return "quick_charge";
    }

    public function getMcpeId(): int{
        return EnchantmentIds::QUICK_CHARGE;
    }

    public function getMinCost(int $level): int{
        return 12 + ($level - 1) * 20;
    }

    public function getMaxCost(int $level): int{
        return 50;
    }

    public function isItemCompatible(Item $item): bool{
        return false;
    }
}