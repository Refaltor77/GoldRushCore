<?php

namespace core\managers\dc;

use core\Main;
use core\managers\Manager;
use core\sql\SQL;

class DcManager extends Manager
{
    public function __construct(Main $plugin)
    {
        $db = SQL::connection();

        $db->prepare("CREATE TABLE IF NOT EXISTS dc(`xuid` VARCHAR(255) PRIMARY KEY);")->execute();

        $db->close();

        parent::__construct($plugin);
    }


    public function isDc(string $xuid): bool
    {
        $db = SQL::connection();
        $prepare = $db->prepare("SELECT * FROM dc WHERE `xuid` = ?;");
        $prepare->bind_param('s', $xuid);
        $prepare->execute();
        $result = $prepare->get_result();
        $db->close();
        return $result->num_rows > 0;
    }

    public function addDc(string $xuid): void
    {
        $db = SQL::connection();
        $prepare = $db->prepare("INSERT INTO dc(`xuid`) VALUES (?);");
        $prepare->bind_param('s', $xuid);
        $prepare->execute();
        $db->close();
    }

    public function removeDc(string $xuid): void
    {
        $db = SQL::connection();
        $prepare = $db->prepare("DELETE FROM dc WHERE `xuid` = ?;");
        $prepare->bind_param('s', $xuid);
        $prepare->execute();
        $db->close();
    }

}