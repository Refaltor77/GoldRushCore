<?php

namespace core\entities;

use core\api\gui\ChestInventory;
use core\events\LogEvent;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Air;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Opaque;
use pocketmine\block\VanillaBlocks;
use pocketmine\color\Color;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\NoteInstrument;
use pocketmine\world\sound\NoteSound;

class AirDrops extends Entity
{

    private $tick = 0;
    private $sec = 0;
    private $minute = 5;

    private $radius = 1;
    private $particles = 10;
    private $sphere = true;
    private $isFall = true;

    private array $wiewers = [];

    private ChestInventory $inv;

    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        $p = $location;
        (new LogEvent("Apparition d'un largage au coordonées ({$p->getX()},{$p->getY()},{$p->getZ()})", LogEvent::EVENT_TYPE))->call();
        parent::__construct($location, $nbt);
        $this->inv = new ChestInventory();


        $lots = [
            CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_FORCE, 2),
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT, 32),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SWORD, 2),
            CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE, 1),
            CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE, 2),
            CustomiesItemFactory::getInstance()->get(Ids::KEY_COMMON, 4),
            CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_HEAL, 4),
            CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_SPEED, 2),
            CustomiesItemFactory::getInstance()->get(Ids::UNCLAIM_FINDER_AMETHYST, 1),
            CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_CHESTPLATE, 1),
            CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE, 64),
            CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_NUGGET, 64),
        ];



        for ($i = 0; $i < 6; $i++) {
            $this->inv->addItem($lots[array_rand($lots)]);
        }

        $this->inv->setClickCallback(function (Player $requester, Inventory $inventory, Item $source, Item $target, int $slot): void {
            $this->inv->setContents($inventory->getContents());
        });

        $this->inv->setCloseCallback(function (Player $player, Inventory $inventory) {
            if (empty($inventory->getContents())) {
                if (empty($this->wiewers)) {
                    if (!$this->isFlaggedForDespawn()) $this->flagForDespawn();
                } elseif (isset($this->wiewers[$player->getXuid()]) && count($this->wiewers) <= 1) {
                    if (!$this->isFlaggedForDespawn()) $this->flagForDespawn();
                }
            }
            if (isset($this->wiewers[$player->getXuid()])) unset($this->wiewers[$player->getXuid()]);
        });


        if (!$this->location->getWorld()->isChunkLoaded($this->location->getFloorX() >> 4, $this->location->getFloorZ() >> 4)) {
            $this->location->getWorld()->loadChunk($this->location->getFloorX() >> 4, $this->location->getFloorZ() >> 4);
        }
    }


    public static function getNetworkTypeId(): string
    {
        return "goldrush:airdrop";
    }

    public static function getRandomPos(): Location
    {
        $array_pos = [
            [212, 72, -16],
            [268, 74, 62],
            [235, 76, 136],
            [198, 73, 198],
            [196, 72, 256],
            [157, 70, 242],
            [117, 70, 278],
            [-92, 71, 275],
            [-128, 71, 205],
            [-244, 72, 232],
            [-209, 76, 138],
            [-262, 78, 61],
            [-185, 78, -69],
        ];
        $pos = $array_pos[array_rand($array_pos)];
        $pos = new Location($pos[0] + 0.5, 255, $pos[2] + 0.5, Server::getInstance()->getWorldManager()->getDefaultWorld(), 0, 0);

        $pos->getWorld()->loadChunk($pos->getFloorX() >> 4, $pos->getFloorZ() >> 4);

        return $pos;
    }

    public function onNearbyBlockChange(): void
    {

    }

    public function attack(EntityDamageEvent $source): void
    {
        $cause = $source->getCause();
        if (!is_null($cause)) {
            if ($cause == EntityDamageEvent::CAUSE_FALL) {
                $worlds = Server::getInstance()->getWorldManager()->getWorlds();
                foreach ($worlds as $world) {
                    foreach ($world->getEntities() as $entity) {
                        if ($entity instanceof AirDrops && $entity->getId() != $this->getId()) {
                            if ($this->getWorld()->getBlock($this->getPosition())->getTypeId() == BlockTypeIds::BARRIER) {
                                $this->getWorld()->setBlock($this->getPosition(), VanillaBlocks::AIR());
                            }
                            $entity->flagForDespawn();
                        }
                    }
                }
                for ($i = 0; $i < $this->particles; ++$i) {
                    $vector = self::getRandomVector()->multiply($this->radius);
                    if (!$this->sphere) {
                        $vector->y = abs($vector->getY());
                    }
                    $this->getWorld()->addParticle($this->location->add($vector->x, $vector->y, $vector->z), new HappyVillagerParticle());
                    $this->location->add($vector->x, $vector->y, $vector->z);
                }
                $this->getWorld()->addSound($this->getPosition(), new AnvilFallSound());
                $this->isFall = false;
            }
        }
    }

    private static function getRandomVector(): Vector3
    {
        $x = rand() / getrandmax() * 2 - 1;
        $y = rand() / getrandmax() * 2 - 1;
        $z = rand() / getrandmax() * 2 - 1;
        $v = new Vector3($x, $y, $z);
        return $v->normalize();
    }

    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        $this->inv->send($player);
        $this->wiewers[$player->getXuid()] = true;
        return parent::onInteract($player, $clickPos);
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if (!$this->location->getWorld()->isChunkLoaded($this->location->getFloorX() >> 4, $this->location->getFloorZ() >> 4)) {
            $this->location->getWorld()->loadChunk($this->location->getFloorX() >> 4, $this->location->getFloorZ() >> 4);
        }
        return parent::entityBaseTick($tickDiff);
    }

    protected function onDeath(): void
    {
        parent::onDeath();
    }

    public function onUpdate(int $currentTick): bool
    {
        $p = $this->getPosition();
        $this->checkGroundState($p->getX(), $p->getY(), $p->getZ(), $this->getMotion()->getX(), -$this->getInitialGravity(), $this->getMotion()->getZ());
        if (!$this->isOnGround()) {
            $this->resetFallDistance();
        }


        $this->getWorld()->addParticle(new Position(
            $this->getPosition()->getFloorX() + 0.5,
            $this->getWorld()->getHighestBlockAt($this->getPosition()->getFloorX(),$this->getPosition()->getFloorZ()) + 3,
            $this->getPosition()->getFloorZ() + 0.5,
            $this->getWorld()
        ), new DustParticle(new Color(255, 0, 0)), $this->getViewers());

        return parent::onUpdate($currentTick);
    }

    public function spawnToAll(): void
    {
        $array_pos = [
            [212, 72, -16],
            [268, 74, 62],
            [235, 76, 136],
            [198, 73, 198],
            [196, 72, 256],
            [157, 70, 242],
            [117, 70, 278],
            [-92, 71, 275],
            [-128, 71, 205],
            [-244, 72, 232],
            [-209, 76, 138],
            [-262, 78, 61],
            [-185, 78, -69],
        ];


        foreach ($array_pos as $posValue) {
            $pos = new Position($posValue[0], $posValue[1], $posValue[2], Server::getInstance()->getWorldManager()->getDefaultWorld());
            $entity = $pos->getWorld()->getNearestEntity($pos, 10, AirDrops::class);
            if ($entity instanceof AirDrops) {
                if ($entity->getId() !== $this->getId()) {
                    $entity->flagForDespawn();
                }
            }
        }


        $x = intval($this->getPosition()->getX());
        $z = intval($this->getPosition()->getZ());
        $msg = "§6§l---\n";
        $msg .= "§r§fUn larguage tombe en warzone §6!\n";
        $msg .= "§r§7Description §8: §fSoyez le premier à prendre\n";
        $msg .= "le larguage !\n";
        $msg .= "§fCoordonnées §8: §e[x]$x §f: §e[z]$z\n";
        $msg .= "§6§l---\n";
        Server::getInstance()->broadcastMessage($msg);
        parent::spawnToAll();
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1, 1.1, 0);
    }

    protected function initEntity(CompoundTag $nbt): void
    {
        $this->setNameTagAlwaysVisible();
        $this->setScale(1.07);
        parent::initEntity($nbt);
    }



    protected function getInitialDragMultiplier(): float
    {
        return 0.0;
    }

    protected function getInitialGravity(): float
    {
        return 0.0005;
    }
}