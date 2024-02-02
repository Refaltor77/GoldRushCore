<?php

namespace core\enchantments\types;

use core\enchantments\EnchantmentTrait;
use core\enchantments\VanillaEnchantment;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\ProtectionEnchantment;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\lang\KnownTranslationFactory;

class FeatherFallingEnchantment extends ProtectionEnchantment implements VanillaEnchantment{
    use EnchantmentTrait;

    public function __construct(){
        parent::__construct(KnownTranslationFactory::enchantment_protect_fall(), Rarity::UNCOMMON, ItemFlags::FEET, ItemFlags::NONE, 4, 2.5, [
            EntityDamageEvent::CAUSE_FALL
        ]);
    }

    public function getId(): string{
        return "feather_falling";
    }

    public function getMcpeId(): int{
        return EnchantmentIds::FEATHER_FALLING;
    }

    public function getMinCost(int $level): int{
        return 5 + ($level - 1) * 6;
    }

    public function getMaxCost(int $level): int{
        return $this->getMinCost($level) + 6;
    }

    public function isItemCompatible(Item $item): bool{
        return $item instanceof Armor && $item->getArmorSlot() === ArmorInventory::SLOT_FEET;
    }
}