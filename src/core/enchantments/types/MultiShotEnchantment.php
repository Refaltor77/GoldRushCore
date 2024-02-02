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
use pocketmine\item\ItemTypeIds;
use pocketmine\lang\KnownTranslationFactory;

class MultiShotEnchantment extends Enchantment implements VanillaEnchantment{
    use EnchantmentTrait;

    public function __construct(){
        parent::__construct(KnownTranslationFactory::enchantment_crossbowMultishot(), Rarity::RARE, ItemFlags::CROSSBOW, PMItemFlags::NONE, 1);
    }

    public function getId(): string{
        return "multishot";
    }

    public function getMcpeId(): int{
        return EnchantmentIds::MULTISHOT;
    }

    public function getMinCost(int $level): int{
        return 20;
    }

    public function getMaxCost(int $level): int{
        return 50;
    }

    public function getIncompatibles(): array{
        return [EnchantmentIds::PIERCING];
    }

    public function isItemCompatible(Item $item): bool{
        return false;
    }
}