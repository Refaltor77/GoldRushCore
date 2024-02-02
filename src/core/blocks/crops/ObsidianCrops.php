<?php

namespace core\blocks\crops;

use core\blocks\BlockHistoryData;
use core\cooldown\BasicCooldown;
use core\items\tools\FarmTools;
use core\Main;
use core\managers\jobs\JobsManager;
use core\particles\CropGrowthParticle;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\sounds\BoneMealUseSound;
use core\utils\Utils;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Block;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\BlockTypeTags;
use pocketmine\block\Farmland;
use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\utils\SupportType;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wheat;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\GlowBerriesPickSound;

class ObsidianCrops extends CustomCropsNoDirtFertil
{
    public int $ageCustom;

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, int $age)
    {
        $this->ageCustom = $age;
        parent::__construct($idInfo, $name, $typeInfo);
    }


    protected function canBeSupportedBy(Block $block) : bool{
        return $block->getTypeId() === BlockTypeIds::FARMLAND;
    }

    public function getAge(): int
    {
        $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
            $this->getPosition()->getX(),
            $this->getPosition()->getY(),
            $this->getPosition()->getZ()
        );

        if (!is_null($data)) {
            return $data->getAge();
        }

        return $this->ageCustom;
    }

    public function setAge(int $age): CustomCropsNoDirtFertil
    {
        $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
            $this->getPosition()->getX(),
            $this->getPosition()->getY(),
            $this->getPosition()->getZ()
        );

        $this->ageCustom = $age;

        if (is_null($data)) {
            return $this;
        }

        $data->setAge($age);

        return $this;
    }


    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{

        if ($this->getPosition()->getWorld()->getFolderName() === "demo") return false;
        $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
            $this->getPosition()->getX(),
            $this->getPosition()->getY(),
            $this->getPosition()->getZ()
        );
        if (is_null($data)) {
            Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->setBlockDataAt(
                $this->getPosition()->getX(),
                $this->getPosition()->getY(),
                $this->getPosition()->getZ(),
                new BlockHistoryData($this->ageCustom)
            );
            $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
                $this->getPosition()->getX(),
                $this->getPosition()->getY(),
                $this->getPosition()->getZ()
            );
        }



        if($data->getAge() < 3 && $item instanceof Fertilizer && $player instanceof CustomPlayer){
            $item->pop();
            $player->getInventory()->setItemInHand($item);
            $this->getPosition()->getWorld()->addSound(Utils::center($this->getPosition()), new BoneMealUseSound());
            $this->getPosition()->getWorld()->addParticle($this->getPosition(), new CropGrowthParticle());
            switch ($data->getAge()) {
                case 0:
                    $block = CustomiesBlockFactory::getInstance()->get(Ids::WHEAT_OBSIDIAN_STAGE_1);
                    $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
                        $this->getPosition()->getX(),
                        $this->getPosition()->getY(),
                        $this->getPosition()->getZ()
                    );

                    $data->setAge(1);
                    $block->customAge = 1;
                    $this->position->getWorld()->setBlock($this->position, $block);

                    break;
                case 1:
                    $block = CustomiesBlockFactory::getInstance()->get(Ids::WHEAT_OBSIDIAN_STAGE_2);
                    $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
                        $this->getPosition()->getX(),
                        $this->getPosition()->getY(),
                        $this->getPosition()->getZ()
                    );
                    $data->setAge(2);
                    $block->customAge = 2;
                    $this->position->getWorld()->setBlock($this->position, $block);
                    break;
                case 2:
                    $block = CustomiesBlockFactory::getInstance()->get(Ids::WHEAT_OBSIDIAN_STAGE_3);
                    $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
                        $this->getPosition()->getX(),
                        $this->getPosition()->getY(),
                        $this->getPosition()->getZ()
                    );
                    $data->setAge(3);
                    $block->customAge = 3;
                    $this->position->getWorld()->setBlock($this->position, $block);
                    break;
            }
            return true;
        }

        return false;
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        $block = $blockReplace->getSide(Facing::DOWN);
        if($block instanceof Farmland){
            Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->setBlockDataAt(
                $this->getPosition()->getX(),
                $this->getPosition()->getY(),
                $this->getPosition()->getZ(),
                new BlockHistoryData($this->ageCustom)
            );
            return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
        }
        return false;
    }

    public function ticksRandomly(): bool{return true;}

    public function getDrops(Item $item): array
    {

        $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
            $this->getPosition()->getX(),
            $this->getPosition()->getY(),
            $this->getPosition()->getZ()
        );






        if (is_null($data)) {
            if ($this->ageCustom === 3) {
                return [
                    CustomiesItemFactory::getInstance()->get(Ids::SEEDS_WHEAT_OBSIDIAN)->setCount(mt_rand(1, 2)),
                    VanillaBlocks::OBSIDIAN()->asItem()
                ];
            }
        } else {
            if ($data->getAge() >= 3) {
                return [
                    CustomiesItemFactory::getInstance()->get(Ids::SEEDS_WHEAT_OBSIDIAN)->setCount(mt_rand(1, 2)),
                    VanillaBlocks::OBSIDIAN()->asItem()
                ];
            }
        }

        if (mt_rand(0, 10) > 7) return [];

        $player = $this->getPosition()->getWorld()->getNearestEntity($this->getPosition(), 7, CustomPlayer::class);
        if ($player instanceof Player && $player->getInventory()->getItemInHand() instanceof FarmTools && $this->getAge() < 3) return [];
        return [
            CustomiesItemFactory::getInstance()->get(Ids::SEEDS_WHEAT_OBSIDIAN)
        ];
    }

    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
    {
        if (is_null($player)) {
            $player = $this->getPosition()->getWorld()->getNearestEntity($this->getPosition(), 7, CustomPlayer::class);
        }
        $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
            $this->getPosition()->getX(),
            $this->getPosition()->getY(),
            $this->getPosition()->getZ()
        );

        if ($data instanceof BlockHistoryData) {
            if ($player instanceof CustomPlayer) {
                if ($data->getAge() >= 3) {
                    $bool = Main::getInstance()->getJobsManager()->addXp($player, JobsManager::FARMER, 7);
                    if ($bool )Main::getInstance()->getJobsManager()->xpNotif($player, $this->getPosition(), 7, JobsManager::FARMER);
                }
            }
        }

        return parent::onBreak($item, $player, $returnedItems);
    }

    public function readStateFromWorld(): Block
    {
        Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
            $this->getPosition()->getX(),
            $this->getPosition()->getY(),
            $this->getPosition()->getZ(),
        );
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1200);
        return parent::readStateFromWorld();
    }

    public function onScheduledUpdate(): void
    {
        $this->onRandomTick();
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 1200);
    }

    public function onRandomTick(): void
    {
        if ($this->getPosition()->getWorld()->getFolderName() === "demo") return;
        $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
            $this->getPosition()->getX(),
            $this->getPosition()->getY(),
            $this->getPosition()->getZ()
        );
        if (is_null($data)) {
            Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->setBlockDataAt(
                $this->getPosition()->getX(),
                $this->getPosition()->getY(),
                $this->getPosition()->getZ(),
                new BlockHistoryData($this->ageCustom)
            );
            $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
                $this->getPosition()->getX(),
                $this->getPosition()->getY(),
                $this->getPosition()->getZ()
            );
        }


        if($data->getAge() < 3 && mt_rand(0, 2) === 1){
            switch ($data->getAge()) {
                case 0:
                    $block = CustomiesBlockFactory::getInstance()->get(Ids::WHEAT_OBSIDIAN_STAGE_1);
                    $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
                        $this->getPosition()->getX(),
                        $this->getPosition()->getY(),
                        $this->getPosition()->getZ()
                    );

                    $data->setAge(1);
                    $block->customAge = 1;
                    $this->position->getWorld()->setBlock($this->position, $block);
                    break;
                case 1:
                    $block = CustomiesBlockFactory::getInstance()->get(Ids::WHEAT_OBSIDIAN_STAGE_2);
                    $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
                        $this->getPosition()->getX(),
                        $this->getPosition()->getY(),
                        $this->getPosition()->getZ()
                    );
                    $data->setAge(2);
                    $block->customAge = 2;
                    $this->position->getWorld()->setBlock($this->position, $block);
                    break;
                case 2:
                    $block = CustomiesBlockFactory::getInstance()->get(Ids::WHEAT_OBSIDIAN_STAGE_3);
                    $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
                        $this->getPosition()->getX(),
                        $this->getPosition()->getY(),
                        $this->getPosition()->getZ()
                    );
                    $data->setAge(3);
                    $block->customAge = 3;
                    $this->position->getWorld()->setBlock($this->position, $block);
                    break;
            }
        }
    }
}