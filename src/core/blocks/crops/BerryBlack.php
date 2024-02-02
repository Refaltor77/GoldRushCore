<?php

namespace core\blocks\crops;

use core\blocks\BlockHistoryData;
use core\cooldown\BasicCooldown;
use core\Main;
use core\managers\jobs\JobsManager;
use core\particles\CropGrowthParticle;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\settings\jobs\Jobs;
use core\sounds\BoneMealUseSound;
use core\utils\Utils;
use customiesdevs\customies\block\CustomiesBlockFactory;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Block;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\GlowBerriesPickSound;

class BerryBlack extends CustomCropsNoDirtFertil
{
    public int $ageCustom;

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, int $age)
    {
        $this->ageCustom = $age;
        parent::__construct($idInfo, $name, $typeInfo);
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->setBlockDataAt(
            $this->getPosition()->getX(),
            $this->getPosition()->getY(),
            $this->getPosition()->getZ(),
            new BlockHistoryData($this->ageCustom)
        );
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }


    public function onInteract2(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{

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
                    $block = CustomiesBlockFactory::getInstance()->get(Ids::BERRY_BLACK_CROPS_STAGE_1);
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
                    $block = CustomiesBlockFactory::getInstance()->get(Ids::BERRY_BLACK_CROPS_STAGE_2);
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
                    $block = CustomiesBlockFactory::getInstance()->get(Ids::BERRY_BLACK_CROPS_STAGE_3);
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


    public function ticksRandomly(): bool{return true;}

    public function getDrops(Item $item): array
    {
        if ($this->ageCustom >= 3) {
            return [
                CustomiesItemFactory::getInstance()->get(Ids::BERRY_BLACK, 2)
            ];
        } else {
            return [
                CustomiesItemFactory::getInstance()->get(Ids::BERRY_BLACK)
            ];
        }
    }

    public function readStateFromWorld(): Block
    {
        Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
            $this->getPosition()->getX(),
            $this->getPosition()->getY(),
            $this->getPosition()->getZ(),
        );
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 20 * 120);
        return parent::readStateFromWorld();
    }

    public function onScheduledUpdate(): void
    {
        $this->onRandomTick();
        $this->getPosition()->getWorld()->scheduleDelayedBlockUpdate($this->getPosition(), 20 * 120);
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
                    $block = CustomiesBlockFactory::getInstance()->get(Ids::BERRY_BLACK_CROPS_STAGE_1);
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
                    $block = CustomiesBlockFactory::getInstance()->get(Ids::BERRY_BLACK_CROPS_STAGE_2);
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
                    $block = CustomiesBlockFactory::getInstance()->get(Ids::BERRY_BLACK_CROPS_STAGE_3);
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


    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
        $world = $this->position->getWorld();

        if ($this->getPosition()->getWorld()->getFolderName() === 'demo') return false;

        if ($player instanceof Player) {
            $itemInHand = $player->getInventory()->getItemInHand();
            if ($itemInHand instanceof Fertilizer) {
                $this->onInteract2($item, $face, $clickVector, $player, $returnedItems);
                return false;
            }
        }

        $data = Main::getInstance()->blockDataService->get($this->getPosition()->getWorld())->getBlockDataAt(
            $this->getPosition()->getX(),
            $this->getPosition()->getY(),
            $this->getPosition()->getZ()
        );


        if ($data instanceof BlockHistoryData) {
            if($data->getAge() >= 3){
                $block = CustomiesBlockFactory::getInstance()->get(Ids::BERRY_BLACK_CROPS_STAGE_2);
                $block->customAge = 2;
                $data->setAge(2);
                $world->setBlock($this->position, $block);
                $world->dropItem($this->position, CustomiesItemFactory::getInstance()->get(Ids::BERRY_BLACK));
                $world->addSound($this->getPosition(), new GlowBerriesPickSound());
                if ($player instanceof CustomPlayer) {
                    $bool = Main::getInstance()->getJobsManager()->addXp($player, JobsManager::FARMER, 3);
                    if ($bool ) Main::getInstance()->getJobsManager()->xpNotif($player, $this->getPosition(), 3, JobsManager::FARMER);
                }
            }
        } elseif ($this->ageCustom >= 3) {

            if ($player instanceof CustomPlayer) {
                $bool = Main::getInstance()->getJobsManager()->addXp($player, JobsManager::FARMER, 3);
                if ($bool )Main::getInstance()->getJobsManager()->xpNotif($player, $this->getPosition(), 3, JobsManager::FARMER);
            }

            $block = CustomiesBlockFactory::getInstance()->get(Ids::BERRY_BLACK_CROPS_STAGE_2);
            $block->customAge = 2;
            $world->setBlock($this->position, $block);
            $world->addSound($this->getPosition(), new GlowBerriesPickSound());
            $world->dropItem($this->position, CustomiesItemFactory::getInstance()->get(Ids::BERRY_BLACK));
        }

        return true;
    }
}