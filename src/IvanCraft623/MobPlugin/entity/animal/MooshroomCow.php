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

namespace IvanCraft623\MobPlugin\entity\animal;

use IvanCraft623\MobPlugin\data\bedrock\MooshroomCowTypeIdMap;
use IvanCraft623\MobPlugin\entity\AgeableMob;
use IvanCraft623\MobPlugin\entity\animal\utils\SuspiciousStewTypeFlowerMap;
use IvanCraft623\MobPlugin\entity\animation\ConsumingItemAnimation;
use IvanCraft623\MobPlugin\entity\Shearable;
use IvanCraft623\MobPlugin\sound\MilkSuspiciouslySound;
use IvanCraft623\MobPlugin\sound\MooshroomCowConvertSound;
use IvanCraft623\MobPlugin\sound\ShearSound;
use IvanCraft623\MobPlugin\utils\Utils;

use pocketmine\data\bedrock\SuspiciousStewTypeIdMap;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\SuspiciousStewType;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\particle\SmokeParticle;
use function lcg_value;
use function mt_rand;

class MooshroomCow extends Cow implements Shearable{

	private const TAG_TYPE = "Variant"; //TAG_Int
	private const TAG_SUSPICIOUS_STEW_TYPE = "MarkVariant"; //TAG_Int

	public const OFFSPRING_MUTATE_CHANCE = 1024;

	public static function getNetworkTypeId() : string{ return EntityIds::MOOSHROOM; }

	public static function getOffspringType(MooshroomCow $parent1, MooshroomCow $parent2) : MooshroomCowType{
		$type1 = $parent1->getType();
		if (mt_rand(0, self::OFFSPRING_MUTATE_CHANCE) === 0) {
			return $type1->equals(MooshroomCowType::BROWN()) ? MooshroomCowType::RED() : MooshroomCowType::BROWN();
		}

		return mt_rand(0, 1) === 0 ? $type1 : $parent2->getType();
	}

	protected MooshroomCowType $mooshroomType;

	protected ?SuspiciousStewType $stewType = null;

	public function getName() : string{
		return "Mooshroom";
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->mooshroomType = MooshroomCowTypeIdMap::getInstance()->fromId($nbt->getInt(self::TAG_TYPE, -1)) ?? MooshroomCowType::RED();
		$this->stewType = SuspiciousStewTypeIdMap::getInstance()->fromId($nbt->getInt(self::TAG_SUSPICIOUS_STEW_TYPE, -1));
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setInt(self::TAG_TYPE, MooshroomCowTypeIdMap::getInstance()->toId($this->mooshroomType));

		$stewId = -1;
		if ($this->stewType !== null) {
			$stewId = SuspiciousStewTypeIdMap::getInstance()->toId($this->stewType);
		}
		$nbt->setInt(self::TAG_SUSPICIOUS_STEW_TYPE, $stewId);

		return $nbt;
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		$properties->setInt(EntityMetadataProperties::VARIANT, MooshroomCowTypeIdMap::getInstance()->toId($this->mooshroomType));
	}

	public function onInteract(Player $player, Vector3 $clickPos) : bool{
		$item = $player->getInventory()->getItemInHand();
		if ($item->getTypeId() === ItemTypeIds::SHEARS && $this->isReadyForShearing()) {
			$this->shear();
			Utils::damageItemInHand($player);

			return true;
		}

		if ($item->getTypeId() === ItemTypeIds::BOWL && !$this->isBaby()) {
			if ($this->stewType !== null) {
				$result = VanillaItems::SUSPICIOUS_STEW()->setType($this->stewType);

				$this->setSuspiciousStewType(null);
			} else{
				$result = VanillaItems::MUSHROOM_STEW();
			}

			$this->broadcastSound(new MilkSuspiciouslySound());

			Utils::transformItemInHand($player, $result);

			return true;
		}

		if (!$this->isBaby() &&
			$this->mooshroomType->equals(MooshroomCowType::BROWN()) &&
			($stewType = SuspiciousStewTypeFlowerMap::getInstance()->fromFlower($item->getBlock())) !== null &&
			($this->stewType === null || !$stewType->equals($this->stewType))
		) {
			$this->setSuspiciousStewType($stewType);

			$this->broadcastAnimation(new ConsumingItemAnimation($this, $item));

			$size = $this->getSize();
			$this->getWorld()->addParticle($this->location->add(
				lcg_value() / 2,
				$this->getSize()->getHeight(),
				lcg_value() / 2,
			), new SmokeParticle());

			Utils::popItemInHand($player);

			return true;
		}

		return parent::onInteract($player, $clickPos);
	}

	/** @return $this */
	public function setType(MooshroomCowType $type) : self{
		$this->mooshroomType = $type;
		$this->networkPropertiesDirty = true;

		return $this;
	}

	public function getType() : MooshroomCowType{
		return $this->mooshroomType;
	}

	/** @return $this */
	public function setSuspiciousStewType(?SuspiciousStewType $type) : self{
		$this->stewType = $type;

		return $this;
	}

	public function getSuspiciousStewType() : ?SuspiciousStewType{
		return $this->stewType;
	}

	public function shear() : void{
		$this->broadcastSound(new ShearSound());

		$world = $this->getWorld();
		$position = $this->location->add(0, $this->getSize()->getHeight(), 0);

		$world->addParticle($position, new HugeExplodeParticle());

		//TODO: pocketmine doesn't allow spread the items, they are merged immediately :(
		$world->dropItem($position, $this->mooshroomType->getMushroom()->asItem()->setCount(5));

		$this->setSheared();
	}

	public function setSheared(bool $sheared = true) : void{
		if ($sheared) {
			//Spawn a cow :P

			$cow = new Cow($this->location, $this->saveNBT());
			$cow->spawnToAll();

			$this->flagForDespawn();
		}
	}

	public function isSheared() : bool{
		return false;
	}

	public function isReadyForShearing() : bool{
		return $this->isAlive() && !$this->isBaby();
	}

	public function getBreedOffspring(AgeableMob $partner) : MooshroomCow{
		$offspring = new MooshroomCow($this->getLocation());

		/** @var MooshroomCow $partner */
		$offspring->setType(static::getOffspringType($this, $partner));

		return $offspring;
	}

	public function onLightningBoltHit() : bool{
		$this->setType($this->mooshroomType->equals(MooshroomCowType::RED()) ? MooshroomCowType::BROWN() : MooshroomCowType::RED());
		if ($this->mooshroomType->equals(MooshroomCowType::RED())) {
			$this->setSuspiciousStewType(null);
		}

		$this->broadcastSound(new MooshroomCowConvertSound());

		return true;
	}

	//TODO: natural spawning logic
}
