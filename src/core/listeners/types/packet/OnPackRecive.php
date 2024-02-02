<?php

namespace core\listeners\types\packet;

use core\listeners\BaseEvent;
use core\Main;
use core\managers\cosmetic\skinsUtils\resetSkin;
use core\managers\cosmetic\skinsUtils\saveSkin;
use core\services\PackSendEntry;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkDataPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkRequestPacket;
use pocketmine\network\mcpe\protocol\ResourcePackClientResponsePacket;
use pocketmine\network\mcpe\protocol\ResourcePackDataInfoPacket;
use pocketmine\network\mcpe\protocol\types\login\ClientData;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackType;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\Player;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\Server;

class OnPackRecive extends BaseEvent
{


    /*
    public function dataReceiveEv(DataPacketReceiveEvent $ev)
    {
        $packet = $ev->getPacket();
        $player = $ev->getOrigin()->getPlayer();
        if ($packet instanceof LoginPacket) {
            $data = self::decodeClientData($packet->clientDataJwt);
            $name = $data->ThirdPartyName;
            if ($data->PersonaSkin) {
                if (!file_exists(Main::getInstance()->getDataFolder() . "saveskin")) {
                    mkdir(Main::getInstance()->getDataFolder() . "saveskin", 0777);
                }
                copy(Main::getInstance()->getDataFolder()."steve.png",Main::getInstance()->getDataFolder() . "saveskin/{$name}.png");
                return;
            }
            if ($data->SkinImageHeight == 32) {
            }
            $saveSkin = new saveSkin();
            $saveSkin->saveSkin(base64_decode($data->SkinData, true), $name);
        }
    }*/


    public function resetSkin(Player $player){
        $player->sendMessage("Reset To Original Skin Successfully");
        $reset = new resetSkin();
        $reset->setSkin($player);
    }



    public function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public static function decodeClientData(string $clientDataJwt): ClientData{
        try{
            [, $clientDataClaims, ] = JwtUtils::parse($clientDataJwt);
        }catch(JwtException $e){
            throw PacketHandlingException::wrap($e);
        }

        $mapper = new \JsonMapper;
        $mapper->bEnforceMapType = false;
        $mapper->bExceptionOnMissingData = true;
        $mapper->bExceptionOnUndefinedProperty = true;
        try{
            $clientData = $mapper->map($clientDataClaims, new ClientData);
        }catch(\JsonMapper_Exception $e){
            throw PacketHandlingException::wrap($e);
        }
        return $clientData;
    }



    public function onPacketReceive(DataPacketReceiveEvent $event) : void{
        $session = $event->getOrigin();
        $packet = $event->getPacket();
        if($packet instanceof ResourcePackClientResponsePacket){
            if($packet->status === ResourcePackClientResponsePacket::STATUS_SEND_PACKS){
                $event->cancel();

                $manager = Main::getInstance()->getServer()->getResourcePackManager();

                Main::$packSendQueue[$session->getDisplayName()] = $entry = new PackSendEntry($session);
                $entry->setSendInterval(Main::DEFAULT_PACKET_SEND_INTERVAL);

                foreach($packet->packIds as $uuid){
                    //dirty hack for mojang's dirty hack for versions
                    $splitPos = strpos($uuid, "_");
                    if($splitPos !== false){
                        $uuid = substr($uuid, 0, $splitPos);
                    }

                    $pack = $manager->getPackById($uuid);
                    if(!($pack instanceof ResourcePack)){
                        //Client requested a resource pack but we don't have it available on the server
                        //Main::getInstance()->getTpaService()->getLogger()->error("Error downloading resource packs: Unknown pack $uuid requested, available packs: " . implode(", ", $manager->getPackIdList()));
                        $session->disconnect(KnownTranslationKeys::DISCONNECTIONSCREEN_RESOURCEPACK);

                        return;
                    }

                    $pk = ResourcePackDataInfoPacket::create(
                        $pack->getPackId(),
                        Main::DEFAULT_CHUNK_SIZE,
                        (int) ceil($pack->getPackSize() / Main::DEFAULT_CHUNK_SIZE),
                        $pack->getPackSize(),
                        $pack->getSha256(),
                        false,
                        ResourcePackType::RESOURCES
                    );
                    $session->sendDataPacket($pk);

                    for($i = 0; $i < $pk->chunkCount; $i++){
                        $pk2 = ResourcePackChunkDataPacket::create(
                            $pack->getPackId(),
                            $i,
                            $pk->maxChunkSize * $i,
                            $pack->getPackChunk($pk->maxChunkSize * $i, $pk->maxChunkSize)
                        );
                        $entry->addPacket($pk2);
                    }
                }
            }
        }elseif($packet instanceof ResourcePackChunkRequestPacket){
            $event->cancel(); // dont rely on client
        }
    }
}