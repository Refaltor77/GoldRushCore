<?php

namespace core\entities\classements;

use core\Main;
use core\managers\stats\StatsManager;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;

class TopEntity extends Entity
{
    public float $gravity = 0.0;
    public $canCollide = false;
    public bool $keepMovement = true;
    protected bool $gravityEnabled = false;
    protected float $drag = 0.0;
    protected float $scale = 0.0;
    protected $immobile = true;
    private string $topName;

    public function __construct(Location $location, ?CompoundTag $nbt = null, string $top = 'common')
    {
        $this->topName = $top;
        if (!is_null($nbt)) {
            $this->topName = $nbt->getString('top_name', 'common');
        }

        parent::__construct($location, $nbt);
        $this->setScale(0.00000000001);
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::EGG;
    }

    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();
        $nbt = $nbt->setString('top_name', $this->getTopName());
        return $nbt;
    }

    public function getTopName(): string
    {
        return $this->topName;
    }

    public function isFireProof(): bool
    {
        return true;
    }

    public function canBeCollidedWith(): bool
    {
        return false;
    }

    public function canCollideWith(Entity $entity): bool
    {
        return false;
    }

    public function canBeMovedByCurrents(): bool
    {
        return false;
    }

    public function getOffsetPosition(Vector3 $vector3): Vector3
    {
        return parent::getOffsetPosition($vector3)->add(0.0, 0.49, 0.0);
    }

    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();
    }

    public int $tick = 0;



    public function setNameTag(string $name): void
    {
        parent::setNameTag($name);
        $this->sendData($this->hasSpawned, $this->getDirtyNetworkData());
        $this->getNetworkProperties()->clearDirtyProperties();
    }

    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->setNameTagAlwaysVisible(true);
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.0, 0.0);
    }

    protected function syncNetworkData(EntityMetadataCollection $properties): void
    {
        $properties->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, $this->alwaysShowNameTag ? 1 : 0);
        $properties->setFloat(EntityMetadataProperties::SCALE, $this->scale);
        $properties->setString(EntityMetadataProperties::NAMETAG, $this->nameTag);
        $properties->setGenericFlag(EntityMetadataFlags::IMMOBILE, $this->immobile);
        $properties->setInt(EntityMetadataProperties::VARIANT, VanillaBlocks::AIR()->getTypeId());

    }

    protected function checkBlockCollision(): void
    {

    }

    protected function entityBaseTick(int $tickDiff = 1): bool
    {
        return false;
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.0;
    }

    protected function getInitialGravity(): float
    {
        return 0.0;
    }
}