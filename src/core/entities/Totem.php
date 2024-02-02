<?php

namespace core\entities;

use core\events\LogEvent;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Limits;
use pocketmine\world\particle\HugeExplodeSeedParticle;
use pocketmine\world\sound\ExplodeSound;

class Totem extends Entity
{
    public static bool $isRunning = false;
    private $state = 0;
    private int|float $DefineHealt;

    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        $p = $location;
        (new LogEvent("Apparition d'un totem au coordonées ({$p->getX()},{$p->getY()},{$p->getZ()})", LogEvent::EVENT_TYPE))->call();

        $pos = $location->add(0.5, 0, 0.5);
        parent::__construct(new Location($pos->getX(), $pos->getY(), $pos->getZ(), $location->getWorld(), $location->getPitch(), $location->getPitch()), $nbt);
    }


    public static function getSpawnPosition(): Location
    {

        return new Location(-235, 77, 4, Server::getInstance()->getWorldManager()->getDefaultWorld(), 0, 0);
    }

    public function isImmobile(): bool
    {
        return true;
    }

    public function isForceMovementUpdate(): bool
    {
        return false;
    }

    public function spawnToAll(): void
    {
        $this->setBlockCollision(VanillaBlocks::AIR());
        parent::spawnToAll();
        $this->setBlockCollision(VanillaBlocks::INVISIBLE_BEDROCK());

        $pk = new AddActorPacket();
        $pk->type = "minecraft:lightning_bolt";
        $pk->actorUniqueId = 1001001;
        $pk->actorRuntimeId = 1001001;
        $pk->syncedProperties = new PropertySyncData([], []);
        $pk->metadata = [];
        $pk->motion = null;
        $pk->yaw = $this->getLocation()->getYaw();
        $pk->pitch = $this->getLocation()->getPitch();
        $pk->position = new Vector3($this->getPosition()->getX(), $this->getPosition()->getY(), $this->getPosition()->getZ());

        $sound = new PlaySoundPacket();
        $sound->soundName = "ambient.weather.thunder";
        $sound->x = $this->getPosition()->getX();
        $sound->y = $this->getPosition()->getY();
        $sound->z = $this->getPosition()->getZ();
        $sound->volume = 100;
        $sound->pitch = 1000;

        $x = $this->getPosition()->getFloorX();
        $z = $this->getPosition()->getFloorZ();

        $this->reject();
        $msg = "§6§l---\n";
        $msg .= "§r§fUn totem provoque la guerre en warzone §6!\n";
        $msg .= "§r§7Description §8: §fSoyez un humble guerrier et\n";
        $msg .= "partez affronter le totem !\n";
        $msg .= "§fCoordonnées §8: §e[x]$x §f: §e[z]$z\n";
        $msg .= "§6§l---\n";

        self::$isRunning = true;

        Server::getInstance()->broadcastMessage($msg);

        foreach ($this->getViewers() as $player) {
            $player->getNetworkSession()->sendDataPacket($pk);
            $player->getNetworkSession()->sendDataPacket($sound);
        }
    }

    private function setBlockCollision(Block $barrier)
    {
        for ($h = 0; $h < 10; $h++) {
            $this->getWorld()->setBlock($this->getPosition()->add(0, $h, 0), $barrier);
            $this->getWorld()->setBlock($this->getPosition()->add(1, $h, 0), $barrier);
            $this->getWorld()->setBlock($this->getPosition()->add(0, $h, 1), $barrier);
            $this->getWorld()->setBlock($this->getPosition()->add(0, $h, 0)->subtract(1, 0, 0), $barrier);
            $this->getWorld()->setBlock($this->getPosition()->add(0, $h, 0)->subtract(0, 0, 1), $barrier);


            $this->getWorld()->setBlock($this->getPosition()->add(0, $h, 0)->subtract(1, 0, 1), $barrier);
            $this->getWorld()->setBlock($this->getPosition()->add(1, $h, 0)->subtract(0, 0, 1), $barrier);

            $this->getWorld()->setBlock($this->getPosition()->subtract(0, 0, 0)->add(1, $h, 1), $barrier);
            $this->getWorld()->setBlock($this->getPosition()->subtract(1, 0, 0)->add(0, $h, 1), $barrier);
            $this->getWorld()->setBlock($this->getPosition()->add(1, $h, 1)->subtract(1, 0, 1), $barrier);
        }
    }

    private function reject(float $size = 4)
    {
        $explosionSize = 10;
        $minX = (int)floor($this->getPosition()->x - $explosionSize - 1);
        $maxX = (int)ceil($this->getPosition()->x + $explosionSize + 1);
        $minY = (int)floor($this->getPosition()->y - $explosionSize - 1);
        $maxY = (int)ceil($this->getPosition()->y + $explosionSize + 1);
        $minZ = (int)floor($this->getPosition()->z - $explosionSize - 1);
        $maxZ = (int)ceil($this->getPosition()->z + $explosionSize + 1);

        $explosionBB = new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);

        $list = $this->getWorld()->getNearbyEntities($explosionBB, $this);
        foreach ($list as $entity) {
            $entity->setMotion($entity->getPosition()->add(0, 3, 0));
            $entityPos = $entity->getPosition();
            $distance = $entityPos->distance($this->getPosition()) / $explosionSize;
            $motion = $entityPos->subtractVector($this->getPosition())->normalize();
            $impact = (1 - $distance) * $size;
            $entity->setMotion($motion->multiply($impact));
        }

        $this->getWorld()->addParticle($this->getPosition(), new HugeExplodeSeedParticle());
        $this->getWorld()->addSound($this->getPosition(), new ExplodeSound());
    }

    public function attack(EntityDamageEvent $source): void
    {
        parent::attack($source);
        $this->setNameTag($this->healhToGraphique(intval($this->getHealth())));
        if ($this->getHealth() <= 0) {
            if (($player = $source->getEntity()->hasSpawned[array_key_first($source->getEntity()->hasSpawned)] ?? null) instanceof Player) {
                $loot = $this->getRandomLoots();
                if ($player->getInventory()->canAddItem($loot)) {
                    $player->getInventory()->addItem($loot);
                } else {
                    $this->getWorld()->dropItem($this->getPosition(), $loot);
                }
            }
        } elseif ($this->getHealth() <= ($this->getMaxHealth()) - ($this->getMaxHealth() * 0.60) && $this->state === 0) {
            $this->reject();
            $pk = new AddActorPacket();
            $pk->type = "minecraft:lightning_bolt";
            $pk->actorUniqueId = 1001001;
            $pk->actorRuntimeId = 1001001;
            $pk->syncedProperties = new PropertySyncData([], []);
            $pk->metadata = [];
            $pk->motion = null;
            $pk->yaw = $this->getLocation()->getYaw();
            $pk->pitch = $this->getLocation()->getPitch();
            $pk->position = new Vector3($this->getPosition()->getX(), $this->getPosition()->getY(), $this->getPosition()->getZ());

            $sound = new PlaySoundPacket();
            $sound->soundName = "ambient.weather.thunder";
            $sound->x = $this->getPosition()->getX();
            $sound->y = $this->getPosition()->getY();
            $sound->z = $this->getPosition()->getZ();
            $sound->volume = 100;
            $sound->pitch = 1000;

            $this->reject();
            foreach ($this->getViewers() as $player) {
                $player->getNetworkSession()->sendDataPacket($pk);
                $player->getNetworkSession()->sendDataPacket($sound);
            }
            $this->state++;
        }
    }

    private function healhToGraphique(int $healt): string
    {
        $healt = floor(intval($healt) / ($this->DefineHealt / 10));
        $carreRed = 10 - $healt;

        $etage = "§2";
        for ($v = 0; $v < $healt; $v++) {
            $etage .= "⬛";
        }
        $etage .= "§4";
        for ($r = 0; $r < $carreRed; $r++) {
            $etage .= "⬛";
        }

        return $etage . "\n" . $etage;
    }

    /**
     * @return Item
     */
    private function getRandomLoots(): Item
    {
        $array = [
            VanillaItems::DIAMOND(),
            VanillaItems::EMERALD(),
            VanillaItems::APPLE()
        ];
        return $array[rand(0, count($array) - 1)];
    }

    public function saveNBT(): CompoundTag
    {
        $this->setBlockCollision(VanillaBlocks::AIR());
        return parent::saveNBT();
    }

    public function onNearbyBlockChange(): void
    {

    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(6.4, 1.0);
    }

    protected function initEntity(CompoundTag $nbt): void
    {
        $this->setNameTagAlwaysVisible(false);
        $healt = Server::getInstance()->getOnlinePlayers();
        $this->DefineHealt = count($healt) * 20 * 5 + 100;
        $this->setMaxHealth($this->DefineHealt);
        $this->setHealth($this->DefineHealt);
        $this->setNameTag($this->healhToGraphique($this->DefineHealt));
        parent::initEntity($nbt);
        $this->setScale(2.5);
    }

    protected function tryChangeMovement(): void
    {
    }

    protected function onDeath(): void
    {
        parent::onDeath();
        $this->flagForDespawn();
        $this->setBlockCollision(VanillaBlocks::AIR());
        self::$isRunning = false;
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
        return "goldrush:totem";
    }
}