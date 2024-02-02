<?php

namespace core\enchantments\types;

use core\enchantments\EnchantmentTrait;
use core\enchantments\VanillaEnchantment;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\lang\KnownTranslationFactory;

class UnbreakingEnchantment extends Enchantment implements VanillaEnchantment{
    use EnchantmentTrait;

    public function __construct(){
        parent::__construct(KnownTranslationFactory::enchantment_durability(), Rarity::UNCOMMON, ItemFlags::DIG | ItemFlags::ARMOR | ItemFlags::FISHING_ROD | ItemFlags::BOW, ItemFlags::TOOL | ItemFlags::CARROT_STICK | ItemFlags::ELYTRA, 3);
    }

    public function getId(): string{
        return "unbreaking";
    }

    public function getMcpeId(): int{
        return EnchantmentIds::UNBREAKING;
    }

    public function getMinCost(int $level): int{
        return 5 + ($level - 1) * 8;
    }

    public function getMaxCost(int $level): int{
        return $this->getMinCost($level) + 50;
    }


    public function isItemCompatible(Item $item): bool{
        return $item instanceof Armor || $item instanceof Tool;
    }
}