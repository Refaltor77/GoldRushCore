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

use Closure;
use InvalidArgumentException;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;

class Curl
{

	private static bool $registered = false;

	private static CurlThreadPool $threadPool;

	public static function register(PluginBase $plugin) : void
	{
		if (self::isRegistered()) {
			throw new InvalidArgumentException("{$plugin->getName()} attempted to register " . self::class . " twice.");
		}

		$server = $plugin->getServer();
		self::$threadPool = new CurlThreadPool(CurlThreadPool::POOL_SIZE, CurlThreadPool::MEMORY_LIMIT, $server->getLoader(), $server->getLogger(), $server->getTickSleeper());

		$plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () : void {
			self::$threadPool->collectTasks();
		}), CurlThreadPool::COLLECT_INTERVAL);
		$plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () : void {
			self::$threadPool->triggerGarbageCollector();
		}), CurlThreadPool::GARBAGE_COLLECT_INTERVAL);

		self::$registered = true;
	}

	public static function isRegistered() : bool
	{
		return self::$registered;
	}

	public static function postRequest(string $page, array|string $args, int $timeout = 10, array $headers = [], Closure $closure = null) : void
	{
		self::$threadPool->submitTask(new CurlPostTask($page, $args, $timeout, $headers, $closure));
	}

	public static function putRequest(string $page, array|string $args, int $timeout = 10, array $headers = [], Closure $closure = null) : void
	{
		self::$threadPool->submitTask(new CurlPutTask($page, $args, $timeout, $headers, $closure));
	}

	public static function deleteRequest(string $page, array|string $args, int $timeout = 10, array $headers = [], Closure $closure = null) : void
	{
		self::$threadPool->submitTask(new CurlDeleteTask($page, $args, $timeout, $headers, $closure));
	}

	public static function getRequest(string $page, int $timeout = 10, array $headers = [], Closure $closure = null) : void
	{
		self::$threadPool->submitTask(new CurlGetTask($page, $timeout, $headers, $closure));
	}
}
