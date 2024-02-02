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

namespace IvanCraft623\MobPlugin\entity\ai\behavior;

use IvanCraft623\MobPlugin\entity\ai\memory\MemoryModuleType;
use IvanCraft623\MobPlugin\entity\Living;
use pocketmine\entity\Entity;
use pocketmine\entity\Living as PMLiving;
use pocketmine\math\Vector3;
use function is_array;

class EntityTracker implements PosTracker {

	protected Entity $target;

	protected bool $trackEyeHeight;

	public function __construct(Entity $target, bool $trackEyeHeight) {
		$this->target = $target;
		$this->trackEyeHeight = $trackEyeHeight;
	}

	public function getTarget() : Entity{
		return $this->target;
	}

	public function currentPosition() : Vector3{
		return $this->trackEyeHeight ? $this->target->getEyePos() : $this->target->getPosition();
	}

	public function currentBlockPosition() : Vector3{
		return $this->target->getPosition()->floor();
	}

	public function isVisibleBy(Living $entity) : bool{
		if ($this->target instanceof PMLiving) {
			if (!$this->target->isAlive()) {
				return false;
			}

			$visibleEntitiesMemory = $entity->getBrain()->getMemory(MemoryModuleType::VISIBLE_LIVING_ENTITIES());
			return is_array($visibleEntitiesMemory) && isset($visibleEntitiesMemory[$this->target->getId()]);
		}
		return true;
	}
}
