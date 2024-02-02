<?php

namespace core\formatters;

use pocketmine\lang\Translatable;
use pocketmine\player\chat\ChatFormatter;
use pocketmine\utils\TextFormat;

class BasicChatFormatter implements ChatFormatter
{
    public function __construct(public string $faction, public string $rank, public string $format)
    {

    }

    public function format(string $username, string $message): Translatable|string
    {
        return str_replace(['{faction}', '{pseudo}', '{msg}'], [$this->faction, $username, TextFormat::clean($message)], $this->format);
    }
}