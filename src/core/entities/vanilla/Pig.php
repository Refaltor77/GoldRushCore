<?php

namespace core\entities\vanilla;

use core\Main;
use core\managers\jobs\JobsManager;
use core\player\CustomPlayer;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Zombie;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaItems;

use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Pig extends Living
{

    protected function initEntity(CompoundTag $nbt): void
    {
        $this->setNameTagAlwaysVisible(false);
        parent::initEntity($nbt);
        $splitted = explode("§6§lx", $this->getNameTag());
        if (isset($splitted[1])) {
            $this->stack = (int)$splitted[1];
        }
    }

    public static function getNetworkTypeId() : string{ return EntityIds::PIG; }

    protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(1.2, 1);
    }

    public function knockBack(float $x, float $z, float $force = self::DEFAULT_KNOCKBACK_FORCE, ?float $verticalLimit = self::DEFAULT_KNOCKBACK_VERTICAL_LIMIT): void
    {
        $force = 0.1;
        $f = sqrt($x * $x + $z * $z);
        if($f <= 0){
            return;
        }
        if(mt_rand() / mt_getrandmax() > $this->knockbackResistanceAttr->getValue()){
            $f = 1 / $f;

            $motionX = $this->motion->x / 2;
            $motionY = $this->motion->y / 2;
            $motionZ = $this->motion->z / 2;
            $motionX += $x * $f * $force;
            $motionY += $force;
            $motionZ += $z * $f * $force;

            $verticalLimit ??= $force;
            if($motionY > $verticalLimit){
                $motionY = $verticalLimit;
            }

            $this->setMotion(new Vector3($motionX, $motionY, $motionZ));
        }
    }

    public function getName() : string{
        return "Pig";
    }

    public function getXpHunter(): int {
        return 15;
    }

    public function attack(EntityDamageEvent $source): void
    {
        if ($this->isAlive()) {
            $this->doHitAnimation();
        }
        if ($source instanceof EntityDamageByEntityEvent) {
            $player = $source->getDamager();
            if ($player instanceof CustomPlayer) {
                $playerReal = $player;
            }
        }
        if ($this->getHealth() - $source->getFinalDamage() <= 2.0) {
            foreach ($this->getDrops() as $item) {
                $this->getPosition()->getWorld()->dropItem($this->getPosition(), $item);
            }
            $this->stack--;
            if (isset($playerReal)) {
                Main::getInstance()->getJobsManager()->addXp($playerReal, JobsManager::HUNTER, $this->getXpHunter());
                Main::getInstance()->getJobsManager()->xpNotif($playerReal, $this->getPosition(), $this->getXpHunter(), JobsManager::HUNTER);
            }
            if ($this->stack <= 0) {
                $this->flagForDespawn();
            } else {
                $this->setHealth($this->getMaxHealth());
                $this->setNameTag("Cochon(s) §6§lx" . $this->stack);
                $source->cancel();
            }
        }

        parent::attack($source);
    }

    public function getDrops() : array{
        $drops = [
            VanillaItems::RAW_PORKCHOP()->setCount(mt_rand(0, 2)),
        ];

        return $drops;
    }

    public function getXpDropAmount() : int{
        return 2;
    }


    public int $stack = 1;

    public function onUpdate(int $currentTick): bool
    {
        $entities = [];

        foreach ($this->getWorld()->getEntities() as $entity) {
            if ($entity instanceof self) {
                if ($entity->getPosition()->distanceSquared($this->getPosition()) <= 20) {
                    $entities[] = $entity;
                }
            }
        }

        foreach ($entities as $entity) {
            if ($entity instanceof Pig && $entity->getId() !== $this->getId()) {
                if ($entity->stack > $this->stack) {
                    $entity->stack += $this->stack;
                    $this->flagForDespawn();
                    $entity->setNameTag("Cochon(s) §6§lx" . $entity->stack);
                } else {
                    $this->stack += $entity->stack;
                    $this->setNameTag("Cochon(s) §6§lx" . $this->stack);
                    $entity->flagForDespawn();
                }
            }
        }


        return parent::onUpdate($currentTick);
    }


    protected function onDeath(): void
    {
        $stack = $this->stack;
        for ($i = $stack; $i !== 0; $i--) {
            foreach ($this->getDrops() as $item) {
                $this->getPosition()->getWorld()->dropItem($this->getPosition(), $item);
            }
        }
        parent::onDeath();
    }
}