<?php

namespace core\traits;

trait FactionTrait
{
    private array $factionChat = [];

    public function isFactionChat(string $playerName): bool
    {
        return isset($this->factionChat[$playerName]);
    }

    public function setFactionChat(string $playerName, bool $factionChat): void
    {
        if ($factionChat) {
            $this->factionChat[$playerName] = true;
        } else {
            unset($this->factionChat[$playerName]);
        }
    }
}