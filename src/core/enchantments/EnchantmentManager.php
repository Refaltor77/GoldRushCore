<?php

namespace core\enchantments;

use core\enchantments\VanillaEnchantment;
use core\utils\Utils;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\item\Bow;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\FishingRod;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\player\Player;
use pocketmine\item\enchantment\ItemFlags as PMItemFlags;
use pocketmine\utils\SingletonTrait;

class EnchantmentManager{
    use SingletonTrait;

    /** @var VanillaEnchantment[] */
    private array $enchantments = [];

    public function __construct(){
        self::setInstance($this);
    }

    public function startup(): void{

    }

    public function registerEnchantment(VanillaEnchantment $enchantment): void{
        $mcpeId = $enchantment->getMcpeId();

        EnchantmentIdMap::getInstance()->register($mcpeId, $enchantment);
        StringToEnchantmentParser::getInstance()->override($enchantment->getId(), fn() => $enchantment);
        $this->enchantments[$enchantment->getId()] = $enchantment;
    }

    public function handleDamage(EntityDamageEvent $event): void{

    }

    /**
     * @param Item $item
     * @param bool $global
     * @param bool $treasure
     * @return Enchantment[]|null
     */
    public function getEnchantmentForItem(Item $item, bool $global = true, bool $treasure = true): ?array{
        $enchantments = [];
        $flags = match(true){
            $item instanceof Armor => [PMItemFlags::ARMOR, PMItemFlags::HEAD, PMItemFlags::TORSO, PMItemFlags::LEGS, PMItemFlags::FEET],
            $item instanceof Axe => [PMItemFlags::AXE],
            $item instanceof Sword => [PMItemFlags::SWORD],
            $item instanceof Pickaxe || $item instanceof Shovel => [PMItemFlags::DIG],
            $item instanceof Bow => [PMItemFlags::BOW],
            $item instanceof FishingRod => [PMItemFlags::FISHING_ROD],
            $item->getTypeId() === ItemTypeIds::BOW => [ItemFlags::CROSSBOW],
            default => []
        };

        if($global){
            $flags[] = PMItemFlags::ALL;
        }
        if(count($flags) >= 1){
            if($item instanceof Axe){
                $flags[] = PMItemFlags::DIG;
            }
            foreach($this->enchantments as $enchantment){
                foreach($flags as $flag){
                    if($enchantment->isItemFlagValid($flag)){
                        if(!$treasure && $enchantment->isTreasure()){
                            continue;
                        }
                        $enchantments[$enchantment->getId()] = $enchantment;
                    }
                }
            }
        }
        foreach($this->enchantments as $enchantment){
            foreach([PMItemFlags::ARMOR, PMItemFlags::HEAD, PMItemFlags::TORSO, PMItemFlags::LEGS, PMItemFlags::FEET] as $flag){
                if($enchantment->isItemFlagValid($flag)){
                    $enchantments[$enchantment->getId()] = $enchantment;
                }
            }
        }
        return count($enchantments) < 1 ? null : $enchantments;
    }

    /**
     * @param bool $treasure
     * @param bool $normal
     * @return VanillaEnchantment[]|Enchantment[]
     */
    public function getAllEnchantments(bool $treasure = true, bool $normal = false): array{
        $data = [];
        $enchantments = $this->enchantments;

        foreach($enchantments as $key => $enchant){
            if(!$treasure && $enchant->isTreasure()){
                continue;
            }
            if($normal){
                $vanilla = EnchantmentIdMap::getInstance()->fromId($enchant->getMcpeId());

                if($vanilla !== null){
                    $data[$key] = $vanilla;
                }
            }else{
                $data[$key] = $enchant;
            }
        }
        return $data;
    }

    /**
     * @param bool $treasure
     * @param bool $normal
     * @return VanillaEnchantment[]|Enchantment[]
     */
    public function getTreasureEnchantments(bool $normal = false): array{
        $data = [];
        $enchantments = $this->enchantments;

        foreach($enchantments as $key => $enchant){
            if(!$enchant->isTreasure()){
                continue;
            }
            if($normal){
                $vanilla = EnchantmentIdMap::getInstance()->fromId($enchant->getMcpeId());

                if($vanilla !== null){
                    $data[$key] = $vanilla;
                }
            }else{
                $data[$key] = $enchant;
            }
        }
        return $data;
    }

    /**
     * @return VanillaEnchantment[]
     */
    public function getEnchantments(): array{
        return $this->enchantments;
    }
}
