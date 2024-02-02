<?php

namespace core\commands\executors;

use core\api\form\elements\Button;
use core\api\form\MenuForm;
use core\commands\Executor;
use core\Main;
use core\managers\jobs\JobsManager;
use core\managers\ranks\RankManager;
use core\managers\stats\StatsManager as S;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;
use pocketmine\Server;

class Stats extends Executor
{

    use UtilsTrait;

    public function __construct(string $name = 'stats', string $description = "Voir les stats", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            Main::getInstance()->getJobsManager()->getAllJobsXuidSql($sender->getXuid(), function (array $jobs) use ($sender): void {
                if (!$sender->isConnected()) return;
                $stats = $this->getPlugin()->getStatsManager()->getStats($sender->getXuid());


                $kill = $stats[S::KILL];
                $death = $stats[S::DEATH];
                $blockPlace = $stats[S::BLOCK_PLACE];
                $blockBreak = $stats[S::BLOCK_BREAK];
                $mobKills = $stats[S::MOB_KILL];
                $bossKills = $stats[S::BOSS_KILL];
                $gold = $stats[S::GOLD_MINED];
                $msg = $stats[S::MSG];

                $kd = 1;
                if ($kill > 0 && $death > 0) $kd = round($kill / $death);

                $time = $stats[S::TIME];
                $formatted_time = date('G', $time);
                $formatted_time2 = date('i', $time);



                $farmerLvl = $jobs[JobsManager::FARMER]['lvl'];
                $hunterLvl = $jobs[JobsManager::HUNTER]['lvl'];
                $minorLvl = $jobs[JobsManager::MINOR]['lvl'];
                $bucheronLvl = $jobs[JobsManager::LUMBERJACK]['lvl'];


                $sender->sendForm(new MenuForm("STATS", "", [
                    new Button("kills_$kill"),
                    new Button("death_$death"),
                    new Button("pseud_" . $sender->getName()),
                    new Button("ranks_" . RankManager::CONVERSION[Main::getInstance()->getRankManager()->getSupremeRankPriority($sender->getXuid())]),
                    new Button("facti_" . Main::getInstance()->getFactionManager()->getFactionName($sender->getXuid())),
                    new Button("blobr_$formatted_time" . "h$formatted_time2"),

                    new Button("jobsf_$farmerLvl"),
                    new Button("jobsh_$hunterLvl"),
                    new Button("jobsm_$minorLvl"),
                    new Button("jobsb_$bucheronLvl"),

                    new Button("ormin_$gold"),
                    new Button("msgse_$msg"),
                ]));
            });
        } else {
            $player = Server::getInstance()->getPlayerByPrefix($args[0]);

            if (!$player instanceof CustomPlayer) {
                $sender->sendMessage(Messages::message("§cLe joueur n'est pas en ligne."));
                return;
            }

            Main::getInstance()->getJobsManager()->getAllJobsXuidSql($player->getXuid(), function (array $jobs) use ($player, $sender): void {
                if (!$player->isConnected()) return;
                $stats = $this->getPlugin()->getStatsManager()->getStats($player->getXuid());


                $kill = $stats[S::KILL];
                $death = $stats[S::DEATH];
                $blockPlace = $stats[S::BLOCK_PLACE];
                $blockBreak = $stats[S::BLOCK_BREAK];
                $mobKills = $stats[S::MOB_KILL];
                $bossKills = $stats[S::BOSS_KILL];
                $gold = $stats[S::GOLD_MINED];
                $msg = $stats[S::MSG];

                $kd = 1;
                if ($kill > 0 && $death > 0) $kd = round($kill / $death);

                $time = $stats[S::TIME];
                $formatted_time = date('G', $time);
                $formatted_time2 = date('i', $time);



                $farmerLvl = $jobs[JobsManager::FARMER]['lvl'];
                $hunterLvl = $jobs[JobsManager::HUNTER]['lvl'];
                $minorLvl = $jobs[JobsManager::MINOR]['lvl'];
                $bucheronLvl = $jobs[JobsManager::LUMBERJACK]['lvl'];


                $sender->sendForm(new MenuForm("STATS", "", [
                    new Button("kills_$kill"),
                    new Button("death_$death"),
                    new Button("pseud_" . $sender->getName()),
                    new Button("ranks_" . RankManager::CONVERSION[Main::getInstance()->getRankManager()->getSupremeRankPriority($player->getXuid())]),
                    new Button("facti_" . Main::getInstance()->getFactionManager()->getFactionName($player->getXuid())),
                    new Button("blobr_$formatted_time" . "h$formatted_time2"),

                    new Button("jobsf_$farmerLvl"),
                    new Button("jobsh_$hunterLvl"),
                    new Button("jobsm_$minorLvl"),
                    new Button("jobsb_$bucheronLvl"),

                    new Button("ormin_$gold"),
                    new Button("msgse_$msg"),
                ]));
            });
        }
    }

    public function convertSecondToStringCorrect(int $timestamp): string
    {
        $time = $timestamp . ' seconde(s)';


        if ($timestamp >= 60) {
            $date = $timestamp / 60;
            $time = intval($date) . ' minute(s)';
        }

        if ($timestamp >= 60 * 60) {
            $date = $timestamp / 3600;
            $time = intval($date) . ' heure(s)';
        }

        if ($timestamp >= 60 * 60 * 24) {
            $date = $timestamp / 86400;
            $time = intval($date) . ' jour(s)';
        }

        if ($timestamp >= 60 * 60 * 24 * 7) {
            $date = new \DateTime();
            $date->setTimestamp($timestamp);
            $time = $date->format('d-m-Y');
        }

        return $time;
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, "Liste des joueurs", true, 'Joueurs', $this->getAllPlayersArrayForArgs());
        return parent::loadOptions($player);
    }
}