<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class Vanish extends Executor
{
    public static array $inVanish = [];


    public function __construct(string $name = 'vanish', string $description = "Dvenir parfaitement invisible", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('vanish.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!in_array($sender->getXuid(), self::$inVanish)) {
            $sender->sendMessage("§c[§fSTAFF§c] §fVanish §aon");
            Server::getInstance()->broadcastMessage("§7[§c-§7] " . $sender->getName());
            self::$inVanish[] = $sender->getXuid();
        } else {
            foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                $onlinePlayer->showPlayer($sender);
            }

            foreach(Server::getInstance()->getOnlinePlayers() as $p) {
                $networkSession = $p->getNetworkSession();
                $networkSession->sendDataPacket(
                    PlayerListPacket::add([
                        PlayerListEntry::createAdditionEntry(
                            $sender->getUniqueId(),
                            $sender->getId(),
                            $sender->getDisplayName(),
                            $networkSession->getTypeConverter()->getSkinAdapter()->toSkinData($sender->getSkin()),
                            $sender->getXuid()
                        )]));
            }

            Server::getInstance()->broadcastMessage("§7[§a+§7] " . $sender->getName());
            $sender->setSilent(false);
            $sender->getXpManager()->setCanAttractXpOrbs(true);
            $sender->sendMessage("§c[§fSTAFF§c] §fVanish §coff");
            unset(self::$inVanish[array_search($sender->getXuid(), self::$inVanish)]);
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}