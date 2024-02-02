<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\events\LogEvent;
use core\Main;
use core\messages\Prefix;
use core\player\CustomPlayer;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;
use pocketmine\Server;

class RemoveMoney extends Executor
{
    use UtilsTrait;

    public function __construct(string $name = 'removemoney', string $description = "Retirer de l'argent à un joueur", ?string $usageMessage = null, array $aliases = ['rm'])
    {
        $this->setPermissionMessage(Prefix::PREFIX_GOOD . "§cVous n'avez pas la permissions !");
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("money.use");
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (isset($args[0])) {
            $player = Server::getInstance()->getPlayerByPrefix(strval($args[0]));
            if (!is_null($player)) {
                if (isset($args[1])) {
                    if ((int)$args[1]) {
                        Main::getInstance()->getEconomyManager()->removeMoney($player, intval($args[1]));
                        $sender->sendNotification("§fTu as retiré §e${args[1]}§2$ §r§fà§6 " . $player->getName(), CustomPlayer::NOTIF_TYPE_MONEY);
                        (new LogEvent($sender->getName()." a retiré {$args[1]}$ à ".$player->getName(),LogEvent::MONEY_TYPE))->call();
                    } else $sender->sendMessage(Prefix::PREFIX_GOOD . "§cLe montant n'est pas un chiffre !");
                } else $sender->sendMessage(Prefix::PREFIX_GOOD . "§cLe montant doit être spécifié !");
            } else {
                $xuid = Main::getInstance()->getDataManager()->getXuidByName(strval($args[0]));
                if (!is_null($xuid)) {
                    if (isset($args[1])) {
                        if ((int)$args[1]) {
                            Main::getInstance()->getEconomyManager()->removeMoneyOffline($xuid, intval($args[1]));
                            $sender->sendNotification("§fTu as retiré §e${args[1]}§2$ §r§fà §6" . $args[0], CustomPlayer::NOTIF_TYPE_MONEY);
                            (new LogEvent($sender->getName()." a retiré {$args[1]}$ à ".$args[0],LogEvent::MONEY_TYPE))->call();
                        } else $sender->sendMessage(Prefix::PREFIX_GOOD . "§cLe montant n'est pas un chiffre !");
                    } else $sender->sendMessage(Prefix::PREFIX_GOOD . "§cLe montant doit être spécifié !");
                } else $sender->sendMessage(Prefix::PREFIX_GOOD . "§cLe joueur n'existe pas !");
            }
        } else $sender->sendMessage(Prefix::PREFIX_GOOD . "§cVous devez sélectionner un joueur pour cette commande !");
    }

    public function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'Liste des joueurs', true, 'Joueurs', $this->getAllPlayersArrayForArgs());
        return parent::loadOptions($player);
    }
}