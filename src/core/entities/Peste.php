<?php

namespace core\entities;

use core\api\camera\CameraSystem;
use core\api\camera\ShakeTypes;
use core\api\timings\TimingsSystem;
use core\cooldown\BasicCooldown;
use core\entities\ia\EntityAI;
use core\entities\ia\LivingCustom;
use core\events\BossBarReloadEvent;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\utils\Utils;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\network\mcpe\protocol\PlayerFogPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\HugeExplodeSeedParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\ExplodeSound;

class Peste extends LivingCustom implements EntityAI
{

    private ?Player $playerTarget = null;
    public int $deathTimeoutTick = 20 * 5;
    public int $attackDelay = 0;
    public int $tickAttackFlash = 0;
    public bool $isInAttackFlash = false;
    public bool $hasAttacked = false;
    public array $hasAttackedPlayer = [];
    public ?Position $oldPos = null;
    public float $forceKnockBack = 0.05;
    public bool $isFloating = false;
    public static array $isInBoss = [];

    protected function initEntity(CompoundTag $nbt): void
    {
        $this->setSpeed(2);
        $this->setMaxHealth(5000);
        $this->isFloating = ($nbt->getByte('floating', 0) === 0 ? false : true);
        parent::initEntity($nbt);
    }

    public function saveNBT(): CompoundTag
    {
        $nbt =  parent::saveNBT();
        $nbt->setByte('floating', ($this->isFloating === true ? 1 : 0));
        return $nbt;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1.53, 1, 1.56);
    }

    public static function getNetworkTypeId(): string
    {
        return "goldrush:peste";
    }

    protected function onDeath(): void
    {
        $this->death = true;
        $this->idle = false;
        $this->run = false;
        $this->move = false;

        if ($this->isFloating) {
            foreach ($this->getViewers() as $player) {
                $pk = new StopSoundPacket();
                $pk->soundName = "music.peste.jump";
                $pk->stopAll = true;
                $player->getNetworkSession()->sendDataPacket($pk, true);
            }
        } else {
            foreach ($this->getViewers() as $player) {
                $pk = new StopSoundPacket();
                $pk->soundName = "music.peste.fight";
                $pk->stopAll = true;
                $player->getNetworkSession()->sendDataPacket($pk, true);

                $pk = new PlaySoundPacket();
                $pk->soundName = "music.peste.win";
                $pk->pitch = 1;
                $pk->x = $this->getPosition()->getX();
                $pk->y = $this->getPosition()->getY();
                $pk->z = $this->getPosition()->getZ();
                $pk->volume = 50;
                $player->getNetworkSession()->sendDataPacket($pk);

                $fog = PlayerFogPacket::create([
                    'minecraft:fog_default',
                ]);
                $player->getNetworkSession()->sendDataPacket($fog);
            }
            $this->chargeDeath("animation.peste.death");
            BossBarReloadEvent::$sylvanar = false;

            $pos = $this->getPosition();
            Utils::timeout(function () use ($pos): void {

                $world = $this->getPosition()->getWorld();
                $eyePos = $this->getEyePos();
                $itemFactory = CustomiesItemFactory::getInstance();

                $entities = [];
                $itemsToDrop = [
                    [Ids::COPPER_INGOT, 20],
                    [Ids::SPECTRAL_CHESTPLATE, 1],
                    [Ids::SPECTRAL_LEGGINGS, 1],
                    [Ids::AMETHYST_INGOT, 20],
                    [Ids::EMERALD_INGOT, 20],
                    [Ids::PLATINUM_INGOT, 20],
                    [Ids::KEY_COMMON, 12],
                    [Ids::KEY_RARE, 22],
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

                }, 20 * 5);
        }
        parent::onDeath();
    }


    public function sendTeleportSound(Player $player): void {
        $pk = new PlaySoundPacket();
        $pk->soundName = "music.peste.teleport";
        $pk->pitch = 1;
        $pk->x = $this->getPosition()->getX();
        $pk->y = $this->getPosition()->getY();
        $pk->z = $this->getPosition()->getZ();
        $pk->volume = 50;
        $player->getNetworkSession()->sendDataPacket($pk);
    }


    public function sendFightMusic(Player $player): void {
        $pk = new PlaySoundPacket();
        $pk->soundName = "music.peste.fight";
        $pk->pitch = 1;
        $pk->x = $this->getPosition()->getX();
        $pk->y = $this->getPosition()->getY();
        $pk->z = $this->getPosition()->getZ();
        $pk->volume = 50;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function sendJumpMusic(Player $player): void {
       Utils::timeout(function () use ($player) : void {
           if (!$player->isConnected() || !$player->isAlive()) return;
           $pk = new PlaySoundPacket();
           $pk->soundName = "music.peste.jump";
           $pk->pitch = 1;
           $pk->x = $this->getPosition()->getX();
           $pk->y = $this->getPosition()->getY();
           $pk->z = $this->getPosition()->getZ();
           $pk->volume = 50;
           $player->getNetworkSession()->sendDataPacket($pk);
       }, 10);
    }


    public function sendSoundCustom(Player $player, string $sound): void {
        $pk = new PlaySoundPacket();
        $pk->soundName = "music.peste." . $sound;
        $pk->pitch = 1;
        $pk->x = $this->getPosition()->getX();
        $pk->y = $this->getPosition()->getY();
        $pk->z = $this->getPosition()->getZ();
        $pk->volume = 50;
        $player->getNetworkSession()->sendDataPacket($pk, true);
    }


    public function sendCameraAnim(Player $player): void {
        $pk = AnimateEntityPacket::create("animation.peste.sitting", "", "", 0, "", 0, [$this->getId()]);
        $player->getNetworkSession()->sendDataPacket($pk);
    }


    public array $hasSendShake = [];
    public array $hasSendMusic = [];
    public int $ticksSounds = 0;

    public function chargeCustom(): void
    {
        self::$isInBoss = $this->getViewers();
        foreach ($this->getViewers() as $player) {
            if ($player->getPosition()->distance($this->getPosition()) >= 80) return;
            if ($this->isFloating) {
                if ($this->ticksSounds >= 20 * 20 && !$this->isInAttackFlash) {
                    $this->ticksSounds = 0;
                    $sounds = [
                        "rire1",
                    ];
                    $this->sendSoundCustom($player, $sounds[array_rand($sounds)]);
                }
                $fog = PlayerFogPacket::create([
                    'goldrush:fog_sylvanar_floating',
                ]);
                $player->getNetworkSession()->sendDataPacket($fog);

                if (isset($this->hasSendMusic[$player->getXuid()])) {
                    if ($this->hasSendMusic[$player->getXuid()] <= time()) {
                        $this->hasSendMusic[$player->getXuid()] = time() + 60 + 8;
                        $this->sendJumpMusic($player);
                    }
                } else {
                    $this->hasSendMusic[$player->getXuid()] = time() + 60 + 8;
                    $this->sendJumpMusic($player);
                }
            } else {
                $fog = PlayerFogPacket::create([
                    'goldrush:fog_sylvanar',
                ]);
                $player->getNetworkSession()->sendDataPacket($fog);
                if (isset($this->hasSendMusic[$player->getXuid()])) {
                    if ($this->hasSendMusic[$player->getXuid()] <= time()) {
                        $this->hasSendMusic[$player->getXuid()] = time() + 60 + 15;
                        $this->sendFightMusic($player);
                    }
                } else {
                    $this->hasSendMusic[$player->getXuid()] = time() + 60 + 15;
                    $this->sendFightMusic($player);
                }


                if ($this->ticksSounds >= 20 * 20 && !$this->isInAttackFlash) {
                    $this->ticksSounds = 0;
                    $sounds = [
                        "rire1",
                        "soin",
                        "soin2",
                    ];
                    $this->sendSoundCustom($player, $sounds[array_rand($sounds)]);

                }
            }
        }


        $this->ticksSounds++;



        if ($this->isInAttackFlash && !$this->hasAttacked) {
            $this->hasAttacked = true;
            $task = new TimingsSystem();
            foreach ($this->getViewers() as $player) {
                $this->sendSoundCustom($player, "name");
                $pk = AnimateEntityPacket::create("animation.peste.flash", "", "", 0, "", 0, [$this->getId()]);
                $player->getNetworkSession()->sendDataPacket($pk);
                $pk = new PlaySoundPacket();
                $pk->soundName = "music.peste.flash";
                $pk->pitch = 1;
                $pk->x = $this->getPosition()->getX();
                $pk->y = $this->getPosition()->getY();
                $pk->z = $this->getPosition()->getZ();
                $pk->volume = 50;
                $player->getNetworkSession()->sendDataPacket($pk);
            }
            $task->createTiming(function (TimingsSystem $task, int $seconds): void {
                if ($seconds === 18) {
                    foreach ($this->getViewers()  as $player) {
                        $this->sendLightning($this->getPosition(), $player);
                    }
                }elseif ($seconds === 19) {
                    $this->despawnFromAll();
                    $this->oldPos = $this->getPosition();
                } elseif ($seconds === 21) {
                    $this->spawnToAll();
                    $this->setScale(1);
                    $viewers = $this->getViewers();
                    foreach ($viewers as $player) {
                        $this->hasAttackedPlayer[] = $player->getXuid();

                        $playerPosition = $player->getPosition();
                        $this->teleport(new Position(
                            $player->getPosition()->getFloorX() + 1,
                            $player->getPosition()->getFloorY(),
                            $player->getPosition()->getFloorZ(),
                            $player->getPosition()->getWorld()
                        ));


                        $this->sendSoundCustom($player, "la");
                        Utils::timeout(function () use ($player) : void {
                            $this->lookAt($player->getEyePos()->asVector3());
                            if ($player->isConnected() && $player->isAlive()) {
                                $this->sendTeleportSound($player);
                                $this->sendCameraAnim($player);

                                Utils::timeout(function () use ($player) : void {
                                    if ($player->isConnected() && $player->isAlive()) {
                                        $this->lookAt($player->getEyePos());
                                        $this->reject(6, $player);
                                        $this->getWorld()->addSound($this->getPosition(), new ExplodeSound());

                                        $this->sendLightning($player->getPosition(), $player);
                                        $damage = 16;
                                        $player->attack(new EntityDamageByEntityEvent($this, $player, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage));

                                    }
                                }, 10);
                            }
                        }, 10);
                        break;
                    }
                } elseif ($seconds === 22) {
                    foreach ($this->getViewers() as $player) {
                        if (!in_array($player->getXuid(), $this->hasAttackedPlayer)) {
                            $this->hasAttackedPlayer[] = $player->getXuid();
                            $playerPosition = $player->getPosition();
                            $playerRotation = $player->getLocation()->getYaw();
                            $this->teleport(new Position(
                                $player->getPosition()->getFloorX() + 1,
                                $player->getPosition()->getFloorY(),
                                $player->getPosition()->getFloorZ(),
                                $player->getPosition()->getWorld()
                            ));

                            $this->sendSoundCustom($player, "la");
                            Utils::timeout(function () use ($player) : void {
                                $this->lookAt($player->getEyePos()->asVector3());
                                if ($player->isConnected() && $player->isAlive()) {
                                    $this->sendTeleportSound($player);
                                    $this->sendCameraAnim($player);

                                    Utils::timeout(function () use ($player) : void {
                                        if ($player->isConnected() && $player->isAlive()) {
                                            $this->lookAt($player->getEyePos());
                                            $this->reject(6, $player);
                                            $this->getWorld()->addSound($this->getPosition(), new ExplodeSound());

                                            $this->sendLightning($player->getPosition(), $player);
                                            $damage = 16;
                                            $player->attack(new EntityDamageByEntityEvent($this, $player, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage));

                                        }
                                    }, 10);
                                }
                            }, 10);
                            break;
                        }
                    }
                } elseif ($seconds === 23) {
                    foreach ($this->getViewers() as $player) {
                        if (!in_array($player->getXuid(), $this->hasAttackedPlayer)) {
                            $this->hasAttackedPlayer[] = $player->getXuid();
                            $this->sendCameraAnim($player);
                            $this->teleport(new Position(
                                $player->getPosition()->getFloorX() + 1,
                                $player->getPosition()->getFloorY(),
                                $player->getPosition()->getFloorZ(),
                                $player->getPosition()->getWorld()
                            ));

                            $this->sendSoundCustom($player, "la");
                            Utils::timeout(function () use ($player) : void {
                                if ($player->isConnected() && $player->isAlive()) {
                                    $this->sendTeleportSound($player);
                                    $this->sendCameraAnim($player);

                                    Utils::timeout(function () use ($player) : void {
                                        if ($player->isConnected() && $player->isAlive()) {
                                            $this->lookAt($player->getEyePos());
                                            $this->reject(10, $player);
                                            $this->getWorld()->addSound($this->getPosition(), new ExplodeSound());

                                            $this->sendLightning($player->getPosition(), $player);

                                            $damage = 16;
                                            $player->attack(new EntityDamageByEntityEvent($this, $player, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage));
                                        }
                                    }, 10);
                                }
                            }, 10);
                            break;
                        }
                    }
                } elseif ($seconds === 24) {
                    $this->isInAttackFlash = false;
                    $this->hasAttacked = false;
                    $this->hasAttackedPlayer = [];
                    $this->teleport($this->oldPos);
                }
            });
        }
    }

    public int $attackFloating = 0;

    public function onUpdate(int $currentTick): bool
    {
        if ($this->death) return false;
        BossBarReloadEvent::$sylvanarLife = $this->getHealth();
        BossBarReloadEvent::$sylvanar = true;


        $this->initEntityCustom();
        $this->chargeViewers();


        if (!$this->isFloating) {
            $this->chargeIdle("animation.peste.static");
        } else {
            $this->chargeIdle("animation.peste.loopjump");
        }


        $this->chargeRun("animation.peste.run");
        $this->chargeWalk("animation.peste.move");
        $this->chargeAttack("animation.peste.attack", 5);
        $this->chargeDeath("animation.peste.death");

        $this->chargeCustom();




        if (!$this->isFloating) {
            foreach ($this->getViewers() as $player) {
                BasicCooldown::validSylvanar($player);
            }
            $playerTarget = $this->getWorld()->getNearestEntity($this->getEyePos(), 20, CustomPlayer::class);
            if ($playerTarget instanceof CustomPlayer) {
                if ($playerTarget instanceof CustomPlayer && $playerTarget->getGamemode()->equals(GameMode::SURVIVAL()) || $playerTarget->getGamemode()->equals(GameMode::ADVENTURE())) {
                    $this->setTarget($playerTarget, 20);
                }
            }


            if ($this->tickAttackFlash >= 20 * 60) {
                foreach ($this->getViewers() as $player) {
                    $camera = new CameraSystem($player);
                    $camera->createTiming(function (CameraSystem $camera, int $seconds, Player $player): void {
                        if ($this->isInAttackFlash === false || $this->isFlaggedForDespawn()) {
                            $camera->stopShakeCamera();
                            $camera->stopTiming();
                        }
                        switch ($seconds) {
                            case 1:
                                $camera->addShakeCamera(0.2, 60, ShakeTypes::TYPE_POSITIONAL);
                                break;
                        }
                    });
                }
                $this->tickAttackFlash = 0;
                $this->isInAttackFlash = true;
            } elseif ($this->tickAttackFlash === 20 * 30) {

            }


            $this->attackDelay++;
            $this->tickAttackFlash++;

            if ($this->isInAttackFlash) {
                return false;
            }
        } else {


            if ($this->attackFloating >= 20 * 30) {
                foreach ($this->getViewers() as $player) {
                    $this->sendSoundCustom($player, "la");
                    Utils::timeout(function () use ($player): void {
                        if ($player->isConnected()) {
                            $this->sendLightning($player->getPosition(), $player);
                        }
                    }, 48);
                }
                $this->attackFloating = 0;
            }

            $this->attackFloating++;


            $this->idle = true;

            foreach ($this->getViewers() as $player) {

                if (isset($this->hasSendShake[$player->getXuid()])) {
                    if ($this->hasSendShake[$player->getXuid()] <= time()) {
                        $this->hasSendShake[$player->getXuid()] = time() + 60;
                        $camera = new CameraSystem($player);
                        $camera->createTiming(function (CameraSystem $camera, int $seconds, Player $player): void {
                            if ($this->isFloating === false || $this->isFlaggedForDespawn()) {
                                $camera->stopShakeCamera();
                                $camera->stopTiming();
                            }
                            switch ($seconds) {
                                case 1:
                                    $camera->addShakeCamera(0.01, 60, ShakeTypes::TYPE_POSITIONAL);
                                    break;
                            }
                        });
                    }
                } else {
                    $this->hasSendShake[$player->getXuid()] = time() + 60;
                    $camera = new CameraSystem($player);
                    $camera->createTiming(function (CameraSystem $camera, int $seconds, Player $player): void {
                        if ($this->isFloating === false || $this->isFlaggedForDespawn()) {
                            $camera->stopShakeCamera();
                            $camera->stopTiming();
                        }
                        switch ($seconds) {
                            case 1:
                                $camera->addShakeCamera(0.2, 60, ShakeTypes::TYPE_POSITIONAL);
                                break;
                        }
                    });
                }
            }
        }
        return parent::onUpdate($currentTick);
    }


    public function attack(EntityDamageEvent $source): void
    {
        if ($this->isFloating) {

            if ($source instanceof EntityDamageByEntityEvent) {
                $damager = $source->getDamager();
                if ($damager instanceof Player) {
                    if (Server::getInstance()->isOp($damager->getName())) {
                        $this->onDeath();
                        BossBarReloadEvent::$sylvanarLife = 0.0;
                        BossBarReloadEvent::$sylvanar = false;
                        self::$isInBoss = [];


                        foreach ($this->getViewers() as $player) {
                            $pk = new StopSoundPacket();
                            $pk->soundName = "music.peste.jump";
                            $pk->stopAll = true;
                            $player->getNetworkSession()->sendDataPacket($pk, true);

                            $fog = PlayerFogPacket::create([
                                'minecraft:fog_default',
                            ]);
                            $player->getNetworkSession()->sendDataPacket($fog);
                        }
                    }
                }
            }
            $source->cancel();
            return;
        }
        if ($this->isInAttackFlash) {
            $source->cancel();
            return;
        }

        parent::attack($source);
    }

    public function initEntityCustom(): void
    {
        if (!$this->hasInit) {
            $this->setHealth(5000);
        }
        parent::initEntityCustom();
    }


    public function onCollideWithPlayer(Player $player): void
    {
        if ($this->attackDelay < 12 || $this->isInAttackFlash) return;
        $this->attackDelay = 0;
        $playerTarget = $this->getTarget();
        if ($playerTarget instanceof CustomPlayer) {
            $this->attack = true;
            $this->idle = false;
            $this->move = false;
            $this->run = false;

            if ($playerTarget->getXuid() == $player->getXuid()) {
                $damage = 28;
                $this->rejectp(1, $player);
                $player->attack(new EntityDamageByEntityEvent($this, $player, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage));
            }
        }
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


        $this->getWorld()->addParticle($this->getPosition(), new HugeExplodeSeedParticle());
        $this->getWorld()->addSound($this->getPosition(), new ExplodeSound());
    }

    private function rejectp(float $size = 4, ?Player $player = null)
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

    public function getName(): string
    {
        return "Sylvanar";
    }
}