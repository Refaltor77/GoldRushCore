<?php

namespace core\managers\homes;

use core\Main;
use core\player\CustomPlayer;
use core\traits\UtilsTrait;
use pocketmine\world\Position;

class Home
{
    use UtilsTrait;

    public function __construct(
        private CustomPlayer $player,
        private Position $position,
        private string $name
    )
    {}

    # args callback: player
    public function save(?callable $callback): void {
        Main::getInstance()->getHomeManager()->createHome($this->player, $this->position, $this->name, $callback);
    }
}