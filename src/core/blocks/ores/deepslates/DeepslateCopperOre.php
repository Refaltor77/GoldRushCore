<?php

namespace core\blocks\ores\deepslates;

use core\Main;
use core\managers\jobs\JobsManager;
use core\player\CustomPlayer;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockTypeInfo;
use pocketmine\block\Opaque;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\world\sound\BlockBreakSound;

class DeepslateCopperOre extends Opaque
{

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        parent::__construct($idInfo, $name, $typeInfo);
    }

    public function getDropsForCompatibleTool(Item $item) : array{
        return [
            CustomiesItemFactory::getInstance()->get(Ids::COPPER_RAW)
        ];
    }

    public function isAffectedBySilkTouch() : bool{
        return false;
    }

    protected function getXpDropAmount() : int{
        return mt_rand(0, 2);
    }

    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($player instanceof CustomPlayer) {
            $player->getWorld()->addSound($player->getEyePos(), new BlockBreakSound(VanillaBlocks::DEEPSLATE()));
            $bool = Main::getInstance()->getJobsManager()->addXp($player, JobsManager::MINOR, 20);
            if ($bool )Main::getInstance()->getJobsManager()->xpNotif($player, $this->getPosition(), 20, JobsManager::MINOR);
            Main::getInstance()->getGrafanaManager()->addCopperMined($player->getXuid(), $this->getPosition());
        }
        return parent::onBreak($item, $player, $returnedItems);
    }
}