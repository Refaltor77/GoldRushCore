<?php

namespace core\entities;

use core\commands\executors\staff\Vanish;
use core\events\LogEvent;
use core\interfaces\EmoteIds;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\EmotePacket;
use pocketmine\player\Player;
use pocketmine\Server;

class Slapper extends Human
{
    public string $name;
    public string $cmd;
    private int $timingss = 0;
    private bool $dance = false;

    public function __construct(Location $location, Skin $skin, string $name = "", string $cmd = "", bool $dance = false, ?CompoundTag $nbt = null)
    {
        $p = $location;
        (new LogEvent("Apparition d'un slapper au coordonées ({$p->getX()},{$p->getY()},{$p->getZ()})", LogEvent::EVENT_TYPE))->call();

        $this->name = $name;
        $this->cmd = $cmd;
        $this->dance = $dance;
        if (!is_null($nbt)) {
            $name = $nbt->getString('floating', 'null');
            $cmd = $nbt->getString('cmd', 'null');
            $dance = $nbt->getString('danse', 'null');
            if ($cmd !== 'null') $this->cmd = $cmd;
            if ($name !== 'null') $this->name = $name;
            if ($dance !== 'null') $this->dance = $dance;
        }
        parent::__construct($location, $skin, $nbt);
    }

    public function initEntity(CompoundTag $nbt): void
    {
        $this->setNameTagAlwaysVisible(true);
        $this->setNameTag($this->name);
        parent::initEntity($nbt);
    }

    public function attack(EntityDamageEvent $source): void
    {
        $damager = null;
        if ($source->getCause() === EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
            $damager = $source->getDamager();
        }

        if (!$damager instanceof Player) return;
        $cmd = $this->cmd;
        if ($cmd !== "") {
            $damager->getServer()->dispatchCommand($damager, $cmd);
        }

        if($this->dance) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->getNetworkSession()->sendDataPacket(EmotePacket::create($this->getId(), EmoteIds::getRandomEmote(), $player->getXuid(), "", EmotePacket::FLAG_MUTE_ANNOUNCEMENT));
            }
        }
    }

    public function onNearbyBlockChange(): void
    {

    }

    public function onUpdate(int $currentTick): bool
    {

        if ($this->getXpManager()->canAttractXpOrbs()) $this->getXpManager()->setCanAttractXpOrbs(false);
        $this->setNameTagAlwaysVisible(true);
        $this->setNameTag($this->name);

        $entityTarget = $this->getWorld()->getNearestEntity($this->getLocation(), 50, Player::class);
        if ($entityTarget instanceof Player) {
            if (!in_array($entityTarget->getXuid(), Vanish::$inVanish)) {
                $posTarget = $entityTarget->getLocation();
                $posZombie = $this->getLocation();
                $xT = $posTarget->getX();
                $yT = $posTarget->getY();
                $zT = $posTarget->getZ();
                $xZ = $posZombie->getX();
                $yZ = $posZombie->getY();
                $zZ = $posZombie->getZ();
                $x = $xT - $xZ;
                $y = $yT - $yZ;
                $z = $zT - $zZ;
                $diff = abs($x) + abs($z);
                if ($diff > 0) {
                    $this->location->yaw = rad2deg(-atan2($x / $diff, $z / $diff));
                }
                $this->location->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));
            }
        }

        if($this->dance) {
            if ($this->timingss === 20 * 10) {
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    $player->getNetworkSession()->sendDataPacket(EmotePacket::create($this->getId(), EmoteIds::getRandomEmote(), $player->getXuid(), "", EmotePacket::FLAG_MUTE_ANNOUNCEMENT));
                }
                $this->timingss = 0;
            }

            $this->timingss++;
        }
        return parent::onUpdate($currentTick);
    }

    protected function move(float $dx, float $dy, float $dz): void
    {

    }


    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        $cmd = $this->cmd;
        if ($cmd !== "") {
            $player->getServer()->dispatchCommand($player, $cmd);
        }
        if ($this->dance) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->getNetworkSession()->sendDataPacket(EmotePacket::create($this->getId(), EmoteIds::getRandomEmote(), $player->getXuid(), "", EmotePacket::FLAG_MUTE_ANNOUNCEMENT));
            }
        }
        return parent::onInteract($player, $clickPos);
    }

    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();
        $nbt->setString('cmd', $this->cmd);
        $nbt->setString('floating', $this->name);
        return $nbt;
    }

    public function tryChangeMovement(): void
    {

    }
}