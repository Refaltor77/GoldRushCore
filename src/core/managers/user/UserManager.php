<?php

namespace core\managers\user;

use core\Main;
use core\managers\Manager;
use core\sql\SQL;

class UserManager extends Manager
{

    public function __construct(Main $plugin)
    {
        parent::__construct($plugin);
    }

    public function ipIsTaken(string $name, string $ip): bool
    {
        $db = SQL::connection();
        $select = $db->prepare("SELECT * FROM `users_goldrush` WHERE `ipv4` = ? AND `pseudo` != ?;");
        $ip = $db->real_escape_string($ip);
        $select->bind_param('ss', $ip, $name);
        $select->execute();
        $result = $select->get_result();
        $db->close();

        if ($result->num_rows > 0) {
            return true;
        }

        return false;
    }

    public function getSameIp(string $name, string $ip): array
    {
        $db = SQL::connection();
        $select = $db->prepare("SELECT * FROM `users_goldrush` WHERE `ipv4` = ? AND `pseudo` != ?;");
        $ip = $db->real_escape_string($ip);
        $select->bind_param('ss', $ip, $name);
        $select->execute();
        $result = $select->get_result();
        $db->close();

        $array = [];
        while ($row = $result->fetch_assoc()) {
            $array[] = $row;
        }

        return $array;
    }

}