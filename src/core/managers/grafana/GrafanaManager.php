<?php

namespace core\managers\grafana;

use core\api\timings\TimingsSystem;
use core\Main;
use core\managers\Manager;
use core\sql\SQL;
use core\traits\HomeTrait;
use core\traits\UtilsTrait;
use pocketmine\Server;
use pocketmine\thread\NonThreadSafeValue;
use pocketmine\world\Position;

class GrafanaManager extends Manager
{
    use UtilsTrait;

    private GrafanaThread $thread;


    public function __construct(Main $plugin)
    {
        $this->thread = new GrafanaThread();




        $db = SQL::grafanaConnection();


        $db->query("CREATE TABLE IF NOT EXISTS buy_rank_transaction(
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_xuid VARCHAR(255),
        `rank` VARCHAR(255),
        price INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        amount INT);
        ");


        $db->query("CREATE TABLE IF NOT EXISTS economy_transaction(
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_xuid VARCHAR(255),
        transaction_type VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        amount INT);
        ");


        $db->query("CREATE TABLE IF NOT EXISTS connections(
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_xuid VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
        ");


        $db->query("CREATE TABLE IF NOT EXISTS messages_send(
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_xuid VARCHAR(255),
        message_content TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
        ");


        $db->query("CREATE TABLE IF NOT EXISTS ban_transactions(
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_xuid_ban VARCHAR(255),
        modo_xuid VARCHAR(255),
        reason_content TEXT,
        time TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
        ");


        $db->query("CREATE TABLE IF NOT EXISTS mute_transactions(
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_xuid_mute VARCHAR(255),
        modo_xuid VARCHAR(255),
        reason_content TEXT,
        time TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
        ");


        $db->query("CREATE TABLE IF NOT EXISTS gold_mined(
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_xuid_mined VARCHAR(255),
        pos_hash VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
        ");


        $db->query("CREATE TABLE IF NOT EXISTS platinum_mined(
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_xuid_mined VARCHAR(255),
        pos_hash VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
        ");


        $db->query("CREATE TABLE IF NOT EXISTS amethyst_mined(
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_xuid_mined VARCHAR(255),
        pos_hash VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
        ");


        $db->query("CREATE TABLE IF NOT EXISTS emerald_mined(
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_xuid_mined VARCHAR(255),
        pos_hash VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
        ");


        $db->query("CREATE TABLE IF NOT EXISTS copper_mined(
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_xuid_mined VARCHAR(255),
        pos_hash VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
        ");


        $db->query("CREATE TABLE IF NOT EXISTS sessions_time(
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_xuid VARCHAR(255),
        time_session INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
        ");



        $db->close();
        $this->thread->start();
        $this->startTaskCollect();
        parent::__construct($plugin);
    }


    public array $connexionsQueues = [];
    public array $oreMined = [];
    public array $copperMinedQueue = [];
    public array $sessionsEnd = [];
    public array $msgQueue = [];
    public array $economyTransactions =  [];
    public array $banTransaction = [];
    public array $muteTransaction = [];
    public array $buyRankTransaction = [];



    private function startTaskCollect(): void {
        $timing = new TimingsSystem();
        $timing->createTiming(function (): void {


            if (!Main::getInstance()->getConfig()->get('stats')) {
                return;
            }

            foreach ($this->connexionsQueues as $xuid) {
                $this->thread->connections[] = $xuid;
                unset($this->connexionsQueues[array_search($xuid, $this->connexionsQueues)]);
            }

            foreach ($this->sessionsEnd as $sessionValues) {
                $this->thread->sessions[] = new NonThreadSafeValue($sessionValues);
                unset($this->sessionsEnd[array_search($sessionValues, $this->sessionsEnd)]);
            }

            foreach ($this->oreMined as $oreValues) {
                $this->thread->oreMined[] = new NonThreadSafeValue($oreValues);
                unset($this->oreMined[array_search($oreValues, $this->oreMined)]);
            }

            foreach ($this->msgQueue as $msgValues) {
                $this->thread->msgValues[] = new NonThreadSafeValue($msgValues);
                unset($this->msgQueue[array_search($msgValues, $this->msgQueue)]);
            }

            foreach ($this->economyTransactions as $economyValues) {
                $this->thread->economyTransaction[] = new NonThreadSafeValue($economyValues);
                unset($this->economyTransactions[array_search($economyValues, $this->economyTransactions)]);
            }

            foreach ($this->banTransaction as $banValues) {
                $this->thread->banTransaction[] = new NonThreadSafeValue($banValues);
                unset($this->banTransaction[array_search($banValues, $this->banTransaction)]);
            }

            foreach ($this->muteTransaction as $muteValues) {
                $this->thread->muteTransaction[] = new NonThreadSafeValue($muteValues);
                unset($this->muteTransaction[array_search($muteValues, $this->muteTransaction)]);
            }

            foreach ($this->buyRankTransaction as $values) {
                $this->thread->buyRankTransaction[] = new NonThreadSafeValue($values);
                unset($this->buyRankTransaction[array_search($values, $this->buyRankTransaction)]);
            }
        }, 60);
    }


    public function addConnexionEntry(string $xuid): void {
        $this->connexionsQueues[] = $xuid;
    }

    public function addSessionTimeEnd(string $xuid, int $timeSession): void {
        $this->sessionsEnd[] = [
            'xuid' => $xuid,
            'time_session' => $timeSession
        ];
    }



    public function addGoldMined(string $xuid, Position $position): void
    {
        $posHash = $this->positionToString($position);
        $this->oreMined[] = [
            'xuid' => $xuid,
            'pos_hash' => $posHash,
            'type' => 'gold'
        ];
    }
    public function addPlatinumMined(string $xuid, Position $position): void
    {
        $posHash = $this->positionToString($position);
        $this->oreMined[] = [
            'xuid' => $xuid,
            'pos_hash' => $posHash,
            'type' => 'platinum'
        ];
    }
    public function addAmethystMined(string $xuid, Position $position): void
    {
        $posHash = $this->positionToString($position);
        $this->oreMined[] = [
            'xuid' => $xuid,
            'pos_hash' => $posHash,
            'type' => 'amethyst'
        ];
    }
    public function addEmeraldMined(string $xuid, Position $position): void
    {
        $posHash = $this->positionToString($position);
        $this->oreMined[] = [
            'xuid' => $xuid,
            'pos_hash' => $posHash,
            'type' => 'emerald'
        ];
    }
    public function addCopperMined(string $xuid, Position $position): void
    {
        $posHash = $this->positionToString($position);
        $this->oreMined[] = [
            'xuid' => $xuid,
            'pos_hash' => $posHash,
            'type' => 'copper'
        ];
    }

    /*
     * $db->query("CREATE TABLE IF NOT EXISTS buy_rank_transaction(
        id INT AUTO_INCREMENT PRIMARY KEY,
        player_xuid VARCHAR(255),
        `rank` VARCHAR(255),
        price INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        amount INT);
        ");
     */

    public function addBuyRankTransactionQueue(string $xuid, string $rank, int $price): void {
        $this->msgQueue[] = [
            'xuid' => $xuid,
            'rank' => $rank,
            'price' => $price
        ];
    }

    public function addMessageQueue(string $xuid, string $content): void {
        $this->msgQueue[] = [
            'xuid' => $xuid,
            'content' => $content
        ];
    }

    public function addMuteQueue(string $xuid, string $xuidModo, string $reason, int $time): void {
        $this->muteTransaction[] = [
            'xuid' => $xuid,
            'xuid_modo' => $xuidModo,
            'reason_content' => $reason,
            'time' => $time
        ];
    }

    public function addBanQueue(string $xuid, string $xuidModo, string $reason, int $time): void {
        $this->banTransaction[] = [
            'xuid' => $xuid,
            'xuid_modo' => $xuidModo,
            'reason_content' => $reason,
            'time' => $time
        ];
    }


    public function addEconomyTransaction(string $xuid, string $type, int $amount): void {
        $this->economyTransactions[] = [
            'xuid' => $xuid,
            'type' => $type,
            'amount' => $amount
        ];
    }




    public function insertFakeData($db)
    {
        for ($i = 0; $i < 1236; $i++) {
            // Insertion de fausses données dans la table economy_transaction
            $db->query("
            INSERT INTO economy_transaction (player_xuid, transaction_type, amount)
            VALUES (
                '" . $this->generateRandomString(10) . "',
                '" . $this->generateRandomTransactionType() . "',
                " . rand(1, 100) . "
            )
        ");
            $db->query("
            INSERT INTO connections (player_xuid)
            VALUES ('" . $this->generateRandomString(10) . "')
        ");

            // Insertion de fausses données dans la table messages_send
            $db->query("
            INSERT INTO messages_send (player_xuid, message_content)
            VALUES (
                '" . $this->generateRandomString(10) . "',
                '" . $this->generateRandomString(50) . "'
            )
        ");
            // Insertion de fausses données dans la table ban_transactions
            $db->query("
            INSERT INTO ban_transactions (player_xuid_ban, modo_xuid, reason_content, time)
            VALUES (
                '" . $this->generateRandomString(10) . "',
                '" . $this->generateRandomString(10) . "',
                '" . $this->generateRandomString(50) . "',
                '" . $this->generateRandomDateTime() . "'
            )
        ");

            // Insertion de fausses données dans la table mute_transactions
            $db->query("
            INSERT INTO mute_transactions (player_xuid_mute, modo_xuid, reason_content, time)
            VALUES (
                '" . $this->generateRandomString(10) . "',
                '" . $this->generateRandomString(10) . "',
                '" . $this->generateRandomString(50) . "',
                '" . $this->generateRandomDateTime() . "'
            )
        ");

            // Insertion de fausses données dans la table golds_mined
            $db->query("
            INSERT INTO golds_mined (player_xuid_mined, pos_hash)
            VALUES (
                '" . $this->generateRandomString(10) . "',
                '" . $this->generateRandomString(255) . "'
            )
        ");

            // Insertion de fausses données dans la table platinum_mined
            $db->query("
            INSERT INTO platinum_mined (player_xuid_mined, pos_hash)
            VALUES (
                '" . $this->generateRandomString(10) . "',
                '" . $this->generateRandomString(255) . "'
            )
        ");

            // Insertion de fausses données dans la table amethyst_mined
            $db->query("
            INSERT INTO amethyst_mined (player_xuid_mined, pos_hash)
            VALUES (
                '" . $this->generateRandomString(10) . "',
                '" . $this->generateRandomString(255) . "'
            )
        ");

            // Insertion de fausses données dans la table emerald_mined
            $db->query("
            INSERT INTO emerald_mined (player_xuid_mined, pos_hash)
            VALUES (
                '" . $this->generateRandomString(10) . "',
                '" . $this->generateRandomString(255) . "'
            )
        ");

            // Insertion de fausses données dans la table copper_mined
            $db->query("
            INSERT INTO copper_mined (player_xuid_mined, pos_hash)
            VALUES (
                '" . $this->generateRandomString(10) . "',
                '" . $this->generateRandomString(255) . "'
            )
        ");



            // Insertion de fausses données dans la table connections
            $db->query("
            INSERT INTO connections (player_xuid)
            VALUES ('" . $this->generateRandomString(10) . "')
        ");

            // Insertion de fausses données dans la table messages_send
            $db->query("
            INSERT INTO messages_send (player_xuid, message_content)
            VALUES (
                '" . $this->generateRandomString(10) . "',
                '" . $this->generateRandomString(50) . "'
            )
        ");

            // ... Ajoutez d'autres insertions selon vos tables ...

            // Insertion de fausses données dans la table sessions_time
            $db->query("
            INSERT INTO sessions_time (player_xuid, time_session)
            VALUES (
                '" . $this->generateRandomString(10) . "',
                '" . $this->generateRandomDateTime() . "'
            )
        ");
        }
    }

    public function generateRandomString($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    public function generateRandomTransactionType()
    {
        $types = ['deposit', 'withdraw'];
        return $types[rand(0, count($types) - 1)];
    }

    public function generateRandomDateTime()
    {
        $startTimestamp = strtotime('2020-01-01');
        $endTimestamp = strtotime('2023-01-01');
        $randomTimestamp = rand($startTimestamp, $endTimestamp);

        return date('Y-m-d H:i:s', $randomTimestamp);
    }
}