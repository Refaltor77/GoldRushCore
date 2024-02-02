<?php

namespace core\entities;

use core\api\timings\TimingsSystem;
use core\entities\ia\EntityAI;
use core\entities\ia\LivingCustom;
use core\events\BossBarReloadEvent;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\utils\Utils;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\sound\ExplodeSound;

class TrollBoss extends LivingCustom implements EntityAI
{

    public int $attackDelay = 0;
    public int $attackDelayZone = 0;
    public bool $isInAttackZone = false;
    public static array $isInBoss = [];
    public bool $hasAttacked = false;

    public static bool $hasStarted = false;

    public ?Player $lastAttacker = null;


    public function attack(EntityDamageEvent $source): void
    {
        if ($source instanceof EntityDamageByEntityEvent) {
            $damahger = $source->getDamager();
            if ($damahger instanceof CustomPlayer) {
                $this->lastAttacker = $damahger;
            }
        }

        parent::attack($source);
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(2, 1, 1);
    }

    public static function getNetworkTypeId(): string
    {
        return 'goldrush:troll';
    }

    public function getName(): string
    {
        return 'Troll';
    }


    public function sendFightMusic(Player $player): void {
        $pk = new PlaySoundPacket();
        $pk->soundName = "music.troll.fight";
        $pk->pitch = 1;
        $pk->x = $this->getPosition()->getX();
        $pk->y = $this->getPosition()->getY();
        $pk->z = $this->getPosition()->getZ();
        $pk->volume = 50;
        $player->getNetworkSession()->sendDataPacket($pk);
    }


    protected function onDeath(): void
    {
        $this->death = true;
        $this->idle = false;
        $this->run = false;
        $this->move = false;

        if ($this->lastAttacker instanceof CustomPlayer) {
            if ($this->lastAttacker->isConnected()) {
                Main::getInstance()->getFactionManager()->addPower("null", 40, $this->lastAttacker);
                $this->lastAttacker->sendMessage(Messages::message("§fVous venez de tuer le troll ! §a+40 powers"));
                $this->lastAttacker->sendSuccessSound();
            }
        }



        foreach ($this->getViewers() as $player) {
            $pk = new StopSoundPacket();
            $pk->soundName = "music.troll.fight";
            $pk->stopAll = true;
            $player->getNetworkSession()->sendDataPacket($pk, true);
        }

        $this->chargeDeath("animation.troll.death");
        BossBarReloadEvent::$troll = false;
        self::$hasStarted = false;

        Utils::timeout(function (): void {
            if (!$this->isFlaggedForDespawn()) {
                $this->flagForDespawn();
            }
        }, 20 * 4);


        Utils::timeout(function (): void {
            if (!$this->isFlaggedForDespawn()) {

                $world = $this->getPosition()->getWorld();
                $eyePos = $this->getEyePos();
                $itemFactory = CustomiesItemFactory::getInstance();

                $entities = [];
                $itemsToDrop = [
                    [Ids::COPPER_INGOT, 10],
                    [Ids::SPECTRAL_BOOTS, 1],
                    [Ids::AMETHYST_INGOT, 10],
                    [Ids::EMERALD_INGOT, 10],
                    [Ids::PLATINUM_INGOT, 10],
                    [Ids::KEY_COMMON, 4],
                    [Ids::KEY_RARE, 5],
                ];

                foreach ($itemsToDrop as $itemData) {
                    $itemId = $itemData[0];
                    $itemCount = $itemData[1];

                    for ($i = 0; $i < $itemCount; $i++) {
                        $itemEntity = $world->dropItem($eyePos, $itemFactory->get($itemId));
                        $entities[] = $itemEntity;
                    }
                }


                $explosionForce = 2.0;


                $this->getWorld()->addSound($this->getEyePos(), new ExplodeSound());
                $this->getWorld()->addParticle($this->getEyePos(), new ExplodeParticle());

                foreach ($entities as $entity) {
                    if ($entity instanceof ItemEntity) {
                        $x = mt_rand(-100, 100) / 100.0;
                        $y = mt_rand(-100, 100) / 100.0;
                        $z = mt_rand(-100, 100) / 100.0;

                        $motionVector = new Vector3($x, $y, $z);
                        $entity->setMotion($motionVector->normalize()->multiply($explosionForce));
                    }
                }


                $this->flagForDespawn();
            }
        }, 20 * 3);
    }


    public function onUpdate(int $currentTick): bool
    {
        if ($this->getHealth() <= 5.0) {
            $this->death = true;
        }


        if ($this->death) {
            $this->setHealth(5000);
            $this->onDeath();
            return false;
        }

        self::$isInBoss = [];
        foreach ($this->getViewers() as $player) {
            if ($player->getPosition()->distanceSquared($this->getPosition()) <= 30) {
                self::$isInBoss[] = $player;
            }
        }

        $this->chargeViewers();

        if (self::$hasStarted) {
            BossBarReloadEvent::$trollLife = $this->getHealth();
            BossBarReloadEvent::$troll = true;
        }


        self::$hasStarted = true;

        if (!$this->attack && !$this->isInAttackZone) {
            if ($this->run) {
                $this->chargeRun("animation.troll.walk");
            }

            if ($this->idle) {
                $this->chargeIdle("animation.troll.idle");
            }


            $this->chargeWalk("animation.troll");
        }

        if (!$this->isInAttackZone) {
            $this->chargeAttack("animation.troll.attack", 1, 20);
        }


        $this->chargeDeath("animation.troll.death");
        $this->chargeCustom();


        $playerTarget = $this->getWorld()->getNearestEntity($this->getEyePos(), 20, CustomPlayer::class);
        if ($playerTarget instanceof CustomPlayer) {
            if ($playerTarget instanceof CustomPlayer && $playerTarget->getGamemode()->equals(GameMode::SURVIVAL()) || $playerTarget->getGamemode()->equals(GameMode::ADVENTURE())) {
                $this->setTarget($playerTarget, 20);
            }
        }



        if ($this->attackDelayZone >= 20 * 20) {
            $this->isInAttackZone = true;
            $this->attackDelayZone = 0;
        }

        if ($this->isInAttackZone) return true;

        $this->attackDelay++;
        $this->attackDelayZone++;
        return parent::onUpdate($currentTick);
    }


    public array $hasSendMusic = [];
    public int $ticksSounds = 0;

    public function sendSoundCustom(Player $player, string $sound): void {
        $pk = new PlaySoundPacket();
        $pk->soundName = "music.troll." . $sound;
        $pk->pitch = 1;
        $pk->x = $this->getPosition()->getX();
        $pk->y = $this->getPosition()->getY();
        $pk->z = $this->getPosition()->getZ();
        $pk->volume = 50;
        $player->getNetworkSession()->sendDataPacket($pk, true);
    }


    public function chargeCustom(): void
    {

        foreach ($this->getViewers() as $player) {
            if ($player->getPosition()->distanceSquared($this->getPosition()) <= 40) {
                if (isset($this->hasSendMusic[$player->getXuid()])) {
                    if ($this->hasSendMusic[$player->getXuid()] <= time()) {
                        $this->hasSendMusic[$player->getXuid()] = time() + 60 + 15;
                        $this->sendFightMusic($player);
                    }
                } else {
                    $this->hasSendMusic[$player->getXuid()] = time() + 60 + 15;
                    $this->sendFightMusic($player);
                }


                if ($this->ticksSounds >= 20 * 10 && !$this->isInAttackZone) {
                    $this->ticksSounds = 0;
                    $sounds = [
                        "troll_1",
                        "troll_2",
                        "troll_3",
                        "troll_4",
                        "troll_5",
                    ];
                    $this->sendSoundCustom($player, $sounds[array_rand($sounds)]);

                }
            }
        }

        $this->ticksSounds++;


        if ($this->isInAttackZone && !$this->hasAttacked) {
            $this->hasAttacked = true;
            $this->run = false;
            $this->idle = false;

            foreach ($this->getViewers() as $playerSend) {
                $pk = AnimateEntityPacket::create("animation.troll.invocation", "", "", 0, "", 0, [$this->getId()]);
                $playerSend->getNetworkSession()->sendDataPacket($pk);
            }

            $task = new TimingsSystem();
            $task->createTiming(function (TimingsSystem $task, int $seconds): void {
                if ($seconds === 1 || $seconds === 2 || $seconds === 3) {
                    foreach ($this->getViewers() as $player) {
                        if ($player->getLocation()->distance($this->getLocation()->asVector3()) <= 10) {
                            $this->reject(6, $player);
                            $damage = 20;
                            $player->attack(new EntityDamageByEntityEvent($this, $player, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage));
                        }
                    }
                }
                if ($seconds === 3) {
                    $this->isInAttackZone = false;
                    $this->hasSendRun = [];
                    $this->hasSendIdle = [];
                    $this->chargeRun("animation.troll.walk");
                    $this->hasAttacked = false;
                    $task->stopTiming();
                }
            });
        }
    }

    public function onCollideWithPlayer(Player $player): void
    {
        if ($this->attackDelay < 22) return;
        $this->attackDelay = 0;
        $playerTarget = $this->getTarget();
        if ($playerTarget instanceof CustomPlayer) {
            $this->attack = true;
            $this->idle = false;
            $this->move = false;
            $this->run = false;

            if ($playerTarget->getXuid() == $player->getXuid()) {
                Utils::timeout(function () use ($player) : void {
                    if ($player->isConnected() && $player->isAlive()) {
                        $damage = 28;
                        $player->attack(new EntityDamageByEntityEvent($this, $player, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage));
                    }
                    }, 18);
            }
        }
        parent::onCollideWithPlayer($player);
    }

    protected function initEntity(CompoundTag $nbt): void
    {
        $this->setSpeed(1);
        $this->setScale(1.5);
        $this->setMaxHealth(5000);
        $this->setHealth($this->getMaxHealth());
        parent::initEntity($nbt);
    }

    private function reject(float $size = 4, ?Player $player = null)
    {
        $explosionSize = 10;
        $minX = (int)floor($this->getPosition()->x - $explosionSize - 1);
        $maxX = (int)ceil($this->getPosition()->x + $explosionSize + 1);
        $minY = (int)floor($this->getPosition()->y - $explosionSize - 1);
        $maxY = (int)ceil($this->getPosition()->y + $explosionSize + 1);
        $minZ = (int)floor($this->getPosition()->z - $explosionSize - 1);
        $maxZ = (int)ceil($this->getPosition()->z + $explosionSize + 1);

        $explosionBB = new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);

        if ($player instanceof Player) {
            $player->setMotion($player->getPosition()->add(0, 3, 0));
            $entityPos = $player->getPosition();
            $distance = $entityPos->distance($this->getPosition()) / $explosionSize;
            $motion = $entityPos->subtractVector($this->getPosition())->normalize();
            $impact = (1 - $distance) * $size;
            $player->setMotion($motion->multiply($impact));
        } else {
            $list = $this->getWorld()->getNearbyEntities($explosionBB, $this);
            foreach ($list as $entity) {
                $entity->setMotion($entity->getPosition()->add(0, 3, 0));
                $entityPos = $entity->getPosition();
                $distance = $entityPos->distance($this->getPosition()) / $explosionSize;
                $motion = $entityPos->subtractVector($this->getPosition())->normalize();
                $impact = (1 - $distance) * $size;
                $entity->setMotion($motion->multiply($impact));
            }
        }
    }

    public function spawnToAll(): void
    {
        self::$hasStarted = true;
        parent::spawnToAll();
    }
}