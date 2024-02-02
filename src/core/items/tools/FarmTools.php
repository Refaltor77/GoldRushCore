<?php

namespace core\items\tools;

use core\blocks\crops\ObsidianCrops;
use core\items\backpacks\BackpackFarm;
use core\items\crops\SeedsObsidian;
use core\Main;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\utils\Utils;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\component\DurabilityComponent;
use customiesdevs\customies\item\component\HandEquippedComponent;
use customiesdevs\customies\item\component\MaxStackSizeComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\CustomiesItemFactory;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Crops;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Beetroot;
use pocketmine\item\Hoe;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\ItemUseResult;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\ItemUseOnBlockSound;

class FarmTools extends Hoe implements ItemComponents
{
    use ItemComponentsTrait;


    public function __construct(ItemIdentifier $identifier)
    {
        $name = 'Farmtools';

        $info = ToolTier::NETHERITE();

        $inventory = new CreativeInventoryInfo(
            CreativeInventoryInfo::CATEGORY_EQUIPMENT,
            CreativeInventoryInfo::GROUP_HOE,
        );

        parent::__construct($identifier, $name, $info);

        $this->initComponent('farmtools', $inventory);

        $this->addComponent(new DurabilityComponent($this->getMaxDurability()));
        $this->addComponent(new MaxStackSizeComponent(1));
        $this->addComponent(new HandEquippedComponent(true));

        $this->setLore([
            "§6---",
            "§l§eDescription:§r§f La FarmTools a été conceptualisée dans la\nforge mystique du grand forgeron nommé OneUP.",
            "§6---",
            "§eAttack: §f" . $this->getAttackPoints() . " ",
            "§eDurability: §f" . $this->getMaxDurability() . " ",
            "§6---",
            "§eRareté: " . TextFormat::GREEN . "RARE"
        ]);
    }


    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems): ItemUseResult
    {
        $world = $blockClicked->getPosition()->getWorld();
        $pos = $blockClicked->getPosition();

        $use = false;
        $newPos = $pos->add(1, 0, 0);
        if (in_array($world->getBlock($newPos)->getTypeId(), [VanillaBlocks::GRASS()->getTypeId(), VanillaBlocks::DIRT()->getTypeId()])) {
            $world->setBlock($newPos, VanillaBlocks::FARMLAND());
            $use = true;
        }
        $newPos = $pos->add(-1, 0, 0);
        if (in_array($world->getBlock($newPos)->getTypeId(), [VanillaBlocks::GRASS()->getTypeId(), VanillaBlocks::DIRT()->getTypeId()])) {
            $world->setBlock($newPos, VanillaBlocks::FARMLAND());
            $use = true;
        }
        $newPos = $pos->add(0, 0, 1);
        if (in_array($world->getBlock($newPos)->getTypeId(), [VanillaBlocks::GRASS()->getTypeId(), VanillaBlocks::DIRT()->getTypeId()])) {
            $world->setBlock($newPos, VanillaBlocks::FARMLAND());
            $use = true;
        }
        $newPos = $pos->add(0, 0, -1);
        if (in_array($world->getBlock($newPos)->getTypeId(), [VanillaBlocks::GRASS()->getTypeId(), VanillaBlocks::DIRT()->getTypeId()])) {
            $world->setBlock($newPos, VanillaBlocks::FARMLAND());
            $use = true;
        }
        $newPos = $pos->add(-1, 0, -1);
        if (in_array($world->getBlock($newPos)->getTypeId(), [VanillaBlocks::GRASS()->getTypeId(), VanillaBlocks::DIRT()->getTypeId()])) {
            $world->setBlock($newPos, VanillaBlocks::FARMLAND());
            $use = true;
        }
        $newPos = $pos->add(1, 0, -1);
        if (in_array($world->getBlock($newPos)->getTypeId(), [VanillaBlocks::GRASS()->getTypeId(), VanillaBlocks::DIRT()->getTypeId()])) {
            $world->setBlock($newPos, VanillaBlocks::FARMLAND());
            $use = true;
        }
        $newPos = $pos->add(1, 0, 1);
        if (in_array($world->getBlock($newPos)->getTypeId(), [VanillaBlocks::GRASS()->getTypeId(), VanillaBlocks::DIRT()->getTypeId()])) {
            $world->setBlock($newPos, VanillaBlocks::FARMLAND());
            $use = true;
        }
        $newPos = $pos->add(-1, 0, 1);
        if (in_array($world->getBlock($newPos)->getTypeId(), [VanillaBlocks::GRASS()->getTypeId(), VanillaBlocks::DIRT()->getTypeId()])) {
            $world->setBlock($newPos, VanillaBlocks::FARMLAND());
            $use = true;
        }

        if ($use) {
            $this->applyDamage(1);
            $player->getPosition()->getWorld()->addSound($player->getEyePos(), new ItemUseOnBlockSound(VanillaBlocks::FARMLAND()));
        }

        return ItemUseResult::SUCCESS();
    }


    public function onDestroyBlock(Block $block, array &$returnedItems): bool
    {
        $blocks = [
            $block,
            $block->getPosition()->getWorld()->getBlockAt($block->getPosition()->getX(), $block->getPosition()->getY(), $block->getPosition()->getZ()),
            $block->getPosition()->getWorld()->getBlockAt($block->getPosition()->getX() + 1, $block->getPosition()->getY(), $block->getPosition()->getZ()),
            $block->getPosition()->getWorld()->getBlockAt($block->getPosition()->getX() - 1, $block->getPosition()->getY(), $block->getPosition()->getZ()),
            $block->getPosition()->getWorld()->getBlockAt($block->getPosition()->getX(), $block->getPosition()->getY(), $block->getPosition()->getZ() + 1),
            $block->getPosition()->getWorld()->getBlockAt($block->getPosition()->getX(), $block->getPosition()->getY(), $block->getPosition()->getZ() - 1),
            $block->getPosition()->getWorld()->getBlockAt($block->getPosition()->getX() + 1, $block->getPosition()->getY(), $block->getPosition()->getZ() + 1),
            $block->getPosition()->getWorld()->getBlockAt($block->getPosition()->getX() - 1, $block->getPosition()->getY(), $block->getPosition()->getZ() + 1),
            $block->getPosition()->getWorld()->getBlockAt($block->getPosition()->getX() + 1, $block->getPosition()->getY(), $block->getPosition()->getZ() - 1),
            $block->getPosition()->getWorld()->getBlockAt($block->getPosition()->getX() - 1, $block->getPosition()->getY(), $block->getPosition()->getZ() - 1),
        ];


        $set = false;
        foreach ($blocks as $blockTarget) {
            if ($blockTarget instanceof Crops) {
                $pos = $blockTarget->getPosition();
                $player = $blockTarget->getPosition()->getWorld()->getNearestEntity($blockTarget->getPosition(), 7, CustomPlayer::class);
                $drops = $blockTarget->getDrops($this);

                foreach ($drops as $drop) {
                    if ($blockTarget->getAge() >= Crops::MAX_AGE) {
                        $set = true;
                        if (!$drop->getBlock() instanceof Air) {
                            $dropsBlock = $blockTarget->getDrops($this);
                            $itemBreak = VanillaItems::DIAMOND_HOE();
                            $pos->getWorld()->useBreakOn($pos, $itemBreak, $player,true,  $dropsBlock);
                            Utils::timeout(function () use ($pos, $drop) : void {
                                $pos->getWorld()->setBlock($pos, $drop->getBlock());
                            }, 5);
                            unset($drops[array_search($drop, $drops)]);
                        }
                    }
                }

                if (!$set) {
                    Utils::timeout(function () use ($pos, $blockTarget) : void {
                        $pos->getWorld()->setBlock($pos, $blockTarget);
                    }, 5);
                }

            } else if ($blockTarget instanceof ObsidianCrops) {
                $pos = $blockTarget->getPosition();
                $player = $blockTarget->getPosition()->getWorld()->getNearestEntity($blockTarget->getPosition(), 7, CustomPlayer::class);
                $drops = $blockTarget->getDrops($this);
                foreach ($drops as $drop) {

                    if ($blockTarget->getAge() >= 3) {
                        if ($drop instanceof SeedsObsidian) {
                            $set = true;
                            $blockTarget->setAge(0);
                            $dropsBlock = $blockTarget->getDrops($this);
                            $itemBreak = VanillaItems::DIAMOND_HOE();
                            Utils::timeout(function () use ($pos, $drop) : void {
                                $pos->getWorld()->setBlock($pos, CustomiesBlockFactory::getInstance()->get(Ids::WHEAT_OBSIDIAN_STAGE_0));
                            }, 5);
                            unset($drops[array_search($drop, $drops)]);
                        }
                    }
                    if($blockTarget === $block) continue;
                    $blockTarget->getPosition()->getWorld()->dropItem($blockTarget->getPosition(), $drop);
                }

                if (!$set) {
                    $blockTarget->setAge(0);
                    Utils::timeout(function () use ($pos) : void {
                        $pos->getWorld()->setBlock($pos, CustomiesBlockFactory::getInstance()->get(Ids::WHEAT_OBSIDIAN_STAGE_0));
                    }, 5);
                }
            }
        }


        return true;
    }
}