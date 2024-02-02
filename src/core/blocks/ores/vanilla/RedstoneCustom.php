<?php

namespace core\blocks\ores\vanilla;

use core\Main;
use core\managers\jobs\JobsManager;
use core\player\CustomPlayer;
use core\settings\jobs\Jobs;
use pocketmine\block\Coal;
use pocketmine\block\Opaque;
use pocketmine\block\Redstone;
use pocketmine\block\RedstoneOre;
use pocketmine\block\utils\FortuneDropHelper;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class RedstoneCustom extends RedstoneOre
{

    protected bool $lit = false;

    protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
        $w->bool($this->lit);
    }

    public function isLit() : bool{
        return $this->lit;
    }

    /**
     * @return $this
     */
    public function setLit(bool $lit = true) : self{
        $this->lit = $lit;
        return $this;
    }

    public function getLightLevel() : int{
        return $this->lit ? 9 : 0;
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
        if(!$this->lit){
            $this->lit = true;
            $this->position->getWorld()->setBlock($this->position, $this); //no return here - this shouldn't prevent block placement
        }
        return false;
    }

    public function onNearbyBlockChange() : void{
        if(!$this->lit){
            $this->lit = true;
            $this->position->getWorld()->setBlock($this->position, $this);
        }
    }

    public function ticksRandomly() : bool{
        return true;
    }

    public function onRandomTick() : void{
        if($this->lit){
            $this->lit = false;
            $this->position->getWorld()->setBlock($this->position, $this);
        }
    }

    public function getDropsForCompatibleTool(Item $item) : array{
        return [
            VanillaItems::REDSTONE_DUST()->setCount(FortuneDropHelper::discrete($item, 4, 5))
        ];
    }

    public function isAffectedBySilkTouch() : bool{
        return false;
    }

    protected function getXpDropAmount() : int{
        return mt_rand(1, 5);
    }


    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($player instanceof CustomPlayer) {
            Main::getInstance()->getTopLuckManager()->addOre($player);
            $bool = Main::getInstance()->getJobsManager()->addXp($player, JobsManager::MINOR, Jobs::MINER_XP[$this->getTypeId()]);
            if ($bool )Main::getInstance()->getJobsManager()->xpNotif($player, $this->getPosition(), Jobs::MINER_XP[$this->getTypeId()], JobsManager::MINOR);
        }
        return parent::onBreak($item, $player, $returnedItems);
    }
}