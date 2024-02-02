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
use pocketmine\utils\Internet;
use function is_array;
use function json_encode;

class CurlPostTask extends CurlTask
{

	protected string $args;

	public function __construct(string $page, array|string $args, int $timeout, array $headers, Closure $closure = null)
	{
		if (is_array($args)) {
			$this->args = json_encode($args, JSON_THROW_ON_ERROR);
		} else {
			$this->args = $args;
		}

		parent::__construct($page, $timeout, $headers, $closure);
	}

	public function onRun() : void
	{
		$this->setResult(Internet::postURL($this->page, $this->args, $this->timeout, $this->getHeaders()));
	}
}
