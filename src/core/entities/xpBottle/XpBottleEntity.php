<?php

namespace core\entities\xpBottle;

use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\particle\PotionSplashParticle;
use pocketmine\world\sound\PotionSplashSound;

class XpBottleEntity extends Throwable
{
    public static function getNetworkTypeId() : string{ return EntityIds::XP_BOTTLE; }

    protected function getInitialGravity() : float{ return 0.07; }

    public function getResultDamage() : int{
        return -1;
    }

    public function onHit(ProjectileHitEvent $event) : void{
        $this->getWorld()->addParticle($this->location, new PotionSplashParticle(PotionSplashParticle::DEFAULT_COLOR()));
        $this->broadcastSound(new PotionSplashSound());

        $this->getWorld()->dropExperience($this->location, 10);
    }
}