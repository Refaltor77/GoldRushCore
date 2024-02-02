<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\messages\Messages;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class Rank extends Executor
{
    use UtilsTrait;

    public function __construct(string $name = 'rank', string $description = "Voir vos grades", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $rank = $this->getPlugin()->getRankManager()->getRanks($sender->getXuid());
            $msg = '';
            foreach ($rank as $rankSolo) {
                $msg .= "§7[" . $this->getPlugin()->getRankManager()->getColorRank($rankSolo) . $this->getPlugin()->getRankManager()->convertRankToString($rankSolo) . "§7] ";
            }
            $sender->sendMessage(Messages::message("§fVoici vos §6grades: $msg"));
        } else {
            $player = $this->getPlugin()->getServer()->getPlayerByPrefix(strval($args[0]));
            if (!is_null($player)) {
                $rank = $this->getPlugin()->getRankManager()->getRanks($player->getXuid());
                $msg = '';
                foreach ($rank as $rankSolo) {
                    $msg .= "§7[" . $this->getPlugin()->getRankManager()->getColorRank($rankSolo) . $this->getPlugin()->getRankManager()->convertRankToString($rankSolo) . "§7] ";
                }
                $sender->sendMessage(Messages::message("§fVoici les §6grades§f du joueur §6{$player->getName()}: $msg"));
            } else {
                $xuid = $this->getPlugin()->getDataManager()->getXuidByName(strval($args[0]));
                if (!is_null($xuid)) {
                    $rank = $this->getPlugin()->getRankManager()->getRanks($xuid);
                    $msg = '';
                    foreach ($rank as $rankSolo) {
                        $msg .= "§7[" . $this->getPlugin()->getRankManager()->getColorRank($rankSolo) . $this->getPlugin()->getRankManager()->convertRankToString($rankSolo) . "§7] ";
                    }
                    $sender->sendMessage(Messages::message("§fVoici les §6grades§f du joueur §6{$args[0]}: $msg"));
                } else $sender->sendMessage(Messages::message("§cLe joueur n'existe pas !"));
            }
        }
    }

    public function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'Liste des joueurs', true, 'Joueurs', $this->getAllPlayersArrayForArgs());
        return parent::loadOptions($player);
    }
}