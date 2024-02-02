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

class ChannelingEnchantment extends Enchantment implements VanillaEnchantment{
    use EnchantmentTrait;

    public function __construct(){
        parent::__construct(KnownTranslationFactory::enchantment_tridentChanneling(), Rarity::MYTHIC, ItemFlags::TRIDENT, ItemFlags::NONE, 1);
    }

    public function getId(): string{
        return "channeling";
    }

    public function getMcpeId(): int{
        return EnchantmentIds::CHANNELING;
    }

    public function getMinCost(int $level): int{
        return 25;
    }

    public function getMaxCost(int $level): int{
        return 50;
    }

    public function getIncompatibles(): array{
        return [EnchantmentIds::RIPTIDE];
    }

    public function isItemCompatible(Item $item): bool{
        return false;
    }
}