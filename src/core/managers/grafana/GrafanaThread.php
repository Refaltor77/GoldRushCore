<?php

namespace core\managers\grafana;

use core\settings\Settings;
use pocketmine\thread\Thread;
use pmmp\thread\ThreadSafeArray;

class GrafanaThread extends Thread
{
    public ThreadSafeArray $connections;
    public ThreadSafeArray $sessions;
    public ThreadSafeArray $oreMined;
    public ThreadSafeArray $msgValues;

    public ThreadSafeArray $economyTransaction;
    public ThreadSafeArray $banTransaction;
    public ThreadSafeArray $muteTransaction;
    public ThreadSafeArray $buyRankTransaction;

    public function __construct()
    {
        $this->connections = new ThreadSafeArray();
        $this->sessions = new ThreadSafeArray();
        $this->oreMined = new ThreadSafeArray();
        $this->msgValues = new ThreadSafeArray();
        $this->economyTransaction = new ThreadSafeArray();
        $this->banTransaction = new ThreadSafeArray();
        $this->muteTransaction = new ThreadSafeArray();
        $this->buyRankTransaction = new ThreadSafeArray();
    }

    protected function onRun(): void
    {
        $information = [
            'hostname' => 'goldrushmc.fun',
            'username' => 'u2_hNGHvA9ucC',
            'database' => 's2_grafana',
            'password' => '=DehP3.FC.S2Jx=manqKpdIf',
            'port' => '3306'
        ];

        while (true) {
            if ($this->isKilled) break;

            $db =  new \mysqli(
                $information['hostname'],
                $information['username'],
                $information['password'],
                $information['database'],
                3306
            );


            foreach ($this->connections as $xuid) {
                $db->query("INSERT INTO connections (player_xuid) VALUES ('$xuid');");
                $this->connections->shift();
            }

            foreach ($this->sessions as $sessionValues) {
                $sessionValues = $sessionValues->deserialize();
                $playerXuid = $sessionValues['xuid'];
                $timeSession = $sessionValues['time_session'];
                $db->query("INSERT INTO sessions_time (player_xuid, time_session) VALUES ('$playerXuid', $timeSession);");
                $this->sessions->shift();
            }


            foreach ($this->oreMined as $oreValues) {
                $oreValues = $oreValues->deserialize();
                $playerXuid = $oreValues['xuid'];
                $posHash = $oreValues['pos_hash'];
                $type = $oreValues['type'];


                $db->query("INSERT INTO " . $type . "_mined" . " (player_xuid_mined, pos_hash) VALUES ('$playerXuid', '$posHash');");
                $this->oreMined->shift();
            }


            foreach ($this->msgValues as $msgValue) {
                $msgValue = $msgValue->deserialize();
                $playerXuid = $msgValue['xuid'];
                $content = $msgValue['content'];
                $content = $db->real_escape_string($content);
                $db->query("INSERT INTO messages_send  (player_xuid, message_content) VALUES ('$playerXuid', '$content');");
                $this->msgValues->shift();
            }



            foreach ($this->banTransaction as $values) {
                $values = $values->deserialize();
                $playerXuid = $values['xuid'];
                $playerXuidModo = $values['xuid_modo'];
                $reason = $values['reason_content'];
                $time = $values['time'];
                $db->query("INSERT INTO ban_transactions (player_xuid_mute, modo_xuid, reason_content, time) VALUES ('$playerXuid', '$playerXuidModo', '$reason', $time);");
                $this->banTransaction->shift();
            }

            foreach ($this->muteTransaction as $values) {
                $values = $values->deserialize();
                $playerXuid = $values['xuid'];
                $playerXuidModo = $values['xuid_modo'];
                $reason = $values['reason_content'];
                $time = $values['time'];
                $db->query("INSERT INTO mute_transactions (player_xuid_mute, modo_xuid, reason_content, time) VALUES ('$playerXuid', '$playerXuidModo', '$reason', $time);");
                $this->muteTransaction->shift();
            }


            foreach ($this->buyRankTransaction as $values) {
                $values = $values->deserialize();
                $playerXuid = $values['xuid'];
                $rank = $values['rank'];
                $price = $values['price'];
                $db->query("INSERT INTO buy_rank_transaction (player_xuid, `rank`, price) VALUES ('$playerXuid', '$rank', $price);");
                $this->buyRankTransaction->shift();
            }

            $db->close();


            sleep(1);
        }
    }
}