<?php

namespace core\entities;

use core\cooldown\BasicCooldown;
use core\events\LogEvent;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\utils\Utils;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Water;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\Zombie;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\particle\HugeExplodeSeedParticle;
use pocketmine\world\sound\ExplodeSound;

class BossSouls extends Zombie
{

    public static array $playersInBoss = [];
    public static int $heal = 0;
    public static int $maxHeal = 0;
    public static bool $hasStarted = false;


    public int $ticks = 0;

    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        $p = $location;
        (new LogEvent("Apparition du boss des âmes au coordonées ({$p->getX()},{$p->getY()},{$p->getZ()})", LogEvent::EVENT_TYPE))->call();
        parent::__construct($location, $nbt);
    }


    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(4, 1, 2);
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.0;
    }


    protected function getInitialGravity(): float
    {
        return 0.05;
    }


    protected function onDeath(): void
    {
        $this->isDeath = true;

        self::$playersInBoss = [];
        self::$heal = 0;
        self::$maxHeal = 0;

        $pk = AnimateEntityPacket::create("animation.boss_souls.death", "", "", 0, "", 0, [$this->getId()]);
        foreach ($this->getViewers() as $player) {
            if ($player instanceof CustomPlayer) {
                $player->getNetworkSession()->sendDataPacket($pk);
            }
        }


        foreach ($this->hasSendMusic as $xuid => $time) {
            $player = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
            if ($player instanceof CustomPlayer) {
                $pk = new PlaySoundPacket();
                $pk->soundName = "music.boss_souls.vaincu";
                $pk->pitch = 1;
                $pk->x = $this->getPosition()->getX();
                $pk->y = $this->getPosition()->getY();
                $pk->z = $this->getPosition()->getZ();
                $pk->volume = 10;
                $player->sendMessage("§f§lVengeur des âmes: §r§fNon ! Tu m'as vaincu... je reviendrai pour te hanter...");
                $player->getNetworkSession()->sendDataPacket($pk);


                $pk = new PlaySoundPacket();
                $pk->soundName = "music.boss_souls.vaincu_music";
                $pk->pitch = 1;
                $pk->x = $this->getPosition()->getX();
                $pk->y = $this->getPosition()->getY();
                $pk->z = $this->getPosition()->getZ();
                $pk->volume = 10;
                $player->sendMessage(Messages::message("§fBravo ! Tu as vaincu le vengeur des âmes !"));
                $player->getNetworkSession()->sendDataPacket($pk);


                $pk = new StopSoundPacket();
                $pk->soundName = "music.boss_souls.fight";
                $pk->stopAll = true;
                $player->getNetworkSession()->sendDataPacket($pk);
            }
        }

        $this->reject(4);

        Utils::timeout(function (): void {
            if (!$this->isFlaggedForDespawn()) {

                $world = $this->getPosition()->getWorld();
                $eyePos = $this->getEyePos();
                $itemFactory = CustomiesItemFactory::getInstance();

                $entities = [];
                $itemsToDrop = [
                    [Ids::SPECTRAL_HELMET, 1],
                    [Ids::GOLD_POWDER, 3],
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

    protected function initEntity(CompoundTag $nbt): void
    {
        self::$hasStarted = true;
        $this->setMaxHealth(count(Server::getInstance()->getOnlinePlayers()) * 2000);
        $this->setHealth(count(Server::getInstance()->getOnlinePlayers()) * 2000);
        parent::initEntity($nbt);
        $this->setScale(1.5);
    }

    public static function getNetworkTypeId(): string
    {
        return "goldrush:boss_souls";
    }



    /*
    1: Faire bouger l'entity quand y'a un joueur et le soulever - yes
    2: mouvement au hasard avec la tete qui biuge au hasard - no
   */

    // options
    public int $AI = 0;

    // tentative de saut
    public int $try = 0;

    public float $speed = 6.0;
    public array $cache = [];
    public float $addVelocity = 0.0;

    private string $basePos = "";

    private ?Player $player = null;
    private int $attackDelay = 0;




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


    public array $hasSendMusic = [];
    public int $ticksVoc = 0;


    public array $hasSendWalk = [];
    public array $hasSendIdle = [];

    public bool $isDeath = false;

    public function onUpdate(int $currentTick): bool
    {
        self::$heal = (int)$this->getHealth();
        self::$maxHeal = $this->getMaxHealth();



        if ($this->getHealth() <= 1.0) {
            self::$maxHeal = 0;
            self::$heal = 0;
            self::$playersInBoss = [];
            self::$hasStarted = false;

            $this->onDeath();
            $this->setHealth($this->getMaxHealth());
        }

        $entityTarget = $this->getWorld()->getNearestEntity($this->getLocation(), 30, CustomPlayer::class);

        foreach ($this->getViewers() as $player) {
            BasicCooldown::validSylvanar($player);
        }

        if (!self::$hasStarted) return false;
        self::$hasStarted = true;
        if ($entityTarget instanceof CustomPlayer) {
            self::$playersInBoss[$entityTarget->getXuid()] = $entityTarget->getXuid();
            if ($entityTarget->getGamemode()->id() === GameMode::CREATIVE()->id() || $entityTarget->getGamemode()->id() === GameMode::SPECTATOR()->id()) {
                $this->hasSendWalk = [];
                $pk = AnimateEntityPacket::create("animation.boss_souls.idle", "", "", 0, "", 0, [$this->getId()]);
                foreach ($this->getViewers() as $player) {
                    if ($player instanceof CustomPlayer) {
                        if (isset($this->hasSendIdle[$player->getXuid()])) {
                            if ($this->hasSendIdle[$player->getXuid()] <= time()) {
                                Utils::timeout(function () use ($player, $pk) : void {
                                    if ($player->isConnected() && $player->isAlive()) {
                                        $player->getNetworkSession()->sendDataPacket($pk);
                                    }
                                }, 2);
                                $this->hasSendIdle[$player->getXuid()] = time() + 60;
                            }

                        } else {
                            Utils::timeout(function () use ($player, $pk) : void {
                                if ($player->isConnected() && $player->isAlive()) {
                                    $player->getNetworkSession()->sendDataPacket($pk);
                                }
                            }, 2);
                            $this->hasSendIdle[$player->getXuid()] = time() + 60;
                        }
                    }
                }
                return parent::onUpdate($currentTick);
            }
            $this->player = $entityTarget;
            $posTarget = $entityTarget->getLocation();
            $posZombie = $this->getLocation();

            // --- ici les coordonées du joueur ayant le zombie le poursuivant --- \\
            $xT = $posTarget->getX();
            $yT = $posTarget->getY();
            $zT = $posTarget->getZ();
            // --- ici les coordonées du joueur ayant le zombie le poursuivant --- \\


            // --- ici les coordonées du zombie  --- \\
            $xZ = $posZombie->getX();
            $yZ = $posZombie->getY();
            $zZ = $posZombie->getZ();
            // --- ici les coordonées du zombie  --- \\

            $x = $xT - $xZ;
            $y = $yT - $yZ;
            $z = $zT - $zZ;
            $diff = abs($x) + abs($z);

            if ($diff > 0) {
                $pk = AnimateEntityPacket::create("animation.boss_souls.run", "", "", 0, "", 0, [$this->getId()]);
                $this->hasSendIdle = [];
                foreach ($this->getViewers() as $player) {
                    if ($player instanceof Player) {
                        if (isset($this->hasSendWalk[$player->getXuid()])) {
                            if ($this->hasSendWalk[$player->getXuid()] <= time()) {
                                Utils::timeout(function () use ($player, $pk) : void {
                                    if ($player->isConnected() && $player->isAlive()) {
                                        if (!$this->isDeath) {
                                            $player->getNetworkSession()->sendDataPacket($pk);
                                        }
                                    }
                                }, 2);
                                $this->hasSendWalk[$player->getXuid()] = time() + 60;
                            }
                        } else {
                            Utils::timeout(function () use ($player, $pk) : void {
                                if ($player->isConnected() && $player->isAlive()) {
                                    if (!$this->isDeath) {
                                        $player->getNetworkSession()->sendDataPacket($pk);
                                    }
                                }
                            }, 2);
                            $this->hasSendWalk[$player->getXuid()] = time() + 60;
                        }
                    }
                }
                if ($this->attackDelay >= 10) {
                    $xMove = $this->speed * 0.15 * ($x / $diff);
                    $zMove = $this->speed * 0.15 * ($z / $diff);
                    $this->location->yaw = rad2deg(-atan2($x / $diff, $z / $diff));
                    $this->motion->x = $xMove;
                    $this->motion->z = $zMove;
                }
            }
            $this->location->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));

            $cache = $this->cache;
            $this->cache[] = $oldX = $this->getLocation()->getX();
            $this->cache[] = $oldZ = $this->getLocation()->getZ();
            $update = parent::onUpdate($currentTick);

            if (!empty($cache)) {
                if ((int)$cache[0] === (int)$oldX && (int)$cache[1] === (int)$oldZ) {
                    $this->addVelocity += 0.01;
                }
            }

            if ($this->getLocation()->getX() === $oldX) {
                $this->move(0, $this->getJumpVelocity() + $this->addVelocity, 0);
            } elseif ($this->getLocation()->getZ() === $oldZ) {
                $this->move(0, $this->getJumpVelocity() + $this->addVelocity, 0);
            }
        } else {
            $this->hasSendWalk = [];
            $pk = AnimateEntityPacket::create("animation.boss_souls.idle", "", "", 0, "", 0, [$this->getId()]);
            foreach ($this->getViewers() as $player) {
                if ($player instanceof Player) {
                    if (isset($this->hasSendIdle[$player->getXuid()])) {
                        if ($this->hasSendIdle[$player->getXuid()] <= time()) {
                            $player->getNetworkSession()->sendDataPacket($pk);
                            $this->hasSendIdle[$player->getXuid()] = time() + 60;
                        }
                    } else {
                        $player->getNetworkSession()->sendDataPacket($pk);
                        $this->hasSendIdle[$player->getXuid()] = time() + 60;
                    }
                }
            }
            $this->player = null;
            $update = parent::onUpdate($currentTick);
        }


        foreach ($this->getViewers() as $player) {
            if ($this->ticksVoc === 20 * 10) {
                $pk = new PlaySoundPacket();
                $pk->soundName = "music.boss_souls.ton_ame";
                $pk->pitch = 1;
                $pk->x = $this->getPosition()->getX();
                $pk->y = $this->getPosition()->getY();
                $pk->z = $this->getPosition()->getZ();
                $pk->volume = 30;
                $player->sendMessage("§f§lVengeur des âmes: §r§fTon âme m'appartient désormais...");
                $player->getNetworkSession()->sendDataPacket($pk);
            } elseif ($this->ticksVoc === 20 * 30) {
                $pk = new PlaySoundPacket();
                $pk->soundName = "music.boss_souls.tenebre";
                $pk->pitch = 1;
                $pk->x = $this->getPosition()->getX();
                $pk->y = $this->getPosition()->getY();
                $pk->z = $this->getPosition()->getZ();
                $pk->volume = 30;
                $player->sendMessage("§f§lVengeur des âmes: §r§fComment oses-tu rentrer dans mes ténèbres...");
                $player->getNetworkSession()->sendDataPacket($pk);
            } elseif ($this->ticksVoc === 20 * 50) {
                $pk = new PlaySoundPacket();
                $pk->soundName = "music.boss_souls.ancetre";
                $pk->pitch = 1;
                $pk->x = $this->getPosition()->getX();
                $pk->y = $this->getPosition()->getY();
                $pk->z = $this->getPosition()->getZ();
                $pk->volume = 30;
                $player->sendMessage("§f§lVengeur des âmes: §r§fL'or appartient à mes ancêtres...");
                $player->getNetworkSession()->sendDataPacket($pk);
                $this->ticksVoc = 0;
            }


            $pk = new PlaySoundPacket();
            $pk->soundName = "music.boss_souls.fight";
            $pk->pitch = 1;
            $pk->x = 6989;
            $pk->y = 63;
            $pk->z = -954;
            $pk->volume = 10;

            if ($player instanceof CustomPlayer) {
                if (isset($this->hasSendMusic[$player->getXuid()])) {
                    if ($this->hasSendMusic[$player->getXuid()] <= time()) {
                        $player->getNetworkSession()->sendDataPacket($pk);
                        $this->hasSendMusic[$player->getXuid()] = time() + (60 * 3) + 47;
                    }
                } else {
                    $player->getNetworkSession()->sendDataPacket($pk);
                    $this->hasSendMusic[$player->getXuid()] = time() + (60 * 3) + 47;
                }
            }
        }
        if ($this->ticks === (20 * 60 * 3) + 20 * 47) $this->ticks = 0;

        $this->ticks++;
        $this->ticksVoc++;
        return $update;
    }


    public function onCollideWithPlayer(Player $player): void
    {
        if ($this->player === null) return;
        if ($this->player->getXuid() == $player->getXuid() and $this->attackDelay > 10) {

            # animation.boss_souls.attack
            $pk = AnimateEntityPacket::create("animation.boss_souls.attack", "", "", 0, "", 0, [$this->getId()]);
            $this->hasSendWalk = [];
            foreach ($this->getViewers() as $viewer) {
                $viewer->getNetworkSession()->sendDataPacket($pk);
            }


            $this->attackDelay = 0;
            $damage = 30;
            $this->reject(mt_rand(1, 2), $player);
            if (mt_rand(1, 5) === 1) {
                $player->setOnFire(5);
            }
            $player->attack(new EntityDamageByEntityEvent($this, $player, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage));
        }
    }

    public function attack(EntityDamageEvent $source): void
    {
        if ($source instanceof EntityDamageByEntityEvent) {
            $source->setKnockBack(0);
        }
        if ($source->getCause() === EntityDamageEvent::CAUSE_FALL) $source->cancel();
        parent::attack($source);
    }

    public function canBeCollidedWith(): bool
    {
        return parent::canBeCollidedWith();
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


    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->isOnFire() and $this->getWorld()->getBlock($this->getPosition(), true, false) instanceof Water) {
            $this->extinguish();
        }
        $this->attackDelay += $tickDiff;
        return parent::entityBaseTick();
    }
}