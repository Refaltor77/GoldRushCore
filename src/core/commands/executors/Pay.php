<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\events\LogEvent;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;
use pocketmine\Server;

class Pay extends Executor
{
    use UtilsTrait;

    public function __construct(string $name = 'pay', string $description = "Payer un joueur", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {

        if ($sender->hasTagged()) {
            $sender->sendMessage(Messages::message("§cVous êtes en combat !"));
            return;
        }

        if (isset($args[0])) {
            $player = $this->getPlugin()->getServer()->getPlayerByPrefix(strval($args[0]));
            if (!is_null($player)) {
                if ($player->getXuid() !== $sender->getXuid()) {
                    if (isset($args[1])) {
                        if (is_numeric($args[1])) {
                            if ($args[1] > 0) {
                                Main::getInstance()->getEconomyManager()->getMoneySQL($sender,
                                function (Player $sender, int $money) use ($args, $player) : void {
                                    if ($money >= intval($args[1])) {
                                        Main::getInstance()->getEconomyManager()->addMoney($player, intval($args[1]));
                                        Main::getInstance()->getEconomyManager()->removeMoney($sender, intval($args[1]));
                                        $player->sendNotification("§fTu as reçu de la part de §6" . $sender->getName() . "§r §e{$args[1]}§2$", CustomPlayer::NOTIF_TYPE_MONEY);
                                        $sender->sendNotification("§fTu as envoyer §e{$args[1]}§2$ §r§fà§6 " . $player->getName(), CustomPlayer::NOTIF_TYPE_MONEY);
                                        (new LogEvent($sender->getName()." a envoyé {$args[1]}$ à ".$player->getName(),LogEvent::MONEY_TYPE))->call();
                                    }else $sender->sendMessage(Messages::message("§cTu n’as pas assez d'argent !"));
                                });
                            } else $sender->sendMessage(Messages::message("§cVous ne pouvez pas mettre un chiffre négatif."));
                        } else $sender->sendMessage(Messages::message("§cLe montant n'est pas un chiffre !"));
                    } else $sender->sendMessage(Messages::message("§cLe montant doit être spécifié !"));
                } else $sender->sendMessage(Messages::message("§cVous ne pouvez pas vous payer vous-même !"));
            } else {
                $xuid = Main::getInstance()->getDataManager()->getXuidByName($args[0]);
                if (!is_null($xuid)) {
                    if ($sender->getXuid() !== $xuid) {
                        if (isset($args[1])) {
                            if (is_numeric($args[1])) {
                                if ($args[1] > 0) {
                                    Main::getInstance()->getEconomyManager()->getMoneySQLXuid($xuid,
                                        function (int $money) use ($args, $sender, $xuid) : void {
                                            if ($money >= intval($args[1])) {
                                                Main::getInstance()->getEconomyManager()->addMoneyOffline($xuid, intval($args[1]));
                                                Main::getInstance()->getEconomyManager()->removeMoney($sender, intval($args[1]));
                                                $sender->sendNotification("§fTu as envoyer §e{$args[1]}§2$ §r§fà§6 " . $args[0], CustomPlayer::NOTIF_TYPE_MONEY);
                                                (new LogEvent($sender->getName()." a envoyé {$args[1]}$ à ".$args[0],LogEvent::MONEY_TYPE))->call();
                                            }else $sender->sendMessage(Messages::message("§cTu n’as pas assez d'argent !"));
                                        });
                                } else $sender->sendMessage(Messages::message("§cVous ne pouvez pas mettre un chiffre négatif."));
                            } else $sender->sendMessage(Messages::message("§cLe montant n'est pas un chiffre !"));
                        } else $sender->sendMessage(Messages::message("§cLe montant doit être spécifié !"));
                    } else $sender->sendMessage(Messages::message("§cVous ne pouvez pas vous payer vous-même !"));

                } else $sender->sendMessage(Messages::message("§cLe joueur n'est pas en ligne."));
            }
        } else $sender->sendMessage(Messages::message("§cVous devez sélectionner un joueur pour cette commande !"));
    }

    public function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'Liste des joueurs', true, 'Joueurs', $this->getAllPlayersArrayForArgs());
        return parent::loadOptions($player);
    }
}