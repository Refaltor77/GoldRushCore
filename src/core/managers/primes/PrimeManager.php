<?php

namespace core\managers\primes;

use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\sql\SQL;
use mysqli;
use pocketmine\player\Player;
use pocketmine\Server;

class PrimeManager extends Manager
{
    public array $cache = [];
    const PATTERN = [
        'kill_combo' => 0,
        'price_prime' => 0
    ];

    public function __construct(Main $plugin)
    {
        $db = SQL::connection();
        $db->query("CREATE TABLE IF NOT EXISTS prime(xuid VARCHAR(255) PRIMARY KEY, kill_combo INT, price_prime INT);");

        $query = $db->query("SELECT * FROM prime;");
        while ($row = $query->fetch_assoc()) {
            $this->cache[$row['xuid']] = [
                'kill_combo' => $row['kill_combo'],
                'price_prime' => $row['price_prime']
            ];
        }

        $db->close();
        parent::__construct($plugin);
    }

    public function loadData(Player $player): void {
        $xuid = $player->getXuid();
        SQL::async(static function(RequestAsync $async, mysqli $db) use ($xuid) : void {
            $query = $db->query("SELECT * FROM prime WHERE xuid = '$xuid';");
            $data = self::PATTERN;
            if ($query->num_rows > 0) {
                $rows = $query->fetch_assoc();
                $data['kill_combo'] = $rows['kill_combo'];
                $data['price_prime'] = $rows['price_prime'];
            }
            $async->setResult($data);
        }, static function(RequestAsync $async) use ($player): void {
            if (!$player->isConnected()) return;
            $player->hasPrimeLoaded = true;
            $xuid = $player->getXuid();
            $result = $async->getResult();
            Main::getInstance()->getPrimeManager()->cache[$xuid] = $result;
        });
    }


    public function resetCombo(Player $player): void {
        $this->cache[$player->getXuid()]['kill_combo'] = 0;
        $this->cache[$player->getXuid()]['price_prime'] = 0;
    }

    public function addKill(Player $player): void {
        $this->cache[$player->getXuid()]['kill_combo']++;

        if ($this->cache[$player->getXuid()]['kill_combo'] > 5) {
            $this->cache[$player->getXuid()]['price_prime'] += 250;
        }
    }

    public function getKillCombo(Player $player): int {
        return $this->cache[$player->getXuid()] ?? 0;
    }

    public function setPrime(Player $player, int $primePrice): void {
        $this->cache[$player->getXuid()]['price_prime'] = $primePrice;
    }

    public function getMaxWanted(): string {
        $i = 0;
        $xuidFind = "";
        foreach ($this->cache as $xuid => $comboKill) {
            if ($comboKill >= $i) {
                $i = $comboKill;
                $xuidFind = $xuid;
            }
        }

        return $xuidFind;
    }

    public function getPrimeDeux(): string {
        $arrayQueried = [];
        foreach ($this->cache as $xuid => $comboKill) {
            $arrayQueried[$xuid] = $comboKill['kill_combo'];
        }

        arsort($arrayQueried);
        $index = [];
        arsort($arrayQueried);
        foreach ($arrayQueried as $xuid => $kill) {
            $index[] = $xuid;
        }

        if (isset($index[1])) {
            return $index[1];
        }

        return $index[0] ?? "";
    }

    public function getPrimeTrois(): string {
        $arrayQueried = [];

        foreach ($this->cache as $xuid => $comboKill) {
            $arrayQueried[$xuid] = $comboKill['kill_combo'];
        }
        $index = [];
        arsort($arrayQueried);
        foreach ($arrayQueried as $xuid => $kill) {
            $index[] = $xuid;
        }

        if (isset($index[2])) {
            return $index[2];
        }
        if (isset($index[1])) {
            return $index[1];
        }
        return $index[0] ?? "";
    }

    public function getPrimePrice(Player $player): int {
        return $this->cache[$player->getXuid()]['price_prime'];
    }

    public function getPrimePriceXuid(string $xuid): int {
        return $this->cache[$xuid]['price_prime'];
    }

    public function addPricePrime(Player $player, int $money): void {
        $this->cache[$player->getXuid()] = $this->getPrimePrice($player) + $money;
    }

    public function getAllPrimeArray(): array {
        $arrayQueried = [];
        foreach ($this->cache as $xuid => $values) {
            if ($values['price_prime'] > 0) {
                $name = Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? "404";
                $arrayQueried[$name] = $values['price_prime'];
            }
        }

        return $arrayQueried;
    }

    public function saveData(Player $player, bool $async = true): void
    {
        $xuid = $player->getXuid();

        $data = $this->cache[$xuid] ?? self::PATTERN;


        if ($async) {
            SQL::async(static function (RequestAsync $async, mysqli $db) use ($xuid, $data): void {

                $comboKill = $data['kill_combo'];
                $pricePrime = $data['price_prime'];

                $prepare = $db->prepare("INSERT INTO prime (xuid, kill_combo, price_prime) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE kill_combo = VALUES(kill_combo), price_prime = VALUES(price_prime);");
                $prepare->bind_param('sii', $xuid, $comboKill, $pricePrime);
                $prepare->execute();
            });
        } else {
            $db = SQL::connection();
            $comboKill = $data['kill_combo'];
            $pricePrime = $data['price_prime'];

            $prepare = $db->prepare("INSERT INTO prime (xuid, kill_combo, price_prime) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE kill_combo = VALUES(kill_combo), price_prime = VALUES(price_prime);");
            $prepare->bind_param('sii', $xuid, $comboKill, $pricePrime);
            $prepare->execute();
            $db->close();
        }
    }


    public function saveAllData(): void {
        $db = SQL::connection();
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {

            $xuid = $player->getXuid();
            $comboKill = $this->cache[$xuid]['kill_combo'];
            $pricePrime = $this->cache[$xuid]['price_prime'];

            $prepare = $db->prepare("INSERT INTO prime (xuid, kill_combo, price_prime) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE kill_combo = VALUES(kill_combo), price_prime = VALUES(price_prime);");
            $prepare->bind_param('sii', $xuid, $comboKill, $pricePrime);
            $prepare->execute();
        }
        $db->close();
    }
}