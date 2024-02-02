<?php

namespace core\entities\horse;

use core\listeners\types\horse\HorseEvent;
use pocketmine\entity\Attribute;
use pocketmine\entity\AttributeFactory;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;

class Horse extends EntityUtils
{

    private float $vitesse = 1;

    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->jumpVelocity = $this->gravity * 20;
    }

    public function setVitesse(float $vitesse): void {
        $this->vitesse = $vitesse;
    }


    public function attack(EntityDamageEvent $source): void
    {

        $this->getArmorInventory()->setContents([]);
        $rider = $this->rider;
        if ($rider instanceof Player) {
            if ($source instanceof EntityDamageByEntityEvent) {
                if (isset(HorseEvent::$playerMount[$rider->getName()])) {
                    $entity = $rider->getWorld()->getEntity((int)HorseEvent::$playerMount[$rider->getName()]);
                    unset(HorseEvent::$playerMount[$rider->getName()]);
                    $entity->flagForDespawn();
                }
            }
        }
        parent::attack($source);
    }

    public function getVitesse(): float
    {
        return $this->vitesse;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1.6, 2);
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::HORSE;
    }

    public function getName(): string
    {
        return "horse";
    }

    public function spawnToAllRiding() : void
    {
        $this->spawnToAll();
        $this->getAttributeMap()->add(AttributeFactory::getInstance()->get(Attribute::HORSE_JUMP_STRENGTH));
        $this->setCanSaveWithChunk(false);
    }
}