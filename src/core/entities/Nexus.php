<?php

namespace core\entities;

use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\ExplodeSound;

class Nexus extends Living
{
    private int $ticksCount = 0;
    public static bool $isRunning = false;
    public static ?self $entity = null;
    public static int $nexusLife = 0;

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

    public function knockBack(float $x, float $z, float $force = self::DEFAULT_KNOCKBACK_FORCE, ?float $verticalLimit = self::DEFAULT_KNOCKBACK_VERTICAL_LIMIT): void
    {

    }

    public function onNearbyBlockChange(): void
    {

    }

    protected function tryChangeMovement(): void
    {

    }

    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        $location->pitch = 0.0;
        $location->yaw = round($location->getYaw() / 90) * 90;
        $location->x = $location->getFloorX() + 0.5;
        $location->y = $location->getFloorY();
        $location->z = $location->getFloorZ() + 0.5;


        parent::__construct($location, $nbt);
    }


    public static function isValidDistance(Player $player): bool {

        return false;
    }

    public bool $isDeath = false;

    protected function onDeath(): void
    {
        $this->isDeath = true;
        self::$entity = null;

        if ($this->lastAttacker instanceof CustomPlayer) {
            if ($this->lastAttacker->isConnected()) {
                Main::getInstance()->getFactionManager()->addPower("null", 40, $this->lastAttacker);
                $this->lastAttacker->sendMessage(Messages::message("§fVous venez de détruire  le Nexus ! §a+40 powers"));
                $this->lastAttacker->sendSuccessSound();
            }
        }


        $world = $this->getPosition()->getWorld();
        $eyePos = $this->getEyePos();
        $itemFactory = CustomiesItemFactory::getInstance();

        $entities = [];
        $itemsToDrop = [
            [Ids::COPPER_INGOT, 4],
            [Ids::COPPER_INGOT, 4],
            [Ids::COPPER_INGOT, 4],
            [Ids::COPPER_INGOT, 4],
            [Ids::COPPER_INGOT, 4],
            [Ids::COPPER_INGOT, 4],
            [Ids::COPPER_INGOT, 4],
            [Ids::COPPER_INGOT, 4],
            [Ids::COPPER_INGOT, 4],
            [Ids::COPPER_INGOT, 4],
            [Ids::COPPER_INGOT, 4],
            [Ids::COPPER_INGOT, 4],
            [Ids::COPPER_INGOT, 4],
            [Ids::COPPER_INGOT, 4],
            [Ids::AMETHYST_INGOT, 4],
            [Ids::AMETHYST_INGOT, 4],
            [Ids::AMETHYST_INGOT, 4],
            [Ids::AMETHYST_INGOT, 4],
            [Ids::AMETHYST_INGOT, 4],
            [Ids::AMETHYST_INGOT, 4],
            [Ids::AMETHYST_INGOT, 4],
            [Ids::AMETHYST_INGOT, 4],
            [Ids::AMETHYST_INGOT, 4],
            [Ids::AMETHYST_INGOT, 4],
            [Ids::AMETHYST_INGOT, 4],
            [Ids::AMETHYST_INGOT, 4],
            [Ids::AMETHYST_INGOT, 16],
            [Ids::AMETHYST_INGOT, 9],
            [Ids::AMETHYST_INGOT, 16],
            [Ids::EMERALD_INGOT, 5],
            [Ids::EMERALD_INGOT, 5],
            [Ids::EMERALD_INGOT, 5],
            [Ids::EMERALD_INGOT, 5],
            [Ids::EMERALD_INGOT, 5],
            [Ids::EMERALD_INGOT, 5],
            [Ids::EMERALD_INGOT, 5],
            [Ids::EMERALD_INGOT, 5],
            [Ids::EMERALD_INGOT, 3],
            [Ids::EMERALD_INGOT, 3],
            [Ids::PLATINUM_INGOT, 2],
            [Ids::PLATINUM_INGOT, 2],
            [Ids::PLATINUM_INGOT, 2],
            [Ids::PLATINUM_INGOT, 2],
            [Ids::PLATINUM_INGOT, 2],
            [Ids::PLATINUM_INGOT, 2],
            [Ids::PLATINUM_INGOT, 2],
            [Ids::PLATINUM_INGOT, 5],
            [Ids::PLATINUM_INGOT, 10],
            [Ids::KEY_COMMON, 2],
            [Ids::KEY_COMMON, 2],
            [Ids::KEY_COMMON, 2],
            [Ids::KEY_COMMON, 2],
            [Ids::KEY_COMMON, 2],
            [Ids::KEY_COMMON, 2],
            [Ids::KEY_COMMON, 2],
            [Ids::KEY_RARE, 1],
            [Ids::KEY_RARE, 1],
            [Ids::KEY_RARE, 1],
            [Ids::KEY_RARE, 1],
            [Ids::KEY_RARE, 1],
            [Ids::KEY_RARE, 1],
            [Ids::KEY_RARE, 1],
            [Ids::KEY_RARE, 1],
        ];

        foreach ($itemsToDrop as $itemData) {
            $itemId = $itemData[0];
            $itemCount = $itemData[1];

            for ($i = 0; $i < $itemCount; $i++) {
                $itemEntity = $world->dropItem($eyePos, $itemFactory->get($itemId));
                $entities[] = $itemEntity;
            }
        }




        $this->getWorld()->addSound($this->getEyePos(), new ExplodeSound());
        $this->getWorld()->addParticle($this->getEyePos(), new ExplodeParticle());

        foreach ($entities as $entity) {
            if ($entity instanceof ItemEntity) {
                $x = 5/(((mt_rand(-1000, 1000)+0.9)/100)+0.1);
                $y = 5/(((mt_rand(-1000, 1000)+0.9)/100)+0.1);
                $z = 5/(((mt_rand(-1000, 1000)+0.9)/100)+0.1);
                $motionVector = new Vector3($x, $y, $z);
                $entity->setMotion($motionVector);
            }
        }

        self::$isRunning = false;
        parent::onDeath();
    }

    public function onUpdate(int $currentTick): bool
    {
        if ($this->isDeath) return false;
        if ($this->ticksCount >= 20) {
            $this->ticksCount = 0;
            $this->setNameTagAlwaysVisible(true);
            $this->setNameTag($this->healhToGraphique(floor($this->getHealth())));
        }
        if (self::$entity === null) self::$entity = $this;
        $this->ticksCount++;
        self::$nexusLife = intval($this->getHealth());
        return parent::onUpdate($currentTick);
    }

    private function healhToGraphique(int $health): string
    {
        $health = floor(intval($health) / ($this->getMaxHealth() / 10));
        $carreRed = 10 - $health;

        $etage = "§2";
        for ($v = 0; $v < $health; $v++) {
            $etage .= "⬛";
        }
        $etage .= "§4";
        for ($r = 0; $r < $carreRed; $r++) {
            $etage .= "⬛";
        }

        return $etage . "\n" . $etage;
    }

    protected function initEntity(CompoundTag $nbt): void
    {
        $this->setMaxHealth(5000);
        $this->setHealth($this->getMaxHealth());
        $this->respawnToAll();
        parent::initEntity($nbt);
    }

    public function spawnToAll(): void
    {
        if (self::$isRunning) {
            parent::spawnToAll();
            $this->flagForDespawn();
            return;
        }
        self::$isRunning = true;
        $msg = "§f===== §6[§fEVENT PVP§6] §f======" . "\n";
        $msg .= "§6Event: §fUn nexus vient d'apparaitre \nen (§6x§f)289 (§6z§f)221." . "\n";
        $msg .= "§6Objectif: §fTuer le nexus afin de le faire\nexploser et ramasser du stuff !";
        Server::getInstance()->broadcastMessage($msg);
        parent::spawnToAll();
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(2.5, 1);
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
        return 'goldrush:nexus';
    }

    public static function getSpawnPosition(): Location {
        return new Location(289, 79, 221, Server::getInstance()->getWorldManager()->getDefaultWorld(), 0, 0);
    }

    public function getName(): string
    {
        return 'nexus';
    }
}