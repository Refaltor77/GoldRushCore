<?php


namespace core\inventory;

use core\enchantments\EnchantmentManager;
use core\enchantments\types\SoulSpeedEnchantment;
use core\enchantments\VanillaEnchantment;
use core\events\EnchantItemEvent;
use core\inventory\type\RecipeInventory;
use core\utils\Utils;
use Exception;
use pocketmine\block\Air;
use pocketmine\block\Bookshelf;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\inventory\TemporaryInventory;
use pocketmine\item\Book;
use pocketmine\item\enchantment\EnchantingOption;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Sword;
use pocketmine\item\TieredTool;
use pocketmine\item\ToolTier;
use pocketmine\network\mcpe\protocol\PlayerEnchantOptionsPacket;
use pocketmine\network\mcpe\protocol\types\Enchant;
use pocketmine\network\mcpe\protocol\types\EnchantOption;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use pocketmine\utils\Random;
use pocketmine\world\Position;

class EnchantInventory extends \pocketmine\block\inventory\EnchantInventory implements TemporaryInventory, RecipeInventory{

    const SLOT_INPUT = 0;
    const SLOT_MATERIAL = 1;

    /** @var EnchantOption[] */
    private array $options = [];

    public function __construct(Position $holder){
        parent::__construct($holder);
    }

    protected function onSlotChange(int $index, Item $before): void{

        if($index === self::SLOT_INPUT){
            foreach($this->viewers as $viewer){
                $this->sendEnchantmentOptions($this->getItem($index), $viewer);
            }
        }
        parent::onSlotChange($index, $before);
    }

    public function sendEnchantmentOptions(Item $input, Player $player): void{
        $random = new Random($player->getXpManager()->getCurrentTotalXp());
        $options = [];

        if(!$input->isNull() && !$input->hasEnchantments()){
            $bookshelfCount = $this->countBookshelves();
            $baseCost = ($random->nextBoundedInt(8) + 1) + floor($bookshelfCount >> 1) + $random->nextBoundedInt($bookshelfCount + 1);
            $topCost = floor(max($baseCost / 3, 1));
            $middleCost = floor($baseCost * 2 / 3 + 1);
            $bottomCost = floor(max($baseCost, $bookshelfCount * 2));

            $options = [
                $this->createOption($random, $input, $topCost),
                $this->createOption($random, $input, $middleCost),
                $this->createOption($random, $input, $bottomCost),
            ];
        }


        //$player->getNetworkSession()->sendDataPacket(PlayerEnchantOptionsPacket::create($options));
        $player->getNetworkSession()->getInvManager()?->syncEnchantingTableOptions($options);
    }


    public function onOpen(Player $who): void
    {
        parent::onOpen($who);
    }

    public function createOption(Random $random, Item $input, int $optionCost): EnchantingOption{

        $cost = $optionCost;
        $ability = $this->getEnchantability($input);
        $enchantAbility = (int)$cost + 1 + $random->nextBoundedInt(intval($ability / 4 + 1)) + $random->nextBoundedInt(intval($ability / 4 + 1));
        $enchantAbility = Utils::clamp(round($cost + $cost * $enchantAbility), 1, PHP_INT_MAX);
        $enchantments = $this->getAvailableEnchantments($cost, $input);
        /** @var EnchantmentInstance[] $list */
        $list = [];

        if(count($enchantments) >= 1){
            $weightedEnchantment = $this->getRandomWeightedEnchantment($random, $enchantments);

            if($weightedEnchantment !== null){
                $list[] = $weightedEnchantment;
            }

            while($random->nextBoundedInt(50) <= $enchantAbility){
                if(count($list) >= 1){
                    $enchantments = $this->filterEnchantments($enchantments, $list[array_key_last($list)]);
                }
                if(count($enchantments) < 1){
                    break;
                }
                $weightedEnchantment = $this->getRandomWeightedEnchantment($random, $enchantments);

                if($weightedEnchantment !== null){
                    $list[] = $weightedEnchantment;
                }
                $enchantAbility /= 2;
            }
        }

        $count = $this->countBookshelves();
        $chance = mt_rand(0, 10);

        if ($count >= 15) {
            $chance = mt_rand(0, 100);
        }

        if ($count < 15 && $count >= 10) {
            $chance = mt_rand(0, 50);
        }

        if ($count < 10 && $count >= 5) {
            $chance = mt_rand(0, 20);
        }


        $enchants = [];
        foreach($list as $enchantment){
            $type = $enchantment->getType();

            if($type instanceof VanillaEnchantment){
                $niv = 1;
                if ($chance >= 40) $niv = 2;
                if ($chance >= 90) $niv = 3;

                if ($type->getMaxLevel() < $niv) $niv = $type->getMaxLevel();


                $enchants[] = new EnchantmentInstance($type, $niv);
            }
        }
        if ($input->hasEnchantments()) {
            $enchants = [];
        }
        $slot = count($this->options);
        $this->options[] = new EnchantingOption($optionCost, "OMG TESTING", $enchants);
        return $this->options[$slot];
    }

    public function getResultItem(Player $player, int $netId): ?Item{
        $option = $this->options[$netId] ?? null;

        if($option === null){
            throw new Exception("Failed to find enchantment option for network id: $netId");
        }
        $this->options = [];
        $enchantments = array_merge($option->getEquipActivatedEnchantments(), $option->getHeldActivatedEnchantments(), $option->getSelfActivatedEnchantments());
        foreach($enchantments as $index => $enchant){
            $enchantments[$index] = new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId($enchant->getId()), $enchant->getLevel());
        }
        $result = $this->getItem(self::SLOT_INPUT);
        $cost = $option->getCost();
        $expectedMaterial = $netId + 1;

        $ev = new EnchantItemEvent($player, $result, $enchantments, $cost, $expectedMaterial);
        $ev->call();
        if($ev->isCancelled()){
            return $result; //should return the item player was trying to enchant with no enchantments
        }
        $result = $ev->getInput();
        $cost = $ev->getLevelCost();
        $expectedMaterial = $ev->getMaterialsCost();
        $enchantments = $ev->getEnchantments();

        if(!$player->isCreative()){
            $xpManager = $player->getXpManager();
            $currentXp = $xpManager->getXpLevel();
            $material = $this->getItem(self::SLOT_MATERIAL);
            $currentMaterial = $material->getCount();

            if($currentXp < $cost){
                throw new Exception("Expected player to have xp level of $cost, but received $currentXp");
            }
            if($material->getTypeId() !== ItemTypeIds::DYE && $material->getStateId() !== 4){
                throw new Exception("Invalid material item");
            }
            if($currentMaterial < $expectedMaterial){
                throw new Exception("Expected material count to be $expectedMaterial, but received $currentMaterial");
            }
            $xpManager->subtractXpLevels($cost);
        }
        foreach($enchantments as $enchantment){
            $result->addEnchantment($enchantment);
        }
        return $result;
    }

    /**
     * @param int $cost
     * @param Item $input
     * @return EnchantmentInstance[]
     */
    public function getAvailableEnchantments(int $cost, Item $input): array{
        $list = [];

        foreach(EnchantmentManager::getInstance()->getEnchantments() as $enchantment){
            if(!$enchantment instanceof SoulSpeedEnchantment){
                if($input instanceof Book || $enchantment->isItemCompatible($input)){
                    for($i = $enchantment->getMaxLevel(); $i > 0; $i--){
                        if($cost >= $enchantment->getMinCost($i) && $cost <= $enchantment->getMaxCost($i)){
                            if($enchantment instanceof Enchantment){
                                $list[] = new EnchantmentInstance($enchantment, $i);
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $list;
    }

    /**
     * @param EnchantmentInstance[] $enchantments
     * @param EnchantmentInstance $last
     * @return EnchantmentInstance[]
     */
    public function filterEnchantments(array $enchantments, EnchantmentInstance $last): array{
        foreach($enchantments as $index => $enchantment){
            $filterType = $enchantment->getType();
            $type = $last->getType();

            if($type instanceof VanillaEnchantment && $filterType instanceof VanillaEnchantment){
                if($type->isIncompatibleWith($filterType)){
                    unset($enchantments[$index]);
                }
            }
        }
        return $enchantments;
    }

    /**
     * @param Random $random
     * @param EnchantmentInstance[] $enchantments
     * @return EnchantmentInstance|null
     */
    public function getRandomWeightedEnchantment(Random $random, array $enchantments): ?EnchantmentInstance{
        $totalWeight = 0;

        foreach($enchantments as $enchantment){
            $totalWeight += $enchantment->getType()->getRarity();
        }
        $i = $random->nextBoundedInt($totalWeight);

        foreach($enchantments as $enchantment){
            $i -= $enchantment->getType()->getRarity();

            if($i < 0){
                return $enchantment;
            }
        }
        return null;
    }

    public function countBookshelves(): int{
        $shelves = 0;
        $pos = $this->getHolder();
        $world = $pos->getWorld();

        for ($x = -1; $x <= 1; $x++){
            for ($z = -1; $z <= 1; $z++){
                if($z !== 0 || $x !== 0){
                    for($y = 0; $y <= 1; $y++){
                        $block = $world->getBlock((clone $pos)->add($x, $y, $z));

                        if(($x !== 0 || $z !== 0) && $block instanceof Air){
                            $block = $world->getBlock((clone $pos)->add($x * 2, $y, $z * 2));

                            if($block instanceof Bookshelf){
                                $shelves++;
                            }

                            if($x !== 0 && $z !== 0){
                                $block = $world->getBlock((clone $pos)->add($x * 2, $y, $z));

                                if($block instanceof Bookshelf){
                                    $shelves++;
                                }
                                $block = $world->getBlock((clone $pos)->add($x, $y, $z * 2));

                                if($block instanceof Bookshelf){
                                    $shelves++;
                                }
                            }
                        }
                        if($shelves >= 15){
                            return 15;
                        }
                    }
                }
            }
        }
        return $shelves;
    }

    public function getEnchantability(Item $input): int{
        if($input instanceof TieredTool){
            return $input->getTier()->getEnchantability();
        }
        return match ($input->getTypeId()) {
            ItemTypeIds::LEATHER_CAP, ItemTypeIds::LEATHER_TUNIC, ItemTypeIds::LEATHER_PANTS, ItemTypeIds::LEATHER_BOOTS => 15,
            ItemTypeIds::CHAINMAIL_HELMET, ItemTypeIds::CHAINMAIL_CHESTPLATE, ItemTypeIds::CHAINMAIL_LEGGINGS, ItemTypeIds::CHAINMAIL_BOOTS => 12,
            ItemTypeIds::IRON_HELMET, ItemTypeIds::IRON_CHESTPLATE, ItemTypeIds::IRON_LEGGINGS, ItemTypeIds::IRON_BOOTS,
            ItemTypeIds::GOLDEN_HELMET, ItemTypeIds::GOLDEN_CHESTPLATE, ItemTypeIds::GOLDEN_LEGGINGS, ItemTypeIds::GOLDEN_BOOTS => 25,
            ItemTypeIds::DIAMOND_HELMET, ItemTypeIds::DIAMOND_CHESTPLATE, ItemTypeIds::DIAMOND_LEGGINGS, ItemTypeIds::DIAMOND_BOOTS => 10,
            ItemTypeIds::NETHERITE_HELMET, ItemTypeIds::NETHERITE_CHESTPLATE, ItemTypeIds::NETHERITE_LEGGINGS, ItemTypeIds::NETHERITE_BOOTS, ItemTypeIds::NETHERITE_AXE, ItemTypeIds::NETHERITE_PICKAXE, ItemTypeIds::NETHERITE_HOE, ItemTypeIds::NETHERITE_SHOVEL, ItemTypeIds::NETHERITE_SWORD => 15,
            default => 1,
        };
    }
}