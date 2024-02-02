<?php

namespace core\entities\horse;

use core\items\ExtraVanillaItem;
use core\listeners\types\horse\HorseEvent;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\player\Player;
use pocketmine\Server;

class EntityUtils extends Living
{
    const STATE_SITTING  = 1;
    const STATE_STANDING = 0;
    const LINK_RIDING = 0;
    const LINK_RIDER  = 1;
    protected int $jumpTicks = 0;

    private ?Player $owner = null;
    public ?Player $rider = null;

    /** @var EntityLink[] */
    private $links = [];

    protected function syncNetworkData(EntityMetadataCollection $properties): void {
        parent::syncNetworkData($properties);

        $properties->setGenericFlag(EntityMetadataFlags::TAMED, true);
        $properties->setGenericFlag(EntityMetadataFlags::RIDING, true);
    }

    public function setRider(Player $player): bool {

        $this->rider = $player;
        $player->canCollide = false;
        $riderSeatPos = new Vector3(0, 1.3 + $this->getScale() * 0.9, -0.25);

        $player->getNetworkProperties()->setVector3(EntityMetadataProperties::RIDER_SEAT_POSITION, $riderSeatPos);
        $player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::WASD_CONTROLLED, true);
        $player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::RIDING, true);
        $this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SADDLED, true);
        $this->getNetworkProperties()->setByte(EntityMetadataProperties::CONTROLLING_RIDER_SEAT_NUMBER, 0);





        $this->addLink($player, self::LINK_RIDER);

        $this->saddled = true;
        $this->networkPropertiesDirty = true;

        $this->size = new EntitySizeInfo(
            max(($riderSeatPos->y / 2.5) + $player->size->getHeight(), $this->size->getHeight()),
            max($player->size->getWidth(), $this->size->getWidth())
        );

        $this->recalculateBoundingBox();
        HorseEvent::$playerMount[$player->getName()] = $this->getId();
        $this->rider = $player;
        return true;
    }

    public function getVitesse(): float {
        return 1;
    }


    public function addLink(Entity $entity, int $type): void {
        $this->removeLink($entity, $type);
        $viewers = $this->getViewers();

        switch($type) {
            case self::LINK_RIDER:
                $link = new EntityLink($this->getId(), $entity->getId(), self::STATE_SITTING, true, true);

                if($entity instanceof Player) {
                    $pk = new SetActorLinkPacket();
                    $pk->link = $link;
                    $entity->getNetworkSession()->sendDataPacket($pk);

                    $link_2 = new EntityLink($this->getId(), 0, self::STATE_SITTING, true, true);

                    $pk = new SetActorLinkPacket();
                    $pk->link = $link_2;
                    $entity->getNetworkSession()->sendDataPacket($pk);
                    unset($viewers[$entity->getId()]);
                }
                break;
            case self::LINK_RIDING:
                $link = new EntityLink($entity->getId(), $this->getId(), self::STATE_SITTING, true, false);

                if($entity instanceof Player) {
                    $pk = new SetActorLinkPacket();
                    $pk->link = $link;
                    $entity->getNetworkSession()->sendDataPacket($pk);

                    $link_2 = new EntityLink($entity->getId(), 0, self::STATE_SITTING, true, false);

                    $pk = new SetActorLinkPacket();
                    $pk->link = $link_2;
                    $entity->getNetworkSession()->sendDataPacket($pk);
                    unset($viewers[$entity->getId()]);
                }
                break;
            default:
                throw new \InvalidArgumentException();
        }

        if(!empty($viewers)) {
            $pk = new SetActorLinkPacket();
            $pk->link = $link;
            foreach ($viewers as $player) {
                if ($player instanceof Player) {
                    $player->getNetworkSession()->sendDataPacket($pk);
                }
            }
        }

        $this->links[$type] = $link;
    }

    public function removeLink(Entity $entity, int $type): void {
        if(!isset($this->links[$type])) {
            return;
        }

        $viewers = $this->getViewers();

        switch($type) {
            case self::LINK_RIDER:
                $link = new EntityLink($this->getId(), $entity->getId(), self::STATE_STANDING, true, true);

                if($entity instanceof Player) {
                    $pk = new SetActorLinkPacket();
                    $pk->link = $link;
                    $entity->getNetworkSession()->sendDataPacket($pk);

                    $link_2 = new EntityLink($this->getId(), 0, self::STATE_STANDING, true, true);

                    $pk = new SetActorLinkPacket();
                    $pk->link = $link_2;
                    $entity->getNetworkSession()->sendDataPacket($pk);
                    unset($viewers[$entity->getId()]);
                    $this->rider = null;
                }
                break;
            case self::LINK_RIDING:
                $link = new EntityLink($entity->getId(), $this->getId(), self::STATE_STANDING, true, false);

                if($entity instanceof Player) {
                    $pk = new SetActorLinkPacket();
                    $pk->link = $link;
                    $entity->getNetworkSession()->sendDataPacket($pk);

                    $link_2 = new EntityLink($entity->getId(), 0, self::STATE_STANDING, true, false);

                    $pk = new SetActorLinkPacket();
                    $pk->link = $link_2;
                    $entity->getNetworkSession()->sendDataPacket($pk);
                    unset($viewers[$entity->getId()]);
                    $this->rider = null;
                }
                break;
            default:
                $this->rider = null;
                throw new \InvalidArgumentException();
        }

        unset($this->links[$type]);

        if(!empty($viewers)) {
            $pk = new SetActorLinkPacket();
            $pk->link = $link;
            foreach ($viewers as $player) {
                if ($player instanceof Player) {
                    $player->getNetworkSession()->sendDataPacket($pk);
                }
            }
        }
    }

    public function jump(): void {
        parent::jump();
        $this->jumpTicks = 5;
    }

    public function doUpdates(int $currentTick): bool {

        if($this->jumpTicks > 0) {
            --$this->jumpTicks;
        }

        if(!$this->isOnGround()) {
            if($this->motion->y > -$this->gravity * 4) {
                $this->motion->y = -$this->gravity * 4;
            } else {
                $this->motion->y += $this->isUnderwater() ? $this->gravity : -$this->gravity;
            }
        } else {
            if($this->isCollidedHorizontally && $this->jumpTicks === 0) {
                $this->jump();
            } else {
                $this->motion->y -= $this->gravity;
            }
        }

        if($this->rider !== null){
            $player = $this->rider;
            if ($player instanceof Player) {
                $this->setRotation($player->getLocation()->yaw, $player->getLocation()->pitch);
            }
        }


        $this->updateMovement();
        return true;
    }

    public function onUpdate(int $currentTick): bool
    {
        if ($this->rider === null) $this->flagForDespawn();
        $this->doUpdates($currentTick);
        return true;
    }

    public function doRidingMovement(Player $player, float $motionX, float $motionZ): void {
        $rider = $player;

        $this->location->pitch = $rider->location->pitch;
        $this->location->yaw = $rider->location->yaw;

        $speed_factor = $this->getVitesse();
        $direction_plane = $this->getDirectionPlane();
        $x = $direction_plane->x / $speed_factor;
        $z = $direction_plane->y / $speed_factor;

        switch($motionZ) {
            case 1:
                $finalMotionX = $x;
                $finalMotionZ = $z;
                break;
            case -1:
                $finalMotionX = -$x;
                $finalMotionZ = -$z;
                break;
            default:
                $average = $x + $z / 2;
                $finalMotionX = $average / 1.414 * $motionZ;
                $finalMotionZ = $average / 1.414 * $motionX;
                break;
        }

        switch($motionX) {
            case 1:
                $finalMotionX = $z;
                $finalMotionZ = -$x;
                break;
            case -1:
                $finalMotionX = -$z;
                $finalMotionZ = $x;
                break;
        }
        $this->move($finalMotionX, $this->motion->y, $finalMotionZ);
        $this->updateMovement();
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(2, 1, 2);
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::HORSE;
    }


    protected function initEntity(CompoundTag $nbt): void
    {
        $this->getNetworkProperties()->setString(EntityMetadataProperties::INTERACTIVE_TAG, "Ride");
        parent::initEntity($nbt);
    }


    public function setChestPlate(Item $item){
        /*
        416, 417, 418, 419 only
        */


        $itemStack = new ItemStack(
            $item->getTypeId(),
            $item->getStateId(),
            $item->getCount(),
            0,
            null,
            [],
            []
        );

        foreach($this->getWorld()->getPlayers() as $player){
            $player->getNetworkSession()->sendDataPacket(MobArmorEquipmentPacket::create(
                $this->getId(),
                new ItemStackWrapper($item->getTypeId(), $itemStack),
                new ItemStackWrapper($item->getTypeId(), $itemStack),
                new ItemStackWrapper($item->getTypeId(), $itemStack),
                new ItemStackWrapper($item->getTypeId(), $itemStack)
            ));
        }
    }

    public function getName(): string
    {
        return "Monture";
    }
}