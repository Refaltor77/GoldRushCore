<?php

namespace core\managers\tebex;

use core\api\timings\TimingsSystem;
use core\api\webhook\Embed;
use core\api\webhook\Message;
use core\api\webhook\Webhook;
use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\sql\SQL;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\player\Player;
use pocketmine\Server;

class TebexManager extends Manager
{
    private Webhook $discord;

    public function __construct(Main $plugin)
    {
        $db = SQL::connection();
        $db->query("CREATE TABLE IF NOT EXISTS tebex_purshace (pseudo VARCHAR(255), data TEXT);");
        $db->query("CREATE TABLE IF NOT EXISTS tempo_rank (xuid VARCHAR(255), time_end INT, `rank` VARCHAR(255));");
        $db->close();

        $this->discord = new Webhook("https://discord.com/api/webhooks/1184602410728034425/0nLco4Owzk_uk8kla3w-XGnI5CKGmqCQo66SAH4Z_mKN8k0PMy_b_e7Fg5lNhw1EBxML");
        parent::__construct($plugin);
    }


    public function addData(string $pseudo, array $data): void {
        $pseudo = strtolower($pseudo);
        $this->sendLog($pseudo, json_encode($data));
        SQL::async(static function (RequestAsync $async, \mysqli $db) use ($pseudo, $data) : void {
            $prepare = $db->prepare("INSERT INTO tebex_purshace (pseudo, data) VALUES (?, ?);");
            $pseudo = $db->real_escape_string($pseudo);
            $data = json_encode($data);
            $prepare->bind_param("ss", $pseudo, $data);
            $prepare->execute();
        });
    }


    private function startCheckupGradesTempoTask(): void {
        TimingsSystem::schedule(function (TimingsSystem $timingsSystem, int $secondPeriod): void {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $xuid = $player->getXuid();
                SQL::async(static function(RequestAsync $async, \mysqli $db) use ($xuid) : void {
                    $query = $db->query("SELECT * FROM tempo_rank WHERE xuid = '$xuid';");
                    $data = [
                        'found' => false,
                        'data' => []
                    ];
                    if ($query->num_rows > 0) {
                        while ($row = $query->fetch_assoc()) {
                            if ($row['time_end'] <= time()) {
                                $data['found'] = true;
                                $data['data'][] = $row['rank'];
                            }
                        }
                    }
                }, static function(RequestAsync $async) use ($xuid, $player): void {
                    $data = $async->getResult();
                    if ($data['found']) {
                        foreach ($data['data'] as $index => $rank) {
                            Main::getInstance()->getRankManager()->removeRank($xuid, $rank);
                            if ($player->isConnected()) {
                                $player->sendMessage(Messages::message("§cVotre abonnement pour le grade §4" . $rank . "§c est expiré :("));
                            }
                        }
                    }
                });
            }
        }, 20 * 60);
    }

    private function sendLog(string $pseudo, string $message): void {
        $msg = new Message();
        $embed = new Embed();
        $embed->setTitle("- TEBEX LOG -");
        $embed->setAuthor("GoldRushCorps");
        $embed->addField("Pseudo", $pseudo);
        $embed->setDescription("Json: " . $message);
        $embed->setTimestamp(new \DateTime());
        $msg->addEmbed($embed);
        $msg->setContent($message);
        $this->discord->send($msg);
    }


    public function addTempoGrade(Player $player, string $rank, int $timeEnd): void {
        $xuid = $player->getXuid();
        SQL::query(static function(RequestAsync $async, \mysqli $db) use ($xuid, $rank, $timeEnd) : void {
            $query = $db->query("SELECT * FROM tempo_rank WHERE xuid = '$xuid';");
            if ($query->num_rows > 0) {
                $found = false;
                while ($row = $query->fetch_assoc()) {
                    if ($row['rank'] == $rank) {
                        $db->query("UPDATE tempo_rank SET time_end = time_end + $timeEnd WHERE xuid = '$xuid' AND `$rank` = '$rank';");
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $timeEnd = time() + $timeEnd;
                    $db->query("INSERT INTO tempo_rank (xuid, time_end, `rank`) VALUES ('$xuid', $timeEnd, '$rank')");
                }
            } else {
                $timeEnd = time() + $timeEnd;
                $db->query("INSERT INTO tempo_rank (xuid, time_end, `rank`) VALUES ('$xuid', $timeEnd, '$rank')");
            }
        });
    }


    public function checkTebexPurshace(CustomPlayer $player): void {
        if (!$player->isConnected()) return;
        $pseudo = strtolower($player->getName());
        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($pseudo) : void {
            $prepare = $db->prepare("SELECT * FROM tebex_purshace WHERE pseudo = ?;");
            $pseudo = $db->real_escape_string($pseudo);
            $prepare->bind_param('s', $pseudo);
            $prepare->execute();

            $data = [
                "found" => false,
                "data" => []
            ];
            $result = $prepare->get_result();
            if ($result->num_rows > 0) {
                $data['found'] = true;
                while ($row = $result->fetch_assoc()) {
                    $row = json_decode($row['data'], true);
                    $data['data'][] = [
                        'type' => $row['type'],
                        'data' => $row['data']
                    ];
                }
            }


            $async->setResult($data);
        }, static function(RequestAsync $async) use ($player): void {
            if (!$player->isConnected()) return;
            $data = $async->getResult();
            if (!$data['found']) return;


            foreach ($data['data'] as $index => $arrayData) {
                switch ($arrayData['type']) {
                    case 'RANK':
                        $grade = $arrayData['data']['rank'];
                        if (isset($arrayData['data']['action'])) {
                            if ($arrayData['data']['action'] === 'REMOVE') {
                                Main::getInstance()->getRankManager()->removeRank($player->getXuid(), $grade);
                                $player->sendMessage(Messages::message("§cVotre abonnement pour le grade §4" . $grade . "§c est expiré :("));
                                return;
                            }
                        }


                        Main::getInstance()->getRankManager()->addRank($player->getXuid(), $grade);
                        $player->sendNotification("Merci de votre achat sur notre boutique ! Les achats effectués sur GoldRush contribuent au maintien à long terme du serveur :3");
                        $player->sendSuccessSound();
                        break;
                    case 'KEY':
                        $key = $arrayData['data']['key_name'];
                        $player->sendNotification("Merci de votre achat sur notre boutique ! Les achats effectués sur GoldRush contribuent au maintien à long terme du serveur :3 §l§c/reward");

                        $item = match ($key) {
                            'common' => CustomiesItemFactory::getInstance()->get(Ids::KEY_COMMON),
                            'rare' => CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE),
                            'fortune' => CustomiesItemFactory::getInstance()->get(Ids::KEY_FORTUNE),
                            'mythical' => CustomiesItemFactory::getInstance()->get(Ids::KEY_MYTHICAL),
                            'legendary' => CustomiesItemFactory::getInstance()->get(Ids::KEY_LEGENDARY),
                            'black_key' => CustomiesItemFactory::getInstance()->get(Ids::KEY_BLACK_KEY),
                        };

                        Main::getInstance()->jobsStorage->addItemInStorage($player, $item);
                        $player->sendSuccessSound();
                        break;
                    case 'COSMETIC':
                        $cosmetic = $arrayData['data']['name'];
                        $type = $arrayData['data']['type'];

                        Main::getInstance()->getCosmeticManager()->addCosmetic($player->getXuid(), $cosmetic, $type);
                        $player->sendSuccessSound();
                        $player->sendMessage(Messages::message("§fMerci de votre achat dans notre boutique !"));
                        break;
                }
            }

            Main::getInstance()->jobsStorage->saveUserCache($player);

            $pseudo = strtolower($player->getName());
            SQL::async(static function(RequestAsync $async, \mysqli $db) use ($pseudo): void {
                $pseudo = $db->real_escape_string($pseudo);
                $prepare = $db->prepare("DELETE FROM tebex_purshace WHERE pseudo = ?;");
                $prepare->bind_param('s', $pseudo);
                $prepare->execute();
            });
        });
    }
}