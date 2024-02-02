<?php

namespace core\traits;

trait HomeTrait
{
    public function getIpFromID(string $XuidServerInterServer): string
    {
        switch ($XuidServerInterServer) {
            default:
            case 'XUID-GOLDRUSH-SERVER-GAME1':
                return "goldrushmc.fun:19132";
            case 'XUID-GOLDRUSH-SERVER-GAME2':
                return "goldrushmc.fun:19133";
        }
    }
}