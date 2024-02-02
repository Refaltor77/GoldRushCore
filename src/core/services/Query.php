<?php

namespace core\services;

use Exception;

class Query {

    public static function query(string $host, int $port, int $timeout = 4) {
        $socket = @fsockopen('udp://'.$host, $port, $errno, $errstr, $timeout);

        if($errno and $socket !== false) {
            fclose($socket);
            throw new Exception($errstr, $errno);
        }elseif($socket === false) {
            throw new \Exception($errstr, $errno);
        }

        stream_Set_Timeout($socket, $timeout);
        stream_Set_Blocking($socket, true);

        // hardcoded magic https://github.com/facebookarchive/RakNet/blob/1a169895a900c9fc4841c556e16514182b75faf8/Source/RakPeer.cpp#L135
        $OFFLINE_MESSAGE_DATA_ID = \pack('c*', 0x00, 0xFF, 0xFF, 0x00, 0xFE, 0xFE, 0xFE, 0xFE, 0xFD, 0xFD, 0xFD, 0xFD, 0x12, 0x34, 0x56, 0x78);
        $command = \pack('cQ', 0x01, time()); // DefaultMessageIDTypes::ID_UNCONNECTED_PING + 64bit current time
        $command .= $OFFLINE_MESSAGE_DATA_ID;
        $command .= \pack('Q', 2); // 64bit guid
        $length = \strlen($command);



        $data = fread($socket, 4096);

        fclose($socket);



        $data = \substr($data, 35);

        $data = \explode(';', $data);

        return [
            'GameName' => $data[0] ?? null,
            'HostName' => $data[1] ?? null,
            'Protocol' => $data[2] ?? null,
            'Version' => $data[3] ?? null,
            'Players' => $data[4] ?? null,
            'MaxPlayers' => $data[5] ?? null,
            'ServerId' => $data[6] ?? null,
            'Map' => $data[7] ?? null,
            'GameMode' => $data[8] ?? null,
            'NintendoLimited' => $data[9] ?? null,
            'IPv4Port' => $data[10] ?? null,
            'IPv6Port' => $data[11] ?? null,
            'Extra' => $data[12] ?? null,
        ];
    }
}