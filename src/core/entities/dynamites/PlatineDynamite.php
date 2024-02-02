<?php

namespace core\entities\dynamites;

use pocketmine\block\TNT;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityPreExplodeEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\player\Player;
use pocketmine\world\Explosion;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\particle\PotionSplashParticle;
use pocketmine\world\Position;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\world\sound\PotionSplashSound;

class PlatineDynamite extends Throwable
{

    public static function getNetworkTypeId(): string
    {
        return "goldrush:dynamite_platinum";
    }


    protected function getInitialGravity() : float{ return 0.07; }

    public function getResultDamage() : int{
        return -1;
    }


    public function onHit(ProjectileHitEvent $event) : void{
        for ($i = 0; $i < 6; ++$i) {
            $this->getWorld()->addParticle($this->location, new HugeExplodeParticle());
        }
        $this->getWorld()->addSound($this->getPosition(), new ExplodeSound());

        $ev = new EntityPreExplodeEvent($this, 1);
        $ev->call();
        if(!$ev->isCancelled()){
            $explosion = new CustomExplodeDyna(Position::fromObject($this->location->add(0, $this->size->getHeight() / 2, 0), $this->getWorld()), $ev->getRadius(), $this);
            if($ev->isBlockBreaking()){
                $explosion->explodeA();
            }
            $explosion->explodeB();
        }


        $this->flagForDespawn();
    }
}