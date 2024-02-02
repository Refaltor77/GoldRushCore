<?php

namespace core\entities;

use core\events\LogEvent;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

class KothText extends Entity
{
    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        $p = $location;
        (new LogEvent("Apparition du KOTH au coordonées ({$p->getX()},{$p->getY()},{$p->getZ()})", LogEvent::EVENT_TYPE))->call();
        parent::__construct($location, $nbt);
    }

    protected function initEntity(CompoundTag $nbt): void
    {
        $this->setScale(2);
        parent::initEntity($nbt);
    }


    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(2, 1);
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
        return "goldrush:koth_text";
    }




    public array $sendAnim = [];

    public function onUpdate(int $currentTick): bool
    {
        $pk = AnimateEntityPacket::create("animation.koth.text", "", "", 0, "", 0, [$this->getId()]);

        foreach ($this->getViewers() as $player) {
            if ($player instanceof  CustomPlayer) {
                if ($player->hasReallyConnected) {
                    if (!isset($this->sendAnim[$player->getXuid()])) {
                        $this->sendAnim[$player->getXuid()] = time() + 6 * 4;
                        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($pk, $player) : void {
                            if ($player->isConnected()) {
                                $result = $player->getNetworkSession()->sendDataPacket($pk);
                            }
                        }), 20);
                    } elseif ($this->sendAnim[$player->getXuid()] <= time()) {
                        $this->sendAnim[$player->getXuid()] = time() + 6 * 4;
                        $player->getNetworkSession()->sendDataPacket($pk);
                    }
                }
            }
        }
        return parent::onUpdate($currentTick);
    }
}