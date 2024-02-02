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

namespace IvanCraft623\MobPlugin\entity;

use pocketmine\entity\Ageable;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\utils\Binary;
use function min;
use function mt_rand;

abstract class AgeableMob extends PathfinderMob implements Ageable {

	private const TAG_AGE = "Age"; //TAG_Int

	public const STARTING_BABY_AGE = -24000;
	public const ADULT_AGE = 0;

	protected int $age = self::ADULT_AGE;

	public static function getRandomStartAge() : int{
		if (mt_rand(1, 19) === 1) { //5% baby chance
			return self::STARTING_BABY_AGE;
		}
		return self::ADULT_AGE;
	}

	public static function getAgeUpWhenFeeding(int $currentAge) : int {
		if ($currentAge < self::ADULT_AGE) {
			return (int) min(-(self::STARTING_BABY_AGE / 10), -$currentAge);
		}
		return 0;
	}

	public abstract function getBreedOffspring(AgeableMob $partner) : AgeableMob;

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->setAge($nbt->getInt(self::TAG_AGE, static::getRandomStartAge()));
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setInt(self::TAG_AGE, Binary::signInt($this->getAge()));

		return $nbt;
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);
		$properties->setGenericFlag(EntityMetadataFlags::BABY, $this->isBaby());
	}

	public function canBreed() : bool {
		return false;
	}

	public function getAge() : int {
		return $this->age;
	}

	public function setAge(int $age) : void{
		$currentAge = $this->getAge();
		$this->age = $age;

		$nowIsBaby = $currentAge >= self::ADULT_AGE && $age < self::ADULT_AGE;
		if ($nowIsBaby || ($currentAge < self::ADULT_AGE && $age >= self::ADULT_AGE)) {
			$this->reachedAgeBoundary();
			$this->setScale($nowIsBaby ? $this->getBabyScale() : 1);

			$this->networkPropertiesDirty = true;
		}
	}

	public function ageUp(int $ageAmount) : void{
		$currentAge = $this->getAge();
		$currentAge += $ageAmount;

		if ($currentAge > self::ADULT_AGE) {
			$currentAge = self::ADULT_AGE;
		}
		$this->setAge($currentAge);
	}

	public function tickAi() : void{
		parent::tickAi();

		if ($this->isAlive()) {
			$currentAge = $this->getAge();
			if ($currentAge < self::ADULT_AGE) {
				$this->setAge(++$currentAge);
			} elseif ($currentAge > self::ADULT_AGE) {
				$this->setAge(--$currentAge);
			}
		}
	}

	public function reachedAgeBoundary() : void{
		//TODO: check if it is mounting something and leave the vehicle if it can no longer ride it.
	}

	public function isBaby() : bool{
		return $this->getAge() < self::ADULT_AGE;
	}

	public function setBaby(bool $value = true) : void{
		$this->setAge($value ? self::STARTING_BABY_AGE : self::ADULT_AGE);
	}

	public function getBabyScale() : float{
		return 0.5;
	}

	//TODO: natural spawning logic
}
