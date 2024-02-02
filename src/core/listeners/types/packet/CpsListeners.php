<?php

namespace core\listeners\types\packet;

use core\listeners\BaseEvent;
use core\Main;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\Player;
use pocketmine\Server;

class CpsListeners extends BaseEvent
{
    public $cps = [];
    private $cooldown = [];
    public static array $cpsEnabled = [];

    public function onDisconnect(PlayerQuitEvent $event){
        unset($this->cps[$event->getPlayer()->getName()]);
    }

    public function onConnect(PlayerJoinEvent $event){
        self::$cpsEnabled[$event->getPlayer()->getName()] = true;
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event){
        $packet = $event->getPacket();

        if($packet instanceof LevelSoundEventPacket){
            if($packet->sound === LevelSoundEvent::ATTACK_NODAMAGE){
                $this->addCPS($event->getOrigin()->getPlayer());
                if(self::$cpsEnabled[$event->getOrigin()->getPlayer()->getName()] === true && Main::getInstance()->getSettingsManager()->getSetting($event->getOrigin()->getPlayer(), "cps") === true){
                    $popup = str_replace("{cps}", $this->getCPS($event->getOrigin()->getPlayer()), "§f[§6CPS§f] §6{cps}");
                    $event->getOrigin()->getPlayer()->sendPopup($popup);
                }
                if($this->getCPS($event->getOrigin()->getPlayer()) >= 15){
                    $players = server::getInstance()->getOnlinePlayers();
                    if(!$this->hasCooldown($event->getOrigin()->getPlayer())){
                        $this->updateCooldown($event->getOrigin()->getPlayer());
                        foreach($players as $playerName){
                            $offender = $event->getOrigin()->getPlayer()->getName();

                        }
                    }
                }
            }
        }
        if($packet instanceof InventoryTransactionPacket){
            if($packet->trData instanceof UseItemOnEntityTransactionData){
                $this->addCPS($event->getOrigin()->getPlayer());
                if(isset(self::$cpsEnabled[$event->getOrigin()->getPlayer()->getName()]) && Main::getInstance()->getSettingsManager()->getSetting($event->getOrigin()->getPlayer(), "cps") === true){
                    $popup = str_replace("{cps}", $this->getCPS($event->getOrigin()->getPlayer()), "§f[§6CPS§f] §6{cps}");
                    $event->getOrigin()->getPlayer()->sendPopup($popup);
                }
                if($this->getCPS($event->getOrigin()->getPlayer()) >= 15){
                    $players = server::getInstance()->getOnlinePlayers();

                    if(!$this->hasCooldown($event->getOrigin()->getPlayer())){
                        $this->updateCooldown($event->getOrigin()->getPlayer());
                        foreach($players as $playerName){
                            $offender = $event->getOrigin()->getPlayer()->getName();
                        }
                    }
                }
            }
            if($this->getCPS($event->getOrigin()->getPlayer()) > 20){
                $event->cancel();
            }
        }
    }


    public function hasCooldown(Player $player): bool{
        return isset($this->cooldown[$player->getName()]) && $this->cooldown[$player->getName()] > time();
    }

    public function updateCooldown(Player $player): void{
        $this->cooldown[$player->getName()] = time() + 10;
    }

    public function addCPS(Player $player): void{
        $time = microtime(true);
        $this->cps[$player->getName()][] = $time;
    }

    public function getCPS(Player $player): int{
        $time = microtime(true);
        return count(array_filter($this->cps[$player->getName()] ?? [], static function(float $t) use ($time):bool{
            return ($time - $t) <= 1;
        }));
    }
}