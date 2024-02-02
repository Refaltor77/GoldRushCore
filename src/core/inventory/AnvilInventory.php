<?php

namespace core\inventory;

use core\blocks\blocks\AnvilBlock;
use core\enchantments\VanillaEnchantment;
use core\events\RepairItemEvent;
use Exception;
use pocketmine\block\Air;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Planks;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\inventory\TemporaryInventory;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\TieredTool;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\sound\AnvilBreakSound;
use pocketmine\world\sound\AnvilUseSound;

class AnvilInventory extends \pocketmine\block\inventory\AnvilInventory implements TemporaryInventory{

    const TAG_REPAIR_COST = "RepairCost";

    const SLOT_INPUT = 0;
    const SLOT_MATERIAL = 1;

    public function __construct(Position $holder){
        parent::__construct($holder, 3, VanillaBlocks::ANVIL(), WindowTypes::ANVIL);
    }

    protected function onSlotChange(int $index, Item $before): void
    {
        $slotMaterial = $this->getItem(self::SLOT_MATERIAL);
        $slotInput = $this->getItem(self::SLOT_INPUT);

        foreach ($this->getContents(true) as $index => $item) {

        }

        parent::onSlotChange($index, $before);
    }

    /**
     * @param Player $player
     * @param int $filterIndex
     * @param string[] $filterStrings
     * @return Item
     */
    public function getResultItem(Player $player, int $filterIndex, array $filterStrings): Item{
        $item = $this->getItem(self::SLOT_INPUT);
        $material = $this->getItem(self::SLOT_MATERIAL);
        $newName = $filterStrings[$filterIndex];
        $cost = 0;
        $baseCost = $this->getRepairCost($item) + $this->getRepairCost($material);
        $renameCost = 0;
        $result = clone $item;

        if(strlen($newName) < 1){
            $result->clearCustomName();
            $renameCost = 1;
        }elseif($result->getName() !== $newName){
            $result->setCustomName($newName);
            $renameCost = 1;
        }
        if(!$material->isNull()){
            $isBook = $material->getTypeId() === ItemTypeIds::ENCHANTED_BOOK;

            if($result instanceof Durable && $this->isRepairable($result, $material)){
                $this->repairDurabilityWithMaterial($result, $material, $cost);
            }else{
                //repairing with same type of item and enchantment
                if(!$isBook){
                    if((!$result instanceof Durable || !$material instanceof Durable || !$result->equals($material, false, false))){
                        throw new Exception("You can only repair non material item with same type of item");
                    }
                    $this->repairDurability($result, $material, $cost);
                }

                [$hasCompatible, $hasIncompatible] = $this->combineEnchantments($isBook, $result, $material, $cost);
                if($hasIncompatible && !$hasCompatible){
                    throw new Exception("Non compatible enchantments found to combine to.");
                }
            }
        }

        $cost += $renameCost;
        $cost += $baseCost;
        if($renameCost === $cost && $renameCost > 0 && $cost >= 40){ //only renaming cost
            $cost = 39;
        }
        $ev = new RepairItemEvent($player, $item, $material, $result, $cost);
        $ev->call();
        if($ev->isCancelled()){
            return $item; //returns input item as result if cancelled
        }

        if(!$player->isCreative()){
            $xpManager = $player->getXpManager();
            $currentXp = $xpManager->getXpLevel();
            if($currentXp < $cost){
                throw new Exception("player does not have enough experience level, expected $cost, got $currentXp");
            }
            $xpManager->subtractXpLevels($cost);
        }
        if(mt_rand(0, 50) === 0){
            $block = $this->holder->getWorld()->getBlock($this->holder);

            if($block instanceof AnvilBlock){
                if($block->getDamage() === AnvilBlock::UNDAMAGED){
                    $block->setDamage(AnvilBlock::SLIGHTLY_DAMAGED);
                }else if($block->getDamage() === AnvilBlock::SLIGHTLY_DAMAGED){
                    $block->setDamage(AnvilBlock::VERY_DAMAGED);
                }else{
                    $block = VanillaBlocks::AIR();
                    $player->getWorld()->addSound($block->getPosition(), new AnvilBreakSound());
                }
                $this->holder->getWorld()->setBlock($this->holder, $block);
                if(!$block instanceof Air){
                    $player->getWorld()->addSound($block->getPosition(), new AnvilUseSound());
                }
            }
        }else{
            $player->getWorld()->addSound($player->getEyePos(), new AnvilUseSound());
        }
        return $result;
    }

    public function repairDurabilityWithMaterial(Durable $input, Item $material, int &$cost): void{
        $durability = $input->getDamage();
        $maxRepairDamage = $input->getMaxDurability() / 4;
        $repairDamage = min($durability, $maxRepairDamage);

        if($repairDamage > 0){
            $damage = $durability;

            for($i = 0; $repairDamage > 0 && $i < $material->getCount(); $i++){
                $damage -= $repairDamage;
                $repairDamage = min($damage, $maxRepairDamage);
                $cost++;
            }
            $input->setDamage($damage);
        }
    }

    public function repairDurability(Durable $input, Durable $material, int &$cost): void{
        $repairAmount = $input->getMaxDurability() - $input->getDamage();
        $damage = ($material->getMaxDurability() - $material->getDamage()) + $input->getMaxDurability() * 12 / 100;
        $damage = $input->getDamage() - ($repairAmount + $damage);

        if($damage < 0){
            $damage = 0;
        }
        if($damage < $input->getDamage()){
            $input->setDamage($damage);
            $cost += 2;
        }
    }

    public function combineEnchantments(bool $isBook, Item $input, Item $material, int &$cost): array{
        $hasIncompatible = false;
        $hasCompatible = false;

        foreach($material->getEnchantments() as $enchantment){
            $type = $enchantment->getType();

            if(!$type instanceof VanillaEnchantment && $type instanceof Enchantment){
                $type = EnchantmentIdMap::getInstance()->fromId(EnchantmentIdMap::getInstance()->toId($type));
            }
            if($type instanceof VanillaEnchantment){
                $isCompatible = $isBook ? true : $type->isItemCompatible($input);

                if(!$isBook && $isCompatible){
                    foreach($input->getEnchantments() as $enchant){
                        $enchantType = $enchant->getType();

                        if($enchantType instanceof VanillaEnchantment && $enchantType->isIncompatibleWith($type)){
                            $isCompatible = false;
//                                    $cost++;
                        }
                    }
                }
                if(!$isCompatible){
                    $hasIncompatible = true;
                    continue;
                }
                $hasCompatible = true;

                $resultLevel = $input->getEnchantmentLevel($type);
                $costLevel = $enchantment->getLevel();

                if($costLevel === $resultLevel){
                    $costLevel++; //increase 1 level if input has same enchantment

                    if($costLevel > $type->getMaxLevel()){
                        $costLevel = $type->getMaxLevel();
                    }
                }else{
                    $costLevel = max($costLevel, $resultLevel); //whichever got higher enchantment level will be applied
                }
                $input->addEnchantment(new EnchantmentInstance($type, $costLevel));

                $rarityCost = $type->getRarityCost();
                if($isBook){
                    $rarityCost = max(1, $rarityCost / 2);
                }
                $cost += $rarityCost * $costLevel;

                if($input->getCount() > 1){
                    $cost = 40;
                }
            }
        }
        return [$hasCompatible, $hasIncompatible];
    }

    public function getRepairCost(Item $item): int{
        if($item->isNull()){
            return 0;
        }
        $tag = $item->getNamedTag()->getTag(self::TAG_REPAIR_COST);
        return $tag instanceof IntTag ? $tag->getValue() : 0;
    }

    public function isRepairable(Item $input, Item $material): bool{
        if($input instanceof TieredTool){
            switch($input->getTier()->name()){
                case "wood":
                    return $material instanceof Planks;
                case "stone":
                    return $material->getTypeId() === BlockTypeIds::COBBLESTONE;
                case "iron":
                    return $material->getTypeId() === ItemTypeIds::IRON_INGOT;
                case "gold":
                    return $material->getTypeId() === ItemTypeIds::GOLD_INGOT;
                case "diamond":
                    return $material->getTypeId() === ItemTypeIds::DIAMOND;
            }
        }
        return match($input->getId()){
            ItemTypeIds::LEATHER_CAP, ItemTypeIds::LEATHER_TUNIC, ItemTypeIds::LEATHER_PANTS, ItemTypeIds::LEATHER_BOOTS => $material->getTypeId() === ItemTypeIds::LEATHER,
            ItemTypeIds::IRON_HELMET, ItemTypeIds::IRON_CHESTPLATE, ItemTypeIds::IRON_LEGGINGS, ItemTypeIds::IRON_BOOTS => $material->getTypeId() === ItemTypeIds::IRON_INGOT,
            ItemTypeIds::GOLDEN_HELMET, ItemTypeIds::GOLDEN_CHESTPLATE, ItemTypeIds::GOLDEN_LEGGINGS, ItemTypeIds::GOLDEN_BOOTS => $material->getTypeId() === ItemTypeIds::GOLD_INGOT,
            ItemTypeIds::DIAMOND_HELMET, ItemTypeIds::DIAMOND_CHESTPLATE, ItemTypeIds::DIAMOND_LEGGINGS, ItemTypeIds::DIAMOND_BOOTS => $material->getTypeId() === ItemTypeIds::DIAMOND,
            ItemTypeIds::NETHERITE_HELMET, ItemTypeIds::NETHERITE_CHESTPLATE, ItemTypeIds::NETHERITE_LEGGINGS, ItemTypeIds::NETHERITE_BOOTS, ItemTypeIds::NETHERITE_AXE, ItemTypeIds::NETHERITE_PICKAXE, ItemTypeIds::NETHERITE_HOE, ItemTypeIds::NETHERITE_SHOVEL, ItemTypeIds::NETHERITE_SWORD => $material->getId() === ItemTypeIds::NETHERITE_INGOT,
            ItemTypeIds::TURTLE_HELMET => $material->getTypeId() === ItemTypeIds::SCUTE,
            default => $input->equals($material, false),
        };
    }
}