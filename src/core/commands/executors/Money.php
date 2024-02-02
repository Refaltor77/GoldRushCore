<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Prefix;
use core\player\CustomPlayer;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;
use pocketmine\Server;

class Money extends Executor
{
    use UtilsTrait;

    public function __construct(string $name = 'money', string $description = "Voir votre argent", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(Prefix::PREFIX_GOOD . "§cCommande exécutable uniquement sur le serveur.");
            return;
        }
        if (isset($args[0])) {
            $player = Server::getInstance()->getPlayerByPrefix(strval($args[0]));
            if (!is_null($player)) {
                Main::getInstance()->getEconomyManager()->getMoneySQL($player, function (Player $player, int $money) use ($sender) : void {
                    if ($sender->isConnected()) {
                        $sender->sendMessage(Prefix::PREFIX_GOOD . "§f" . $player->getName() . " possède §e" . $money . "§r§6$");
                    }
                });
            } else {
                $xuid = Main::getInstance()->getDataManager()->getXuidByName(strval($args[0]));
                if (!is_null($xuid)) {
                    Main::getInstance()->getEconomyManager()->getMoneySQLXuid($xuid, function (int $money) use ($sender, $args): void {
                        $sender->sendMessage(Prefix::PREFIX_GOOD . "§f" . strval($args[0]) . " possède §e" . $money . "§r§6$");
                    });
                } else $sender->sendMessage(Prefix::PREFIX_GOOD . "§cLe joueur n'existe pas !");
            }
        } else {
            Main::getInstance()->getEconomyManager()->getMoneySQL($sender, function (Player $sender, int $money): void {
                $sender->sendMessage(Prefix::PREFIX_GOOD . "§fTu as §e" . $money . "§r§6$");
            });
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'Liste des joueurs', true, 'Joueurs', $this->getAllPlayersArrayForArgs());
        return parent::loadOptions($player);
    }
}