<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\utils\Utils;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class Staff extends Executor
{
    public static array $inVanish = [];


    public function __construct(string $name = 'staff', string $description = "staff mode", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('staff.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (Main::getInstance()->getStaffManager()->isInStaffMode($sender)) {
            $sender->sendMessage(Messages::message("§fVous n'etes plus en staff mode."));
            Main::getInstance()->getStaffManager()->removeStaffMode($sender);


            if (in_array($sender->getXuid(), Vanish::$inVanish)) {
                if (!$sender->isOp()) {
                    unset(Vanish::$inVanish[array_search($sender->getXuid(), Vanish::$inVanish)]);
                    Server::getInstance()->broadcastMessage("§7[§a+§7] " . $sender->getName());
                    $sender->setSilent(false);
                    $sender->getXpManager()->setCanAttractXpOrbs(true);
                    $sender->sendMessage("§c[§fSTAFF§c] §fVanish §coff");


                    foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                        $onlinePlayer->showPlayer($sender);
                    }

                    foreach (Server::getInstance()->getOnlinePlayers() as $p) {
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
                }
            }

        } else {
            $sender->sendMessage(Messages::message("§fVous êtes en staff mode"));


            Main::getInstance()->getInventoryManager()->saveInventory($sender);
            Utils::timeout(function () use ($sender) : void {
                if (!$sender->isConnected()) return;
                $sender->getInventory()->clearAll();
                $sender->getArmorInventory()->clearAll();
                $sender->getOffHandInventory()->clearAll();
                $sender->getEnderInventory()->clearAll();
                Main::getInstance()->getStaffManager()->setStaffMode($sender);
            }, 20);
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}