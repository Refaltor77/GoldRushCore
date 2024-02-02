<?php

namespace core\entities;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;

class XpAndDamage extends Entity
{

    private $time = 1;

    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $nbt);
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::CHICKEN;
    }

    public function onUpdate(int $currentTick): bool
    {
        $this->time++;
        if ($this->time == 15) {
            $this->time = 1;
            $this->flagForDespawn();
        }
        return parent::onUpdate($currentTick);
    }


    public function showIndicator(Player $player, string $name)
    {
        $scale = pow(10, 2);
        $projection = new Vector3(mt_rand(-0.1 * $scale, 0.1 * $scale) / $scale, 0.05, mt_rand(-0.1 * $scale, 0.1 * $scale) / $scale);
        $this->spawnTo($player);
        $this->setNameTag($name);
        $this->setMotion($projection);
    }


    public function show(string $name)
    {
        $scale = pow(10, 2);
        $projection = new Vector3(mt_rand(-0.1 * $scale, 0.1 * $scale) / $scale, 0.05, mt_rand(-0.1 * $scale, 0.1 * $scale) / $scale);
        $this->spawnToAll();
        $this->setNameTag($name);
        $this->setMotion($projection);
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0, 0, 0);
    }

    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->setScale(0.001);
        $this->setNameTagAlwaysVisible();
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.0;
    }

    protected function getInitialGravity(): float
    {
        return 0.0;
    }
}