<?php

namespace core\tasks;

use core\listeners\types\events\KothListeners;
use core\Main;
use core\managers\factions\FactionManager;
use core\messages\Messages;
use core\traits\UtilsTrait;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\Position;

class KothScheduler extends Task
{
    public static bool $hasKoth = false;
    public static array $dominant = ['xuid' => 'null', 'i' => 0, 'hasInterupt' => false];

    use UtilsTrait;

    private int $time;

    public function __construct(int $time = 3000)
    {
        $this->time = $time;
        $msg = "§6§l---\n";
        $msg .= "§r§fUn KOTH a débuté §6!\n";
        $msg .= "§r§7Description §8: §fSoyez le premier à accumuler\n";
        $msg .= "le plus de points en maintenant votre position\n";
        $msg .= "dans la zone à conquérir !\n";
        $msg .= "\n/koth\n";
        $msg .= "§6§l---\n";
        Server::getInstance()->broadcastMessage($msg);
        self::$hasKoth = true;
    }

    public function onRun(): void
    {
        $hasUpdate = false;

        if (count(KothListeners::$isInAreaKoth) == 0) {
            if (self::$dominant['i'] > 0) self::$dominant['i']--;
        }

        foreach (KothListeners::$isInAreaKoth as $xuid => $bool) {
            if (count(KothListeners::$isInAreaKoth) === 1) {
                self::$dominant['hasInterupt'] = false;
                $values = self::$dominant;
                if ($values['xuid'] !== $xuid) {
                    self::$dominant['xuid'] = $xuid;
                } else {
                    self::$dominant['xuid'] = $xuid;
                    $hasUpdate = true;
                    if (self::$dominant['i'] >= 100) {
                        self::$hasKoth = false;
                    }
                }
            }elseif (count(KothListeners::$isInAreaKoth) == 0) {
                if (self::$dominant['i'] > 0) self::$dominant['i']--;
            } else {
                if (self::$dominant['xuid'] === '') {
                    self::$dominant['xuid'] = $xuid;
                    $hasUpdate = true;
                    if (self::$dominant['i'] >= 100) {
                        self::$hasKoth = false;
                    }
                } else {
                    $hasUpdate = true;
                    if (self::$dominant['i'] >= 100) {
                        self::$hasKoth = false;
                    }
                }
            }
        }
        if ($hasUpdate) self::$dominant['i']++;

        if (self::$hasKoth) {
            switch ($this->time) {
                case 300:
                    foreach (KothListeners::$participateEvent as $xuid => $value) {
                        $player = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                        if (!is_null($player)) {
                            $player->sendPopup("§6- §fKOTH /!\ §e5§fminute§es§c restante§es§f ! §6-");
                        }
                    }
                    break;
                case 120:
                    foreach (KothListeners::$participateEvent as $xuid => $value) {
                        $player = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                        if (!is_null($player)) {
                            $player->sendPopup("§6- §fKOTH /!\ §e2§fminute§es§c restante§es§f ! §6-");
                        }
                    }
                    break;
                case 30:
                    foreach (KothListeners::$participateEvent as $xuid => $value) {
                        $player = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                        if (!is_null($player)) {
                            $player->sendPopup("§6- §fKOTH /!\ §e30§fsecond§es§c restante§es§f ! §6-");
                        }
                    }
                    break;
                case 5:
                case 4:
                case 3:
                case 2:
                case 1:
                    foreach (KothListeners::$participateEvent as $xuid => $value) {
                        $player = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                        if (!is_null($player)) {
                            $player->sendPopup("§6- §fKOTH /!\ §e{$this->time}§fsecond§es§c restante§es§f ! §6-");
                        }
                    }
                    break;
                case 0:
                    $winner = self::$dominant['xuid'];
                    $player = Main::getInstance()->getDataManager()->getPlayerXuid($winner);
                    if (!is_null($player)) {
                        if ($this->getManagerFaction()->isInFaction($winner)) {
                            $factionName = $this->getManagerFaction()->getFactionName($winner);
                            Server::getInstance()->broadcastMessage(Messages::message("§aLa faction §f{$factionName}§a remporte le KothEvents !"));
                            $player->sendMessage(Messages::message("§aVotre faction remporte le KothEvents !"));
                            $this->getManagerFaction()->addPower($factionName, mt_rand(20, 40), $winner);
                        } else {
                            $player->sendMessage(Messages::message("§aVous avez gagné le KothEvents !"));
                            Server::getInstance()->broadcastMessage(Messages::message("§aLe joueur §f{$player->getName()}§a remporte le KothEvents !"));
                            Main::getInstance()->getEconomyManager()->addMoney($player, mt_rand(500, 1500));
                        }
                    }
                    KothListeners::$participateEvent = [];
                    KothListeners::$isInAreaKoth = [];
                    self::$hasKoth = false;
                    if (!$this->getHandler()->isCancelled()) $this->getHandler()->cancel();
                    break;
            }
        } else {
            $winner = self::$dominant['xuid'];
            $player = Main::getInstance()->getDataManager()->getPlayerXuid($winner);
            if (!is_null($player)) {
                if ($this->getManagerFaction()->isInFaction($winner)) {
                    $factionName = $this->getManagerFaction()->getFactionName($winner);
                    Server::getInstance()->broadcastMessage(Messages::message("§aLa faction §f{$factionName}§a remporte le KothEvents !"));
                    $player->sendMessage(Messages::message("§aVotre faction remporte le KothEvents !"));
                    $this->getManagerFaction()->addPowerPlayerXuid($factionName, mt_rand(20, 40), $winner);
                } else {
                    $player->sendMessage(Messages::message("§aVous avez gagné le KothEvents !"));
                    Server::getInstance()->broadcastMessage(Messages::message("§aLe joueur §f{$player->getName()}§a remporte le KothEvents !"));
                    Main::getInstance()->getEconomyManager()->addMoney($player, mt_rand(5000, 10000));
                }
            }
            KothListeners::$participateEvent = [];
            KothListeners::$isInAreaKoth = [];
            self::$hasKoth = false;
            self::$dominant = ['xuid' => 'null', 'i' => 0, 'hasInterupt' => false];
            if (!$this->getHandler()->isCancelled()) $this->getHandler()->cancel();
        }
        $this->time--;
    }


    public function getManagerFaction(): FactionManager
    {
        return Main::getInstance()->getFactionManager();
    }
}