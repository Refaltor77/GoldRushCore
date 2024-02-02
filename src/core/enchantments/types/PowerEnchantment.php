<?php

namespace core\enchantments\types;

use core\enchantments\EnchantmentTrait;
use core\enchantments\VanillaEnchantment;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\lang\KnownTranslationFactory;

class PowerEnchantment extends Enchantment implements VanillaEnchantment{
    use EnchantmentTrait;

    public function __construct(){
        parent::__construct(KnownTranslationFactory::enchantment_arrowDamage(), Rarity::COMMON, ItemFlags::BOW, ItemFlags::NONE, 5);
    }

    public function getId(): string{
        return "power";
    }

    public function getMcpeId(): int{
        return EnchantmentIds::POWER;
    }

    public function getMinCost(int $level): int{
        return 1 + ($level - 1) * 10;
    }

    public function getMaxCost(int $level): int{
        return $this->getMinCost($level) + 15;
    }

    public function isItemCompatible(Item $item): bool{
        return $item->getTypeId() === ItemTypeIds::BOW;
    }
}