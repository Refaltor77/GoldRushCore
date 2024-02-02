<?php

namespace core\commands\executors\staff;

use core\api\gui\ChestInventory;
use core\api\gui\DoubleChestInventory;
use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class EnderChestView extends Executor
{
    public function __construct(string $name = 'seeec', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('ender_chest.view');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§cVous devez séléctionner un joueur."));
            $sender->sendErrorSound();
            return;
        }

        $name = $args[0];
        $player = Server::getInstance()->getPlayerByPrefix($name);
        if (!$player instanceof CustomPlayer) {
            $xuid = Main::getInstance()->getDataManager()->getXuidByName($args[0]);
            if ($xuid === null) {
                $sender->sendMessage(Messages::message("§cLe joueur n'est pas en ligne"));
                return;
            }

            Main::getInstance()->getInventoryManager()->checkingDatabasePlayerXuid($xuid, function (array $invContent, array $armorInv, array $offHand, array $ecInv) use ($sender, $args) : void {
                if (!$sender->isConnected()) return;
                $inv = new ChestInventory();
                $inv->setViewOnly(true);
                $inv->setName("EC : " . $args[0]);
                $inv->setContents($ecInv);
                $inv->send($sender);
            });
            return;
        }




        $ec = $player->getEnderInventory()->getContents();
        $inv = new ChestInventory();
        $inv->setViewOnly(true);
        $inv->setName("EC");
        $inv->setContents($ec);
        $inv->send($sender);
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, "Liste des joueurs a", true, 'Joueurs a', Main::getInstance()->getDataManager()->getAllNameInDatabaseForArgs());
        return parent::loadOptions($player);
    }
}