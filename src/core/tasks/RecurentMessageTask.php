<?php

namespace core\tasks;

use pocketmine\scheduler\Task;
use pocketmine\Server;

class RecurentMessageTask extends Task
{

    public int $seconds = 0;


    public function onRun(): void
    {
        if ($this->seconds === 60) {
            $msg = "§6[§fALERT§6]\n";
            $msg .= "§fN'oubliez pas de voter sur §6notre site §f\npour obtenir des récompenses : §6goldrushmc.fun/vote";
            Server::getInstance()->broadcastMessage($msg);
        }

        if ($this->seconds === 60 * 5) {
            $msg = "§6[§fALERT§6]\n";
            $msg .= "§fRejoignez notre §6serveur Discord\n§fsur §6goldrushmc.fun/discord §fpour discuter\navec la communauté.";
            Server::getInstance()->broadcastMessage($msg);
        }

        if ($this->seconds === 60 * 10) {
            $msg = "§6[§fALERT§6]\n";
            $msg .= "§fSuivez-nous sur TikTok : §6goldrushmc.fun/tiktok\n§fpour du contenu exclusif.";
            Server::getInstance()->broadcastMessage($msg);
            $this->seconds = 0;
        }

        $this->seconds++;
    }
}