<?php

namespace core\entities\projectils;

use core\entities\dynamites\CustomExplode;
use core\Main;
use core\messages\Messages;
use core\particles\IceParticle;
use core\utils\Utils;
use pocketmine\block\TNT;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\entity\projectile\Projectile;
use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\world\Explosion;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\sound\EndermanTeleportSound;
use pocketmine\world\sound\ExplodeSound;

class FreezePearlEntity extends Throwable
{

    protected function onHit(ProjectileHitEvent $event): void
    {
        $explode = new Explosion($this->getPosition(), 4, $this);
        $explode->explodeA();

        $ices = [
            VanillaBlocks::BLUE_ICE(),
            VanillaBlocks::PACKED_ICE()
        ];



        foreach ($explode->affectedBlocks as $block) {
            $area = Main::getInstance()->getAreaManager();
            if (!$area->isInArea($block->getPosition())) {
                $iceBlock = $ices[array_rand($ices)];
                $block->getPosition()->getWorld()->setBlock($block->getPosition(), $iceBlock);
                $this->getPosition()->getWorld()->addParticle($block->getPosition(), new DustParticle(DyeColor::WHITE()->getRgbValue()));
            }
        }
        $this->getPosition()->getWorld()->addSound($this->getPosition(), new ExplodeSound());

        $entities = $this->getViewers();
        foreach ($entities as $entity) {
            if ($entity->getPosition()->distanceSquared($this->getPosition()) <= 7) {
                $entity->getNetworkProperties()->setFloat(EntityMetadataProperties::FREEZING_EFFECT_STRENGTH, 1);
                $entity->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), 20 * 5, 1, false));
                $this->sendIceSound($entity);


                Utils::timeout(function () use ($entity): void {
                    if ($entity->isConnected()) {
                        $entity->getNetworkProperties()->setFloat(EntityMetadataProperties::FREEZING_EFFECT_STRENGTH, 0);
                    }
                }, 20 * 5);
            }
        }
    }


    public function sendIceSound(Player $player): void {
        $pk = new PlaySoundPacket();
        $pk->soundName = "music.ice";
        $pk->pitch = 1;
        $pk->x = $this->getPosition()->getX();
        $pk->y = $this->getPosition()->getY();
        $pk->z = $this->getPosition()->getZ();
        $pk->volume = 1;
        $player->getNetworkSession()->sendDataPacket($pk);
    }


    public function onUpdate(int $currentTick): bool
    {
        $this->getPosition()->getWorld()->addParticle($this->getPosition(), new DustParticle(DyeColor::WHITE()->getRgbValue()));
        return parent::onUpdate($currentTick);
    }

    public static function getNetworkTypeId(): string
    {
        return 'goldrush:freeze_pearl';
    }
}