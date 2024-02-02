<?php

namespace core\enchantments\types;

use core\enchantments\EnchantmentTrait;
use core\enchantments\VanillaEnchantment;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\lang\KnownTranslationFactory;

class RiptideEnchantment extends Enchantment implements VanillaEnchantment{
    use EnchantmentTrait;

    public function __construct(){
        parent::__construct(KnownTranslationFactory::enchantment_tridentRiptide(), Rarity::RARE, ItemFlags::TRIDENT, ItemFlags::NONE, 3);
    }

    public function getId(): string{
        return "riptide";
    }

    public function getMcpeId(): int{
        return EnchantmentIds::RIPTIDE;
    }

    public function getMinCost(int $level): int{
        return 10 + $level * 7;
    }

    public function getMaxCost(int $level): int{
        return 50;
    }

    public function getIncompatibles(): array{
        return [EnchantmentIds::LOYALTY, EnchantmentIds::CHANNELING];
    }

    public function isItemCompatible(Item $item): bool{
        return false;
    }
}