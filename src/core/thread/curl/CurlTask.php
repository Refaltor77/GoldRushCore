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
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\InternetRequestResult;
use pocketmine\utils\Utils;
use function igbinary_serialize;
use function igbinary_unserialize;

abstract class CurlTask extends AsyncTask
{

	protected string $page;

	protected int $timeout;

	protected string $headers;

	public function __construct(string $page, int $timeout, array $headers, Closure $closure = null)
	{
		$this->page = $page;
		$this->timeout = $timeout;

		$serialized_headers = igbinary_serialize($headers);
		if ($serialized_headers === null) {
			throw new InvalidArgumentException("Headers cannot be serialized");
		}
		$this->headers = $serialized_headers;

		if ($closure !== null) {
			Utils::validateCallableSignature(function (?InternetRequestResult $result) : void {}, $closure);
			$this->storeLocal('closure', $closure);
		}
	}

	public function getHeaders() : array
	{
		/** @var array $headers */
		$headers = igbinary_unserialize($this->headers);

		return $headers;
	}

	public function onCompletion() : void
	{
		try {
			/** @var Closure $closure */
			$closure = $this->fetchLocal('closure');
		} catch (InvalidArgumentException $exception) {
			return;
		}

		if ($closure !== null) {
			$closure($this->getResult());
		}
	}
}
