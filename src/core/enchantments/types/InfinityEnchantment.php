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

class InfinityEnchantment extends Enchantment implements VanillaEnchantment{
    use EnchantmentTrait;

    public function __construct(){
        parent::__construct(KnownTranslationFactory::enchantment_arrowInfinite(), Rarity::MYTHIC, ItemFlags::BOW, ItemFlags::NONE, 1);
    }

    public function getId(): string{
        return "infinity";
    }

    public function getMcpeId(): int{
        return EnchantmentIds::INFINITY;
    }

    public function getMinCost(int $level): int{
        return 20;
    }

    public function getMaxCost(int $level): int{
        return 50;
    }

    public function getIncompatibles(): array{
        return [EnchantmentIds::MENDING];
    }

    public function isItemCompatible(Item $item): bool{
        return $item->getTypeId() === ItemTypeIds::BOW;
    }
}