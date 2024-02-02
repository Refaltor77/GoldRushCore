<?php

namespace core\events;

use core\Main;
use core\managers\stats\StatsManager;
use core\unicodes\OreUnicode;
use core\unicodes\OtherUnicode;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;
use pocketmine\Server;

class ScoreboardReloadEvent extends PlayerEvent
{
    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function call(): void
    {
            parent::call();
            if (!$this->player->isConnected()) return;
            $player = $this->player;
            $scoreboard = Main::getInstance()->getScoreboardManager()->getScoreboardApi()->getScoreboard("objectif");
            if (!Main::getInstance()->getSettingsManager()->getSetting($this->player, "scoreboard")) {
                Main::getInstance()->getScoreboardManager()->getScoreboardApi()->removeScoreboard($scoreboard, [$player]);
                return;
            }




            // TODO: discord link account
            //$discord = Main::getInstance()->social->hasDiscordVerifid($player) ? Main::getInstance()->social->getDiscordPseudo($player) : '§cNon vérifier';



            Main::getInstance()->getEconomyManager()->getMoneySQL($player, function (Player $player, int $money) use ($scoreboard): void {
                Main::getInstance()->getScoreboardManager()->getScoreboardApi()->sendScoreboard($scoreboard, [$player]);




                $dataScoreboard = Main::getInstance()->getScoreboardManager()->getData($player);

                $line = 1;
                $entries = [];

                $money = self::dolarsParse($money);


                    $entries[] = $scoreboard->createEntry($line, 1, 3, "mo_" . OtherUnicode::MONEY . " §f" . $money . "§6$");
                    $line++;


                if (Main::getInstance()->getFactionManager()->isInFaction($player->getXuid())) {
                    $entries[] = $scoreboard->createEntry($line, 1, 3, "fa_" . OtherUnicode::SWORD . " §f" . ($factionName = Main::getInstance()->getFactionManager()->getFactionName($player->getXuid())) . " : " . Main::getInstance()->getFactionManager()->getConnectedInt($factionName) . "§6/§f" . Main::getInstance()->getFactionManager()->getMaxMembers($factionName));
                    $line++;
                }


                    $entries[] = $scoreboard->createEntry($line, 1, 3, "pl_" . OtherUnicode::ONLINE_PLAYER . " §f" . count(Server::getInstance()->getOnlinePlayers()) ."§6/§f" . Server::getInstance()->getMaxPlayers());
                    $line++;

                    $entries[] = $scoreboard->createEntry($line, 1, 3, OtherUnicode::GOLD . " §f" . Main::getInstance()->getStatsManager()->getStats($player->getXuid())[StatsManager::GOLD_MINED]);
                    $line++;

                $entries[] = $scoreboard->createEntry($line, 1, 3,  "vo_Vote Party : " . Main::getInstance()->getVotePartyManager()->get() . "§6/§f50 " . OtherUnicode::ORB_PURPLE);
                $line++;




                foreach ($entries as $entry) {
                    $scoreboard->removeEntry($entry, [$player]);
                    $scoreboard->addEntry($entry, [$player]);
                    $scoreboard->updateEntry($entry, [$player]);
                }
            });
    }

    public function dolarsParse($s) {
        if (empty($s)) {
            return $s;
        }

        // Vérifie si la chaîne se termine par "$" et si oui, supprime-le temporairement
        if (substr($s, -1) === '$') {
            $s = substr($s, 0, -1);
        }

        // Utilise la fonction number_format pour ajouter des séparateurs de milliers
        $s = number_format((float) $s, 2, '.', ',');

        // Ajoute le symbole "$" à la fin

        $explode = explode(".", $s);
        return $explode[0];
    }
}