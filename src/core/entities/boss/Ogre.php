<?php

/*
 *   __  __       _     _____  _             _
 *  |  \/  |     | |   |  __ \| |           (_)
 *  | \  / | ___ | |__ | |__) | |_   _  __ _ _ _ __
 *  | |\/| |/ _ \| '_ \|  ___/| | | | |/ _` | | '_ \
 *  | |  | | (_) | |_) | |    | | |_| | (_| | | | | |
 *  |_|  |_|\___/|_.__/|_|    |_|\__,_|\__, |_|_| |_|
 *                                      __/ |
 *                                     |___/
 *
 * A PocketMine-MP plugin that implements mobs AI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 *
 * @author IvanCraft623
 */

declare(strict_types=1);

namespace core\entities\boss;

use core\player\CustomPlayer;
use core\utils\Utils;
use IvanCraft623\MobPlugin\entity\AgeableMob;
use IvanCraft623\MobPlugin\entity\ai\goal\BreedGoal;
use IvanCraft623\MobPlugin\entity\ai\goal\FloatGoal;
use IvanCraft623\MobPlugin\entity\ai\goal\FollowParentGoal;
use IvanCraft623\MobPlugin\entity\ai\goal\LookAtEntityGoal;
use IvanCraft623\MobPlugin\entity\ai\goal\MeleeAttackGoal;
use IvanCraft623\MobPlugin\entity\ai\goal\PanicGoal;
use IvanCraft623\MobPlugin\entity\ai\goal\RandomLookAroundGoal;
use IvanCraft623\MobPlugin\entity\ai\goal\target\NearestAttackableGoal;
use IvanCraft623\MobPlugin\entity\ai\goal\TemptGoal;
use IvanCraft623\MobPlugin\entity\ai\goal\WaterAvoidingRandomStrollGoal;
use IvanCraft623\MobPlugin\entity\ItemSteerable;
use IvanCraft623\MobPlugin\entity\monster\Enemy;
use IvanCraft623\MobPlugin\entity\monster\Monster;
use IvanCraft623\MobPlugin\entity\Saddleable;
use IvanCraft623\MobPlugin\utils\ItemSet;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AnimateEntityPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\player\Player;
use function mt_rand;

class Ogre extends Monster implements Enemy {


    public static function getNetworkTypeId() : string{ return "darkage:ice_golem"; }

    protected bool $saddled = false;

    protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(4, 0.5);
    }

    public function getName() : string{
        return "Ogre des neiges";
    }

    protected function registerGoals() : void{
        $this->goalSelector->addGoal(3, new FloatGoal($this));
        $this->goalSelector->addGoal(1, new NearestAttackableGoal($this, Player::class, 60));

        $attackGoal = new MeleeAttackGoal($this, 1, true, 10);
        $attackGoal->setAttackAnimation("animation.ice_golem.attack2");
        $attackGoal->setAttackDelay(12);

        $this->goalSelector->addGoal(0, $attackGoal);
    }

    private int $ticksUpdate = 0;

    public function onUpdate(int $currentTick): bool
    {
        $this->ticksUpdate++;
        return parent::onUpdate($currentTick);
    }



    protected function initProperties() : void{
        parent::initProperties();

        $this->setMaxHealth(1000);
    }


    protected function initEntity(CompoundTag $nbt): void
    {
        $this->setScale(2.5);
        parent::initEntity($nbt);
    }

    public function getAttackDamage(): float
    {
        return 38.0;
    }

    public function getDefaultMovementSpeed() : float{
        return 0.30;
    }

    public function boost() : bool{
        return false;
    }

    public function onLightningBoltHit() : bool{
        return parent::onLightningBoltHit();
    }
}
