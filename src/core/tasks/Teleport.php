<?php

namespace core\tasks;

use core\messages\Prefix;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\Position;

class Teleport extends Task
{
    use UtilsTrait;

    public $callable;
    private ?Player $player;
    private Position $pos;
    private int $time = 5;
    private array $cache = [];


    public function __construct(?Player $player, Position $position, ?callable $callable = null, public bool $minage = false)
    {
        $this->player = $player;
        $this->pos = $position;
        $this->callable = $callable;
    }

    public function onRun(): void
    {
        if (is_null($this->pos)) {
            if (!$this->getHandler()->isCancelled()) {
                $this->getHandler()->cancel();
                return;
            }
        }
        $call = $this->callable;
        if (!is_null($this->player)) {
            if (Server::getInstance()->isOp($this->player->getName()) && !$this->player->hasPlayer) {
                if ($this->minage) {
                    $this->player->transfer('goldrushmc.fun', 19133);
                } else {
                    $this->player->teleport($this->pos);
                }
                $this->getHandler()->cancel();
                return;
            }
            if ($this->player->isConnected()) {
                if ($this->time === 5) {
                    $this->player->sendPopup("§6- §eTéléportation dans §65§e seconde(§6s§e)§6 -");
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
                        if ($this->minage) {
                            $this->player->transfer("goldrushmc.fun", "19133");
                        } else {
                            if ($this->pos->getWorld()->isLoaded()) {
                                $this->player->teleport($this->pos);
                                $this->player->sendMessage(Prefix::PREFIX_GOOD . "§eTéléportation effectuée !");
                            } else {
                                $this->player->sendMessage(Prefix::PREFIX_GOOD . "§eLe monde n'est pas sécurisé.");
                            }
                        }
                        if (!empty($call)) {
                            $call($this->player, true);
                        }
                        $pk = new PlaySoundPacket();
                        $pk->x = $this->cache['x'];
                        $pk->y = $this->cache['y'];
                        $pk->z = $this->cache['z'];
                        $pk->pitch = 0.80;
                        $pk->volume = 5;
                        $pk->soundName = 'note.harp';
                        $this->player->getNetworkSession()->sendDataPacket($pk);
                        if (!$this->getHandler()->isCancelled()) {
                            $this->getHandler()->cancel();
                        }
                    } else {
                        $this->player->sendMessage(Prefix::PREFIX_GOOD . "§cVous avez bougé, téléportation annulé.");
                        if (!empty($call)) {
                            $call($this->player, false);
                        }
                        $pk = new PlaySoundPacket();
                        $pk->x = $this->cache['x'];
                        $pk->y = $this->cache['y'];
                        $pk->z = $this->cache['z'];
                        $pk->pitch = 0.80;
                        $pk->volume = 5;
                        $pk->soundName = 'note.bass';
                        $this->player->getNetworkSession()->sendDataPacket($pk);
                        if (!$this->getHandler()->isCancelled()) {
                            $this->getHandler()->cancel();
                        }
                    }
                }
            } else if (!$this->getHandler()->isCancelled()) $this->getHandler()->cancel();
        } else if (!$this->getHandler()->isCancelled()) $this->getHandler()->cancel();
    }
}