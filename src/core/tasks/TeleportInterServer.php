<?php

namespace core\tasks;

use core\Main;
use core\managers\homes\HomeManager;
use core\messages\Messages;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class TeleportInterServer extends Task
{
    private Player $player;
    private string $informationTeleportationOtherServer;
    private int $time;
    private array $cache;
    private string $ipv4AndPort;
    private Main $plugin;

    use UtilsTrait;


    public function __construct(Player $player, string $tpInfo, string $address)
    {
        $this->plugin = Main::getInstance();
        $this->player = $player;
        $this->informationTeleportationOtherServer = $tpInfo;
        $this->time = 5;
        $this->cache = [];
        $this->ipv4AndPort = $address;
    }

    public function onRun(): void
    {
        if (!is_null($player = $this->player)) {
            if (Server::getInstance()->isOp($this->player->getName())) {
                $parse = explode(':', $this->ipv4AndPort);
                $player->transfer($parse[0], intval($parse[1]), 'Transfer Home');
                $this->getHandler()->cancel();
                return;
            }
            if ($this->player->isConnected()) {
                if ($this->time === 5) {
                    $this->player->sendPopup("§6- §eTéléportation dans §65§e seconde(§6s§e)§6 -");
                    HomeManager::$cacheFastInterServer[$player->getXuid()] = $this->informationTeleportationOtherServer;
                    $this->cache['x'] = intval($this->player->getPosition()->getX());
                    $this->cache['y'] = intval($this->player->getPosition()->getY());
                    $this->cache['z'] = intval($this->player->getPosition()->getZ());
                    $this->time--;
                } elseif ($this->time > 0 &&
                    intval($this->player->getPosition()->getX()) === $this->cache['x'] &&
                    intval($this->player->getPosition()->getY()) === $this->cache['y'] &&
                    intval($this->player->getPosition()->getZ()) === $this->cache['z']
                ) {
                    $pk = new PlaySoundPacket();
                    $pk->x = $this->cache['x'];
                    $pk->y = $this->cache['y'];
                    $pk->z = $this->cache['z'];
                    $pk->pitch = 0.80;
                    $pk->volume = 5;
                    $pk->soundName = 'step.amethyst_block';
                    $this->player->getNetworkSession()->sendDataPacket($pk);
                    $this->player->sendPopup("§6- §eTéléportation dans §6{$this->time}§e seconde(§6s§e)§6 -");
                    $this->time--;
                } else {
                    if ($this->time <= 0) {
                        $pk = new PlaySoundPacket();
                        $pk->x = $this->cache['x'];
                        $pk->y = $this->cache['y'];
                        $pk->z = $this->cache['z'];
                        $pk->pitch = 0.80;
                        $pk->volume = 5;
                        $pk->soundName = 'note.harp';
                        $this->player->getNetworkSession()->sendDataPacket($pk);
                        $parse = explode(':', $this->ipv4AndPort);
                        $player->transfer($parse[0], intval($parse[1]), 'Transfer Home');
                        //Main::getInstance()->threadManager->addLogQueue("TP INTER SERVER: " , $this->player->getName() . " -> " . $this->ipv4AndPort);
                        if (!$this->getHandler()->isCancelled()) $this->getHandler()->cancel();
                    } else {
                        unset(HomeManager::$cacheFastInterServer[$player->getXuid()]);
                        $this->player->sendMessage(Messages::message("§cVous avez bougé, téléportation annulé."));
                        $pk = new PlaySoundPacket();
                        $pk->x = $this->cache['x'];
                        $pk->y = $this->cache['y'];
                        $pk->z = $this->cache['z'];
                        $pk->pitch = 0.80;
                        $pk->volume = 5;
                        $pk->soundName = 'note.bass';
                        $this->player->getNetworkSession()->sendDataPacket($pk);
                        if (!$this->getHandler()->isCancelled()) $this->getHandler()->cancel();
                    }
                }
            } else if (!$this->getHandler()->isCancelled()) $this->getHandler()->cancel();
        } else if (!$this->getHandler()->isCancelled()) $this->getHandler()->cancel();
    }
}