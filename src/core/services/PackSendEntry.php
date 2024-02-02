<?php

namespace core\services;

use core\Main;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientboundPacket;

final class PackSendEntry{
    /** @var ClientboundPacket[] */
    protected array $packets = [];
    protected int $sendInterval = 30;

    public function __construct(protected NetworkSession $session){}

    public function addPacket(ClientboundPacket $packet) : void{
        $this->packets[] = $packet;
    }

    public function setSendInterval(int $value) : void{
        $this->sendInterval = $value;
    }

    public function tick(int $tick) : void{
        if(!$this->session->isConnected()){
            unset(Main::$packSendQueue[$this->session->getDisplayName()]);
            return;
        }

        if(($tick % $this->sendInterval) === 0){
            if(($next = array_shift($this->packets)) !== null){
                $this->session->sendDataPacket($next);
            }else{
                unset(Main::$packSendQueue[$this->session->getDisplayName()]);
            }
        }
    }
}