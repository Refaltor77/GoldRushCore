<?php

/*
 *
 *  ____           _            __           _____
 * |  _ \    ___  (_)  _ __    / _|  _   _  |_   _|   ___    __ _   _ __ ___
 * | |_) |  / _ \ | | | '_ \  | |_  | | | |   | |    / _ \  / _` | | '_ ` _ \
 * |  _ <  |  __/ | | | | | | |  _| | |_| |   | |   |  __/ | (_| | | | | | | |
 * |_| \_\  \___| |_| |_| |_| |_|    \__, |   |_|    \___|  \__,_| |_| |_| |_|
 *                                   |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ReinfyTeam
 * @link https://github.com/ReinfyTeam/
 *
 *
 */

declare(strict_types=1);

namespace core\thread\curl;

use pocketmine\scheduler\AsyncPool;
use pocketmine\scheduler\DumpWorkerMemoryTask;
use pocketmine\scheduler\GarbageCollectionTask;
use function gc_collect_cycles;

class CurlThreadPool extends AsyncPool
{
	public const MEMORY_LIMIT = 256; // 256MB Limit
	public const POOL_SIZE = 2; // 2 workers
	public const COLLECT_INTERVAL = 1; // 1 tick
	public const GARBAGE_COLLECT_INTERVAL = 15 * 60 * 20; // 15 minutes

	/**
	 * Dumps the server memory into the specified output folder.
	 */
	public function dumpMemory(string $outputFolder, int $maxNesting, int $maxStringSize) : void
	{
		foreach ($this->getRunningWorkers() as $i) {
			$this->submitTaskToWorker(new DumpWorkerMemoryTask($outputFolder, $maxNesting, $maxStringSize), $i);
		}
	}

	public function triggerGarbageCollector() : int
	{
		$this->shutdownUnusedWorkers();

		foreach ($this->getRunningWorkers() as $i) {
			$this->submitTaskToWorker(new GarbageCollectionTask(), $i);
		}

		return gc_collect_cycles();
	}
}
