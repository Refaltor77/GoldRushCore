<?php

namespace core\entities\ia;

use core\player\CustomPlayer;
use core\utils\Utils;
use pocketmine\block\Block;
use pocketmine\block\Liquid;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\math\Math;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;
use pocketmine\world\Position;

abstract class LivingCustom extends Living implements EntityAI
{
    /** @var Player[]  */
    private array $oldViewers = [];
    private float $speed = 1;

    private ?Entity $targetEntity = null;
    private int $distanceTarget = 0;

    public bool $run = false;
    public bool $idle = true;
    public bool $attack = false;
    public bool $move = false;
    public bool $death = false;

    public array $hasSendIdle = [];
    public array $hasSendRun = [];
    public array $hasSendMove = [];
    public array $hasSendDeath = [];
    public array $hasSendAttack = [];

    private array $jumpsCaching = [];
    private float $addVelocity = 0.0;
    public bool $hasInit = false;

    public int $deathTimeoutTick = 20;

    public float $forceKnockBack = 0.01;

    public function jumpEntity(): void
    {

        $jumpForce = 0.2; // Réglez cette valeur en fonction de la hauteur de saut souhaitée
        $this->setMotion($this->getMotion()->add(0, $jumpForce, 0));
    }


    public function initEntityCustom(): void {
        // code execute une fois par restart

        $this->hasInit = true;
    }


    public function setSpeed(float $speed): void {
        $this->speed = $speed;
    }

    public function chargeViewers(): void {
        $viewers = $this->getViewers();
        foreach ($this->oldViewers as $player) {
            if (!$player->isConnected() || !in_array($player, $viewers)) {
                if (isset($this->hasSendIdle[$player->getXuid()])) {
                    unset($this->hasSendIdle[$player->getXuid()]);
                }
                if (isset($this->hasSendRun[$player->getXuid()])) {
                    unset($this->hasSendRun[$player->getXuid()]);
                }
                if (isset($this->hasSendMove[$player->getXuid()])) {
                    unset($this->hasSendMove[$player->getXuid()]);
                }
            }
        }
        $this->oldViewers = $viewers;
    }


    public function lookAtEntity(Vector3 $target) : array{
        $horizontal = sqrt(($target->x - $this->location->x) ** 2 + ($target->z - $this->location->z) ** 2);
        $vertical = $target->y - ($this->location->y + $this->getEyeHeight());
        $pitch = -atan2($vertical, $horizontal) / M_PI * 180; //negative is up, positive is down

        $xDist = $target->x - $this->location->x;
        $zDist = $target->z - $this->location->z;

        $yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
        if($yaw < 0){
            $yaw += 360.0;
        }

        $this->setRotation($yaw, $pitch);

        return [
            $yaw, $pitch
        ];
    }


    protected function checkJump($dx, $dz): bool{
        if($this->motion->y === $this->gravity * 2){ // swimming
            return $this->getWorld()->getBlock(new Vector3(Math::floorFloat($this->getPosition()->x), (int) $this->getPosition()->y, Math::floorFloat($this->getPosition()->z))) instanceof Liquid;
        }else{ // dive up?
            if($this->getWorld()->getBlock(new Vector3(Math::floorFloat($this->getPosition()->x), (int) ($this->getPosition()->y + 0.8), Math::floorFloat($this->getPosition()->z))) instanceof Liquid){
                $this->motion->y = $this->gravity * 2; // set swimming (rather walking on water ;))
                return true;
            }
        }


        $entityUpperBlock = $this->getWorld()->getBlock(new Vector3(Math::floorFloat($this->getPosition()->x), Math::floorFloat($this->getPosition()->y + $this->getSize()->getHeight()) + 1, Math::floorFloat($this->getPosition()->z)));
        if(!empty($entityUpperBlock->getCollisionBoxes())){
            return false;
        }

        $highestBlock = 0;
        $lowestBlock = null;
        foreach($this->getWorld()->getCollisionBlocks($this->boundingBox->addCoord($this->motion->x, $this->motion->y, $this->motion->z)) as $_ => $block){

            $blockBox = $block->getCollisionBoxes()[0] ?? null;
            $blockBoxDiff = $blockBox === null ? 0 : $blockBox->maxY - $blockBox->minY;

            if($blockBox === null or ($block->getCollisionBoxes()[0]->maxY - $this->boundingBox->minY) <= 0){
                continue;
            }


            $upperblock = $this->getWorld()->getBlock($block->getPosition()->add(0, 1, 0));
            if(!empty($upperblock->getCollisionBoxes())){
                $blockBoxDiff = $blockBoxDiff + 1;
            }
            if($blockBoxDiff > $highestBlock){
                $highestBlock = $blockBoxDiff;
            }
            if($lowestBlock === null){
                $lowestBlock = $blockBoxDiff;
            }elseif($lowestBlock > $blockBoxDiff){
                $lowestBlock = $blockBoxDiff;
            }

        }
        if($lowestBlock > 0 && $lowestBlock <= 0.5 && $lowestBlock <= 1){
            $this->motion->y = $this->gravity * 4;
            return true;
        }elseif($lowestBlock > 0 && $lowestBlock <= 1){
            $this->motion->y = $this->gravity * 3.2;
            return true;
        }elseif($lowestBlock > 0 && $lowestBlock > 1){

         }else{

        }
        return false;

    }


    public function chargeIdle(string $animateName): void
    {
        if ($this->idle) {
            $this->hasSendRun = [];
            $this->hasSendMove = [];
            $this->hasSendDeath = [];
            $this->hasSendAttack = [];
            foreach ($this->getViewers() as $player) {
                if ($player->getPosition()->distance($this->getPosition()) >= 80) return;
                if (!isset($this->hasSendIdle[$player->getXuid()]) && $player->hasReallyConnected) {
                    $this->hasSendIdle[$player->getXuid()] = true;
                    Utils::timeout(function () use  ($player, $animateName) : void {
                        if ($player->isConnected() && !$this->isFlaggedForDespawn()) {
                            $pk = AnimateEntityPacket::create($animateName, "", "", 0, "", 0, [$this->getId()]);
                            $player->getNetworkSession()->sendDataPacket($pk);
                        }
                    }, 1);
                }
            }
        }
    }

    public function chargeRun(string $animateName): void
    {
        if ($this->run && !$this->attack) {
            $this->hasSendIdle = [];
            $this->hasSendMove = [];
            $this->hasSendDeath = [];
            $this->hasSendAttack = [];
            foreach ($this->getViewers() as $player) {
                if (!isset($this->hasSendRun[$player->getXuid()]) && $player->hasReallyConnected) {
                    $this->hasSendRun[$player->getXuid()] = true;
                    $pk = AnimateEntityPacket::create($animateName, "", "", 0, "", 0, [$this->getId()]);
                    $player->getNetworkSession()->sendDataPacket($pk);
                }
            }
        }
    }

    public function chargeWalk(string $animateName): void
    {
        if ($this->move) {
            $this->hasSendIdle = [];
            $this->hasSendRun = [];
            $this->hasSendDeath = [];
            $this->hasSendAttack = [];
            foreach ($this->getViewers() as $player) {
                if (!isset($this->hasSendMove[$player->getXuid()]) && $player->hasReallyConnected) {
                    $this->hasSendMove[$player->getXuid()] = true;
                    $pk = AnimateEntityPacket::create($animateName, "", "", 0, "", 0, [$this->getId()]);
                    $player->getNetworkSession()->sendDataPacket($pk);
                }
            }
        }
    }

    public function chargeAttack(string $animateName, int $ticksDelays, int $attackTime = 20): void
    {
        if ($this->attack) {
            $this->hasSendIdle = [];
            $this->hasSendRun = [];
            $this->hasSendDeath = [];
            foreach ($this->getViewers() as $player) {
                if (!isset($this->hasSendAttack[$player->getXuid()]) && $player->hasReallyConnected) {
                    $this->hasSendAttack[$player->getXuid()] = true;
                    Utils::timeout(function () use ($animateName, $player) : void {
                        if ($player->isConnected() && !$this->isFlaggedForDespawn()) {
                            $pk = AnimateEntityPacket::create($animateName, "", "", 0, "", 0, [$this->getId()]);
                            $player->getNetworkSession()->sendDataPacket($pk);
                        }
                    }, $ticksDelays);
                }
            }
            Utils::timeout(function (): void {
                if (!$this->isFlaggedForDespawn()) {
                    $this->hasSendAttack = [];
                    $this->attack = false;
                }
            }, $attackTime);
        }
    }


    public function chargeDeath(string $animateName): void
    {
        if ($this->death) {
            $this->hasSendIdle = [];
            $this->hasSendRun = [];
            $this->hasSendMove = [];
            foreach ($this->getViewers() as $player) {
                if (!isset($this->hasSendDeath[$player->getXuid()]) && $player->hasReallyConnected) {
                    $this->hasSendDeath[$player->getXuid()] = true;
                    $pk = AnimateEntityPacket::create($animateName, "", "", 0, "", 0, [$this->getId()]);
                    $player->getNetworkSession()->sendDataPacket($pk);
                }
            }
        }
    }

    protected function onDeath(): void
    {





        Utils::timeout(function (): void {
            if (!$this->isFlaggedForDespawn()) {
                $this->flagForDespawn();
            }
        }, $this->deathTimeoutTick);
    }

    public function getTarget(): ?Entity {
        return $this->targetEntity;
    }


    public function moveAroundObstacle(float &$dx, float &$dz)
    {
        // Rayon de détection de l'obstacle
        $obstacleRadius = 1.0;

        // Calculez l'angle entre l'entité et la cible
        $angleToTarget = atan2($dz, $dx);

        // Pour chaque angle autour de l'entité (par exemple, de -pi/4 à pi/4)
        for ($angleOffset = -M_PI / 4; $angleOffset <= M_PI / 4; $angleOffset += 0.1) {
            // Calculez un nouvel angle en ajoutant un décalage
            $newAngle = $angleToTarget + $angleOffset;

            // Calculez les nouvelles valeurs de déplacement dx et dz
            $newDx = cos($newAngle);
            $newDz = sin($newAngle);

            // Vérifiez si un bloc solide est présent à la nouvelle position
            $newX = $this->getPosition()->getX() + $obstacleRadius * $newDx;
            $newZ = $this->getPosition()->getZ() + $obstacleRadius * $newDz;

            $newBlock = $this->getWorld()->getBlockAt(floor($newX), floor($this->getPosition()->getY()), floor($newZ));

            if (!$newBlock->isSolid()) {
                // Si le bloc n'est pas solide, utilisez ces nouvelles valeurs de déplacement
                $dx = $newDx;
                $dz = $newDz;

                $this->move($dx, 0, $dz);
                return;
            }
        }
    }

    public function chargeCustom(): void
    {

    }

    public function setTarget(?Entity $entity, int $distance = 10) {
        $this->targetEntity = $entity;
        $this->distanceTarget = $distance;
    }


    public function setForceKnockBack(float $force): void {
        $this->forceKnockBack = $force;
    }

    public function knockBack(float $x, float $z, float $force = self::DEFAULT_KNOCKBACK_FORCE, ?float $verticalLimit = self::DEFAULT_KNOCKBACK_VERTICAL_LIMIT): void
    {
        $force = $this->forceKnockBack;
        $f = sqrt($x * $x + $z * $z);
        if($f <= 0){
            return;
        }
        if(mt_rand() / mt_getrandmax() > $this->knockbackResistanceAttr->getValue()){
            $f = 1 / $f;

            $motionX = $this->motion->x / 2;
            $motionY = $this->motion->y / 2;
            $motionZ = $this->motion->z / 2;
            $motionX += $x * $f * $force;
            $motionY += $force;
            $motionZ += $z * $f * $force;

            $verticalLimit ??= $force;
            if($motionY > $verticalLimit){
                $motionY = $verticalLimit;
            }

            $this->setMotion(new Vector3($motionX, $motionY, $motionZ));
        }
    }

    public function onUpdate(int $currentTick): bool
    {

        if ($this->getHealth() <= 5.0) {
            $this->setMaxHealth($this->getMaxHealth());
            $this->onDeath();
        }

        $entityTarget = $this->targetEntity;
        if ($entityTarget !== null) {
            if ($entityTarget->getPosition()->distance($this->getEyePos()) >= $this->distanceTarget) {
                $this->setTarget(null, 0);
                return false;
            }


            $targetX = $entityTarget->getPosition()->getX();
            $targetZ = $entityTarget->getPosition()->getZ();

            $currentX = $this->getPosition()->getX();
            $currentZ = $this->getPosition()->getZ();

            $dx = $targetX - $currentX;
            $dz = $targetZ - $currentZ;
            $diff = abs($dx) + abs($dz);


            $this->lookAtEntity($entityTarget->getEyePos());

            $speed = $this->speed;


            if ($diff > 0) {
                $dx = $speed * 0.15 * ($dx / $diff);
                $dz = $speed * 0.15 * ($dz / $diff);
            }


            $cache = $this->jumpsCaching;
            $this->jumpsCaching[] = $oldX = $this->getLocation()->getX();
            $this->jumpsCaching[] = $oldZ = $this->getLocation()->getZ();


            if (!empty($cache)) {
                if ((int)$cache[0] === (int)$oldX && (int)$cache[1] === (int)$oldZ) {
                    $this->addVelocity += 0.01;
                }
            }


            $this->move($dx, 0, $dz);
            $update = parent::onUpdate($currentTick);



            if ($this->getLocation()->getX() === $oldX) {
                $this->move(0, $this->getJumpVelocity() + $this->addVelocity, 0);
            } elseif ($this->getLocation()->getZ() === $oldZ) {
                $this->move(0, $this->getJumpVelocity() + $this->addVelocity, 0);
            }


            $this->checkJump($dx, $dz);


            $this->run = true;
            $this->idle = false;
        } else {
            $this->run = false;
            $this->idle = true;
        }
        return $update ?? parent::onUpdate($currentTick);
    }

    public function sendLightning(Position $position, Player $player): void {
        $pk = new AddActorPacket();
        $pk->type = "minecraft:lightning_bolt";
        $pk->actorUniqueId = 1001001;
        $pk->actorRuntimeId = 1001001;
        $pk->syncedProperties = new PropertySyncData([], []);
        $pk->metadata = [];
        $pk->motion = null;
        $pk->yaw = 0;
        $pk->pitch = 0;
        $pk->position = new Vector3($position->getX(), $position->getY(), $position->getZ());
        $player->getNetworkSession()->sendDataPacket($pk);

        $sound = new PlaySoundPacket();
        $sound->soundName = "ambient.weather.thunder";
        $sound->x = $this->getPosition()->getX();
        $sound->y = $this->getPosition()->getY();
        $sound->z = $this->getPosition()->getZ();
        $sound->volume = 100;
        $sound->pitch = 1000;
        $player->getNetworkSession()->sendDataPacket($sound);
    }
}