<?php

namespace core\managers\vote;

use core\api\timings\TimingsSystem;
use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\sql\SQL;
use pocketmine\utils\Config;

class VoteParty extends Manager
{
    public int $vote = 0;

    public function __construct(Main $main)
    {
        $db = SQL::connection();
        $db->query("CREATE TABLE IF NOT EXISTS voteparty (data INT);");

        $query = $db->query("SELECT * FROM voteparty;");
        if ($query->num_rows <= 0) {
            $db->query("INSERT INTO voteparty (data) VALUES (0)");
        }
        $all = $query->fetch_assoc();
        $this->vote = $all["data"] ?? 0;

        $db->close();

        $timing = new TimingsSystem();
        $timing->createTiming(function (): void {
            $this->getVote(function (int $vote): void {
                $this->vote = $vote;
            });
        }, 20 * 30);

        parent::__construct($main);
    }

    public function add(): void {
        $this->vote++;


        SQL::async(static function (RequestAsync $async, \mysqli $db): void  {
            $stmt = $db->prepare("UPDATE voteparty SET data = data + 1");
            $stmt->execute();
        });
    }

    public function reset(): void {
        SQL::async(static function (RequestAsync $async, \mysqli $db): void  {
            $stmt = $db->prepare("DELETE FROM voteparty;");
            $stmt->execute();

            $stmt = $db->prepare("INSERT INTO voteparty (data) VALUES (0)");
            $stmt->execute();
        });
    }

    public function getVote(callable $callback): void {
        SQL::async(static function (RequestAsync $async, \mysqli $db): void {
            $all = $db->query("SELECT * FROM voteparty;");
            $return = 0;
            if ($all->num_rows > 0) {
                $all = $all->fetch_assoc();
                $return = $all["data"];
            }
            $async->setResult($return);
        }, static function(RequestAsync $async) use ($callback) : void {
            $vote = $async->getResult();
            $callback((int)$vote);
        });
    }

    public function createTaskMaintened(): void {
        $t = new TimingsSystem();
        $t->createTiming(function (TimingsSystem $timingsSystem, int $second): void {
            SQL::async(static function (RequestAsync $async, \mysqli $db): void {
                $all = $db->query("SELECT * FROM voteparty;");
                $all = $all->fetch_assoc();
                $async->setResult((int)$all['data'] ?? 0);
            }, static function(RequestAsync $async): void {
                $vote = $async->getResult();
                Main::getInstance()->getVotePartyManager()->vote = (int)$vote;
            });
        }, 20 * 10);
    }

    public function get(): int {
        return $this->vote;
    }
}