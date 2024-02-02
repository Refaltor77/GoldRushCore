<?php

namespace core\enchantments\types;

use core\enchantments\EnchantmentTrait;
use core\enchantments\VanillaEnchantment;
use core\items\tools\amethyst\AmethystHammer;
use core\items\tools\copper\CopperHammer;
use core\items\tools\emerald\EmeraldHammer;
use core\items\tools\gold\GoldHammer;
use core\items\tools\platinum\PlatinumHammer;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\Item;
use pocketmine\item\Sword;
use pocketmine\item\Tool;
use pocketmine\item\ToolTier;
use pocketmine\lang\KnownTranslationFactory;

class EfficiencyEnchantment extends Enchantment implements VanillaEnchantment{
    use EnchantmentTrait;

    public function __construct(){
        parent::__construct(KnownTranslationFactory::enchantment_digging(), Rarity::COMMON, ItemFlags::DIG, ItemFlags::SHEARS, 5);
    }

    public function getId(): string{
        return "efficiency";
    }

    public function getMcpeId(): int{
        return EnchantmentIds::EFFICIENCY;
    }

    public function getMinCost(int $level): int{
        return 1 + 10 * ($level - 1);
    }

    public function getMaxCost(int $level): int{
        return $this->getMinCost($level) + 50;
    }

    public function isItemCompatible(Item $item): bool{

        $bannedItemClass = [
            PlatinumHammer::class,
            GoldHammer::class,
            AmethystHammer::class,
            EmeraldHammer::class,
            CopperHammer::class
        ];

        if (in_array($item::class, $bannedItemClass)) {
            return false;
        }


        return $item instanceof Tool && !$item instanceof Sword;
    }
}