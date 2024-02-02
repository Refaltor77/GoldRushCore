<?php

namespace core\managers\ranks;


use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\managers\sync\SyncTypes;
use core\player\CustomPlayer;
use core\sql\SQL;
use core\traits\UtilsTrait;
use core\unicodes\RankUnicodes;
use mysqli;
use pocketmine\player\Player;

class RankManager extends Manager
{
    use UtilsTrait;



    const RANK = [
        'PLAYER' => [
            'priority' => 0,
            'unicode' => "",
            'color' => '§7',
            'initial' => "P",
            'chat_format' =>  RankUnicodes::PLAYER . ' §7{faction} §f{pseudo} §l§7»§r §7{msg}',
            'perms' => [

            ]
        ],
        'FARMER' => [
            'priority' => 1,
            'unicode' => "",
            'color' => '§7',
            'initial' => "P",
            'chat_format' =>  '§a[Farmer] §7{faction} §f{pseudo} §l§7»§r §7{msg}',
            'perms' => [
                'xpbottle.use',
                'sell.all.use',
            ]
        ],
        'YOUTUBER' => [
            'priority' => 2,
            'unicode' => "",
            'color' => '§7',
            'initial' => "P",
            'chat_format' =>  '§f[§cYoutu§fbeur§f] §7{faction} §f{pseudo} §l§7»§r §7{msg}',
            'perms' => [

            ]
        ],
        'BOOST' => [
            'priority' => 3,
            'color' => '§7',
            'initial' => "P",
            'unicode' => '',
            'chat_format' => RankUnicodes::BOOST . ' §7{faction} §f{pseudo} §l§d»§r §f{msg}',
            'perms' => [

            ]
        ],
        'BANDIT' => [
            'priority' => 4,
            'color' => '§9',
            'initial' => "B",
            'unicode' => '',
            'chat_format' => RankUnicodes::BANDIT . ' §7{faction} §f{pseudo} §l§8»§r §f{msg}',
            'perms' => [
                'sell.use',
                'ec.use',
                'furnace.use',
                'xpbottle.use',
                'nv.use'
            ]
        ],
        'BRAQUEUR' => [
            'priority' => 5,
            'color' => '§9',
            'initial' => "B",
            'unicode' => '',
            'chat_format' => RankUnicodes::BRAQUEUR . ' §7{faction} §f{pseudo} §l§8»§r §f{msg}',
            'perms' => [
                'repair.use',
                'sell.all.use',
                'sell.use',
                'ec.use',
                'furnace.use',
                'xpbottle.use',
                'nv.use'
            ]
        ],
        'COWBOY' => [
            'priority' => 6,
            'color' => '§6',
            'initial' => "S",
            'unicode' => '',
            'chat_format' => RankUnicodes::COWBOY . ' §7{faction} §f{pseudo} §l§e»§r §f{msg}',
            'perms' => [
                'repair.use',
                'sell.all.use',
                'sell.use',
                'feed.use',
                'ec.use',
                'craft.use',
                'top.use',
                'repair.all.use',
                'rename.use',
                'furnace.use',
                'xpbottle.use',
                'nv.use'
            ]
        ],
        'MARSHALL' => [
            'priority' => 7,
            'color' => '§6',
            'initial' => "S",
            'unicode' => '',
            'chat_format' => RankUnicodes::MARSHALL . ' §7{faction} §f{pseudo} §l§d»§r §f{msg}',
            'perms' => [
                'repair.use',
                'repair.all.use',
                'sell.all.use',
                'feed.use',
                'sell.use',
                'ec.use',
                'craft.use',
                'top.use',
                'rename.use',
                'furnace.use',
                'furnace.all.use',
                'xpbottle.use',
                'nv.use'
            ]
        ],
        'SHERIF' => [
            'priority' => 8,
            'color' => '§6',
            'initial' => "S",
            'unicode' => '',
            'chat_format' => RankUnicodes::SHERIF . ' §7{faction} §f{pseudo} §l§e»§r §f{msg}',
            'perms' => [
                'repair.use',
                'repair.all.use',
                'sell.all.use',
                'feed.use',
                'sell.use',
                'ec.use',
                'craft.use',
                'top.use',
                'rename.use',
                'furnace.use',
                'furnace.all.use',
                'xpbottle.use',
                'nv.use'
            ]
        ],
        'AMIS' => [
            'priority' => 9,
            'color' => '§a',
            'initial' => "A",
            'unicode' => '',
            'chat_format' => '§f[§aAmi§f] §7{faction} §f{pseudo} §l§a»§r §7{msg}',
            'perms' => [
                'chat.delay.use',
            ]
        ],
        'ANIMATEUR' => [
            'priority' => 10,
            'color' => '§5',
            'initial' => "A",
            'unicode' => '',
            'chat_format' => ' §7{faction} §f{pseudo} §l§5»§r §5{msg}',
            'perms' => [
                'chat.delay.use',
                'money.use',
                'area.use',

                'mute.use',
                'unmute.use',
                'warn.use',
                'text.use',
                'core.command.giveall',

                'core.command.addwarp',
                'core.command.delwarp',
                'clearlagg.use',
            ]
        ],
        'GUIDE' => [
            'priority' => 11,
            'color' => '§a',
            'initial' => "G",
            'unicode' => '',
            'chat_format' => RankUnicodes::HELPER . ' §7{faction} §f{pseudo} §l§a»§r §a{msg}',
            'perms' => [
                'chat.delay.use',
                'mute.use',
                'unmute.use',
                'warn.use',
                'pocketmine.command.kick'
            ]
        ],
        'MODERATEUR' => [
            'priority' => 12,
            'color' => '§3',
            'initial' => "M",
            'unicode' => '',
            'chat_format' => RankUnicodes::MODO . ' §f{pseudo} §l§9»§r §9{msg}',
            'perms' => [
                'clearlag.use',
                'chat.delay.use',
                'ban.use',
                'adminjob.use',
                'mute.use',
                'freeze.use',
                'unmute.use',
                'warn.use',
                'topluck.use',
                'staff.use',
                'clearlagg.use',
                'pocketmine.command.kick'
            ]
        ],
        'SUPER_MODERATEUR' => [
            'priority' => 13,
            'color' => '§1',
            'initial' => "M+",
            'unicode' => '',
            'chat_format' => RankUnicodes::SUPER_MODO . ' §f{pseudo} §l§6»§r §6{msg}',
            'perms' => [
                'chat.delay.use',
                'money.use',
                'ban.use',
                'unban.use',
                'mute.use',
                'unmute.use',
                'warn.use',
                'core.command.topluck',
                'clearlagg.use',
                'rank.use',
                'staff.use',
                'pocketmine.command.kick'
            ]
        ],
        'RESPONSABLE' => [
            'priority' => 14,
            'color' => '§b',
            'initial' => "R",
            'unicode' => '',
            'chat_format' => '§7[§2Résponsable§r§7] §f{pseudo} §l§3»§r §3{msg}',
            'perms' => [
                'chat.delay.use',
                'money.use',
                'area.use',
                'ban.use',
                'unban.use',
                'mute.use',
                'unmute.use',
                'warn.use',
                'text.use',
                'core.command.topluck',
                'core.command.giveall',
                'core.command.fossilanalyseur',
                'core.command.addwarp',
                'core.command.delwarp',
                'clearlagg.use',
                'rank.use',
                'staff.use'
            ]
        ],
        'DEVELOPPEUR' => [
            'priority' => 15,
            'color' => '§d',
            'initial' => "D",
            'unicode' => '',
            'chat_format' => '§f[§dDev§f] §f{pseudo} §l§d»§r §f{msg}',
            'perms' => [
                'chat.delay.use',
            ]
        ],
        'SUPER_DEVELOPPEUR' => [
            'priority' => 16,
            'color' => '§e',
            'initial' => "D+",
            'chat_format' => '§4[§6D§ee§av§b+§d] §f{pseudo} §l§5»§r §f{msg}',
            'perms' => [
                'money.use',
                'freeze.use',
                'clearlag.use',
                'ban.use',
                'unban.use',
                'mute.use',
                'unmute.use',
                'tp.use',
                'vanish.use',
                'chat.delay.use',
            ]
        ],
        "ADMINISTRATEUR" => [
            'priority' => 17,
            'color' => '§4',
            'initial' => "A",
            'unicode' => '',
            'chat_format' => RankUnicodes::ADMIN . ' §f{pseudo} §l§c»§r §c{msg}',
            'perms' => [
                'money.use',
                'freeze.use',
                'clearlag.use',
                'ban.use',
                'unban.use',
                'mute.use',
                'unmute.use',
                'tp.use',
                'vanish.use',
                'chat.delay.use',
            ]
        ]

    ];
    public static array $fastCache = [];
    private Main $plugin;
    private static array $globalCache = [];
    private static array $attachment = [];
    private static array $perms = [];

    public function __construct(Main $plugin)
    {
        $this->plugin = Main::getInstance();
        $db = SQL::connection();
        $db->prepare("CREATE TABLE IF NOT EXISTS `rank` (`xuid` VARCHAR(255) PRIMARY KEY, `rank` TEXT);")->execute();
        $select = $db->prepare("SELECT * FROM `rank`;");
        $select->execute();
        $result = $select->get_result();
        while ($array = $result->fetch_array(1)) {
            self::$globalCache[$array['xuid']] = unserialize(base64_decode($array['rank']));
        }
        $db->close();

        parent::__construct($plugin);
    }

    public function getUnicodeRank(string $rank): string {
        return self::RANK[$rank]['unicode'];
    }

    public function convertStringToRANK(string $rank): string
    {
        foreach (self::CONVERSION as $RANK => $string) {
            if (strtolower($rank) === strtolower($string)) return $RANK;
        }
        return '404';
    }

    public function convertRankToString(string $RANK): string
    {
        return self::CONVERSION[$RANK] ?? '404';
    }


    // Priority rank 0 -> 9

    public function hasRank(string $xuid, string $rank): bool
    {
        if (isset(self::$fastCache[$xuid])) {
            return in_array($rank, self::$fastCache[$xuid]);
        } elseif (isset(self::$globalCache[$xuid])) {
            return in_array($rank, self::$globalCache[$xuid]);
        } else return false;
    }

    public function getFormatChat(string $rank): string
    {
        return self::RANK[$rank]['chat_format'];
    }

    public function addPerms(string $xuid, string $perm)
    {
        self::$perms[$xuid][] = $perm;
        $player = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
        if ($player instanceof CustomPlayer) {
            Main::getInstance()->getDatabaseSyncManager()->addPlayerQueue($player, SyncTypes::RANKS);
        }
    }

    public function addRank(string $xuid, string $rank): void
    {
        if ($this->hasRank($xuid, $rank)) return;

        if (isset(self::$fastCache[$xuid])) {
            self::$fastCache[$xuid][] = $rank;
        } elseif (isset(self::$globalCache[$xuid])) {
            self::$globalCache[$xuid][] = $rank;
            $ranks = base64_encode(serialize(self::$globalCache[$xuid]));


            SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid, $ranks) {
                $xuid = $db->real_escape_string($xuid);
                $rank = $db->real_escape_string($ranks);
                $delete = $db->prepare("DELETE FROM `rank` WHERE `xuid` = ?;");
                $delete->bind_param('s', $xuid);
                $delete->execute();
                $insert = $db->prepare("INSERT INTO `rank` (`xuid`, `rank`) VALUES (?, ?);");
                $insert->bind_param('ss', $xuid, $rank);
                $insert->execute();
            });
        }
    }

    public function removeRank(string $xuid, string $rank): void
    {
        if (isset(self::$fastCache[$xuid])) {
            unset(self::$fastCache[$xuid][array_search($rank, self::$fastCache[$xuid])]);
        } elseif (isset(self::$globalCache[$xuid])) {
            unset(self::$globalCache[$xuid][array_search($rank, self::$globalCache[$xuid])]);
            $array = SQL::informations();
            $ranks = base64_encode(serialize(self::$globalCache[$xuid]));
            SQL::async(static function () use ($array, $xuid, $ranks) {
                $db = new mysqli(
                    $array['hostname'],
                    $array['username'],
                    $array['password'],
                    $array['database'],
                    $array['port']
                );

                $xuid = $db->real_escape_string($xuid);
                $rank = $db->real_escape_string($ranks);
                $delete = $db->prepare("DELETE FROM `rank` WHERE `xuid` = ?;");
                $delete->bind_param('s', $xuid);
                $delete->execute();
                $insert = $db->prepare("INSERT INTO `rank` (`xuid`, `rank`) VALUES (?, ?);");
                $insert->bind_param('ss', $xuid, $rank);
                $insert->execute();

                $db->close();
            });
        }
    }

    public function removePerms(string $xuid, string $perm)
    {
        unset(self::$perms[$xuid][array_search($perm, self::$perms[$xuid])]);
        $player = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
        if ($player instanceof CustomPlayer) {
            Main::getInstance()->getDatabaseSyncManager()->addPlayerQueue($player, SyncTypes::RANKS);
        }
    }

    public function hasPerms(string $xuid, string $perm)
    {
        return in_array($perm, self::$perms[$xuid]);
    }

    public function getSupremeRankPriority(string $xuid): string
    {
        $priority = 0;
        $return = 'PLAYER';
        foreach ($this->getRanks($xuid) as $rank) {
            $i = $this->getRankPriority($rank);
            if ($i >= $priority) {
                if (!in_array($rank, [
                    "GUIDE", "MODERATEUR", "SUPER_MODERATEUR", "ADMINISTRATEUR"
                ])) {
                    $return = $rank;
                    $priority = $i;
                }
            }
        }
        return $return;
    }


    public function getSupremeRankPriorityChat(string $xuid): string
    {
        $priority = 0;
        $return = 'PLAYER';
        foreach ($this->getRanks($xuid) as $rank) {
            $i = $this->getRankPriority($rank);
            if ($i >= $priority) {
                $return = $rank;
                $priority = $i;
            }
        }
        return $return;
    }


    const CONVERSION = [
        'PLAYER' => 'Pionnier',
        'BANDIT' => 'Bandit',
        'BRAQUEUR' => 'BRAQUEUR',
        'COWBOY' => 'Cowboy',
        'MARSHALL' => 'Marshall',
        'SHERIF' => 'Shérif',
        'AMIS' => 'Amis',
        'ANIMATEUR' => 'Animateur',
        'GUIDE' => 'Guide',
        'MODERATEUR' => 'Modérateur',
        'SUPER_MODERATEUR' => 'Super Modérateur',
        'DEVELOPPEUR' => 'Développeur',
        'RESPONSABLE' => 'Résponsable',
        'SUPER_DEVELOPPEUR' => 'Super Développeur',
        "ADMINISTRATEUR" => "Administrateur",
        "BOOST" => "Boost",
        "FARMER" => "Farmer",
        "YOUTUBER"=> "Youtubeur"
    ];


    public function getUnicode(string $rank): string {
        switch ($rank) {
            case 'PLAYER': return RankUnicodes::PLAYER;
            case 'BANDIT': return RankUnicodes::BANDIT;
            case 'BRAQUEUR': return RankUnicodes::BRAQUEUR;
            case 'COWBOY': return RankUnicodes::COWBOY;
            case 'MARSHALL': return RankUnicodes::MARSHALL;
            case 'SHERIF': return RankUnicodes::SHERIF;
            case 'AMIS': return "[Ami]";
            case 'ANIMATEUR': return "[Animateur]";
            case 'GUIDE': return RankUnicodes::HELPER;
            case 'MODERATEUR': return RankUnicodes::MODO;
            case 'SUPER_MODERATEUR': return RankUnicodes::SUPER_MODO;
            case 'RESPONSABLE': return "[Responsable]";
            case 'SUPER_DEVELOPPEUR': return "[SuperDev]";
            case 'ADMINISTRATEUR': return RankUnicodes::ADMIN;
            case 'BOOST': return RankUnicodes::BOOST;
            case 'FARMER': return "§a[Farmer]";
            case 'YOUTUBER': return RankUnicodes::YOUTUBE;
        }

        return "";
    }

    public function getRanks(string $xuid): array
    {
        if (isset(self::$fastCache[$xuid])) return self::$fastCache[$xuid];
        if (isset(self::$globalCache[$xuid])) return self::$globalCache[$xuid];
        return [];
    }

    public function getRankPriority(string $rank): int
    {
        if ($this->existGrade($rank)) {
            return self::RANK[$rank]['priority'];
        }
        return 0;
    }

    public function existGrade(string $rank): bool
    {
        return isset(self::RANK[$rank]);
    }

    public function getColorRank(string $rank): string
    {
        return self::RANK[$rank]['color'];
    }

    public function getInitialRank(string $rank): string
    {
        return self::RANK[$rank]['initial'];
    }

    public function loadData(Player $player): void
    {
        $xuid = $player->getXuid();
        $array = SQL::informations();

        SQL::async(static function (RequestAsync $thread) use ($xuid, $array) {
            $db = new mysqli(
                $array['hostname'],
                $array['username'],
                $array['password'],
                $array['database'],
                $array['port']
            );

            $select = $db->prepare("SELECT `rank` FROM `rank` WHERE `xuid` = ?;");
            $xuid = $db->real_escape_string($xuid);
            $select->bind_param('s', $xuid);
            $select->execute();
            $base = ['PLAYER'];
            $result = $select->get_result();
            if ($result->num_rows > 0) {
                $base = unserialize(base64_decode($result->fetch_assoc()['rank']));
            }
            $db->close();
            $thread->setResult($base);
        }, static function (RequestAsync $thread) use ($xuid, $player) {
            $result = $thread->getResult();
            self::$fastCache[$xuid] = $result;
            self::$globalCache[$xuid] = $result;
            self::$attachment[$xuid] = [];
            self::$perms[$xuid] = [];


            if ($player->isConnected()) {
                $player->hasRankLoaded = true;
                Main::getInstance()->getRankManager()->refreshPerm($player);
            }
        });
    }



    public function refreshPerm(Player $player): void
    {
        if (isset(self::$attachment[$player->getXuid()])) {
            foreach (self::$attachment[$player->getXuid()] as $att) {
                $player->removeAttachment($att);
            }
        } else self::$attachment[$player->getXuid()] = [];

        foreach ($this->getRanks($player->getXuid()) as $rankName) {
            foreach ($this->getPermRank($rankName) as $permString) {
                self::$attachment[$player->getXuid()][] = $player->addAttachment($this->getPlugin(), $permString, true);
            }
        }
    }

    public function getPermRank(string $rank): array
    {
        if (isset(self::RANK[$rank])) return self::RANK[$rank]['perms'];
        return [];
    }


    public function saveData(Player $player, bool $async = true): void
    {
        $xuid = $player->getXuid();
        if (isset(self::$fastCache[$xuid])) {

            $ranks = base64_encode(serialize(self::$fastCache[$xuid]));
            if ($async) {
                SQL::async(static function (RequestAsync $async, mysqli $db) use ($xuid, $ranks) {
                    $xuid = $db->real_escape_string($xuid);
                    $rank = $db->real_escape_string($ranks);
                    $insert = $db->prepare("INSERT INTO `rank` (`xuid`, `rank`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `rank` = VALUES(`rank`);");
                    $insert->bind_param('ss', $xuid, $rank);
                    $insert->execute();
                }, static function (RequestAsync $async) use ($xuid, $player): void {
                    if ($player->isConnected()) return;
                    unset(self::$fastCache[$xuid]);
                    unset(self::$perms[$xuid]);
                    unset(self::$attachment[$xuid]);
                });
            } else {
                $db = SQL::connection();
                $xuid = $db->real_escape_string($xuid);
                $rank = $db->real_escape_string($ranks);
                $insert = $db->prepare("INSERT INTO `rank` (`xuid`, `rank`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `rank` = VALUES(`rank`);");
                $insert->bind_param('ss', $xuid, $rank);
                $insert->execute();
                $db->close();
            }
        }
    }


    public function saveAllData(): void
    {
        $db = SQL::connection();
        foreach (self::$fastCache as $xuid => $rank) {
            $xuid = $db->real_escape_string($xuid);
            $rank = $db->real_escape_string(base64_encode(serialize($rank)));
            $insert = $db->prepare("INSERT INTO `rank` (`xuid`, `rank`) VALUES (?, ?);");
            $insert->bind_param('ss', $xuid, $rank);
            $insert->execute();
        }
        $db->close();
    }

}