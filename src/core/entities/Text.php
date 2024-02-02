<?php

namespace core\entities;

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

class Text extends Entity
{
    public float $gravity = 0.0;
    public $canCollide = false;
    public bool $keepMovement = true;
    protected bool $gravityEnabled = false;
    protected $immobile = true;
    public string $nameFloating = "";

    public function __construct(Location $location, ?CompoundTag $nbt = null, string $name = "")
    {
        $this->nameFloating = $name;
        if (!is_null($nbt)) {
            $name = $nbt->getString('floating', '404');
            if ($name !== '404') $this->nameFloating = $name;
        }

        parent::__construct($location, $nbt);
        $this->setScale(0.00000000001);
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::EGG;
    }

    public function onUpdate(int $currentTick): bool
    {
        $this->setNameTag($this->nameFloating);
        return parent::onUpdate($currentTick);
    }

    public function setNameTag(string $name): void
    {
        parent::setNameTag($name);
        $this->sendData($this->hasSpawned, $this->getDirtyNetworkData());
        $this->getNetworkProperties()->clearDirtyProperties();
    }

    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();
        $nbt->setString('floating', $this->nameFloating);
        return $nbt;
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

    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->setNameTagAlwaysVisible(true);
        $name = $nbt->getString('floating', 'null');
        if ($name !== 'null') {
            $this->nameFloating = $name;
        } else $name = $this->nameFloating;
        $this->setNameTag($name);
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.5, 0.5);
    }

    protected function syncNetworkData(EntityMetadataCollection $properties): void
    {
        $properties->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, $this->alwaysShowNameTag ? 1 : 0);
        $properties->setFloat(EntityMetadataProperties::SCALE, $this->getScale());
        $properties->setString(EntityMetadataProperties::NAMETAG, $this->nameFloating);
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