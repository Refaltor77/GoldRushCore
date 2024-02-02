<?php

namespace core\entities;

use core\api\camera\CameraSystem;
use core\events\LogEvent;
use core\player\CustomPlayer;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;

class CameraEntity extends Living
{
    private bool $isOccuped = false;
    private ?CameraSystem $camera = null;


    public function getMaxHealth(): int
    {
        return 1;
    }

    public function getHealth(): float
    {
         return 0.1;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1.8, 0.6, 1.62);
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.0;
    }

    protected function getInitialGravity(): float
    {
        return 0.0;
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::TRIPOD_CAMERA;
    }

    protected function onDeath(): void
    {
        $this->getWorld()->dropItem(
            $this->getEyePos(),
            CustomiesItemFactory::getInstance()->get(Ids::CAMERA)
        );
    }

    public function attack(EntityDamageEvent $source): void
    {
        if (!is_null($this->camera)) {
            $this->camera->stopTiming();
            $this->camera->end();
        }
    }

    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        if($this->isOccuped) return false;
        $camera = new CameraSystem($player);
        $this->camera = $camera;
        $this->isOccuped = true;
        $camera->createTiming(function(CameraSystem $system, int $seconds, Player $player) : void {
            if(!$player->isSneaking()) {
                $system->setCameraPostionWithFacing($this->getEyePos(), durationEase: 0.5, posCameraFixing: $player->getEyePos());
            }
            if($player->isSneaking()){
                $system->stopTiming();
                $this->isOccuped = false;
                $this->camera = null;
            }
        },10);
        $camera->end();
        return parent::onInteract($player, $clickPos);
    }

    public function getName(): string
    {
        return "Camera";
    }
}