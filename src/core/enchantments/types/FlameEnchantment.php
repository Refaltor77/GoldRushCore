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

class FlameEnchantment extends Enchantment implements VanillaEnchantment{
    use EnchantmentTrait;

    public function __construct(){
        parent::__construct(KnownTranslationFactory::enchantment_arrowFire(), Rarity::RARE, ItemFlags::BOW, ItemFlags::NONE, 1);
    }

    public function getId(): string{
        return "flame";
    }

    public function getMcpeId(): int{
        return EnchantmentIds::FLAME;
    }

    public function getMinCost(int $level): int{
        return 20;
    }

    public function getMaxCost(int $level): int{
        return 50;
    }

    public function isItemCompatible(Item $item): bool{
        return $item->getTypeId() === ItemTypeIds::BOW;
    }
}