<?php

namespace core\managers\factions;

use core\async\RequestAsync;
use core\events\LogEvent;
use core\Main;
use core\managers\Manager;
use core\messages\Prefix;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\sql\SQL;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;

class FactionManager extends Manager
{
    const TYPE = [
        'FARMEUR',
        'BUCHERON',
        'ASSASSIN',
        'CUISINIER',
        'MINEUR',
        'CHIMISTE',
        'HUNTER',
        'FORGERON'
    ];

    const OWNER = 'Owner';
    const OFFICIER = 'Officier';
    const MEMBER = 'Membre';
    const RECRUE = 'Recrue';
    public array $fastCacheFaction = [];
    public array $fastCacheClaims = [];
    public array $fastCacheFactionsBank = [];
    private Main $plugin;
    private array $invitation = [];

    private array $fastCachePlayerFactionNotSaveMySQL = [];



    // 4 mois de quest hebdo
    const QUEST = [
        'minecraft:oak_log',
        'minecraft:spruce_log',
        'minecraft:birch_log',
        'minecraft:jungle_log',
        Ids::COPPER_RAW,
        Ids::EMERALD_INGOT,
        Ids::AMETHYST_INGOT,
        Ids::PLATINUM_RAW,
        Ids::GOLD_POWDER,
        'minecraft:melon',
        'minecraft:pumpkin',
        'minecraft:carrot',
        'minecraft:wheat',
        'minecraft:potatoes',
        'minecraft:sugarcane',
        Ids::RAISIN,
        Ids::BERRY_BLUE,
        Ids::BERRY_BLACK,
        Ids::BERRY_YELLOW,
        Ids::BERRY_PINK
    ];


    const QUEST_TEXTURE = [
        'minecraft:oak_log' => '/blocks/log_oak',
        'minecraft:spruce_log' => '/blocks/log_spruce',
        'minecraft:birch_log' => '/blocks/log_birch',
        'minecraft:jungle_log' => '/blocks/log_jungle',
        Ids::COPPER_RAW => '/items/copper_raw',
        Ids::EMERALD_INGOT => '/items/emerald_raw',
        Ids::AMETHYST_INGOT => '/items/amethyst',
        Ids::PLATINUM_RAW => '/items/platinum_raw',
        Ids::GOLD_POWDER => '/blocks/gold_powder',
        'minecraft:melon' => '/items/melon',
        'minecraft:pumpkin' => '/blocks/pumkin_side',
        'minecraft:carrot' => '/items/carrot',
        'minecraft:wheat' => '/items/wheat',
        'minecraft:potatoes' => '/items/potato',
        'minecraft:sugarcane' => '/items/reeds',
        Ids::RAISIN => '/blocks/goldrush_grape',
        Ids::BERRY_BLUE => '/blocks/sweet_berries_blue',
        Ids::BERRY_BLACK => '/blocks/berry_black',
        Ids::BERRY_YELLOW => '/blocks/berry_yellow',
        Ids::BERRY_PINK => '/blocks/berry_pink',
    ];


    public string $questToday = "";
    public array $questCacheFaction = [];
    public array $powerPlayers = [];



    public function __construct(Main $plugin)
    {
        $this->plugin = Main::getInstance();
        $db = SQL::connection();




        $db->query("CREATE TABLE IF NOT EXISTS `wype_power` (hasWype VARCHAR(255), mois INT);");




        $db->query("CREATE TABLE IF NOT EXISTS `factions_bank` (faction VARCHAR(255) PRIMARY KEY, money INT, data LONGTEXT)");
        $db->query("CREATE TABLE IF NOT EXISTS `quest_hebdo_realized` (item_string_ids VARCHAR(255));");
        $db->query("CREATE TABLE IF NOT EXISTS `quest_today` (item_string_ids VARCHAR(255));");
        $db->query("CREATE TABLE IF NOT EXISTS `quest_hebdo` (faction VARCHAR(255) PRIMARY KEY, item_number INT, data LONGTEXT);");
        $db->query("CREATE TABLE IF NOT EXISTS `mails_factions` (id INT, name VARCHAR(255), msg TEXT, faction_name_sender VARCHAR(255), lue INT);");

        $db->prepare("CREATE TABLE IF NOT EXISTS `factions` (
    `xuidOwner` VARCHAR(255),
    `name` VARCHAR(255) PRIMARY KEY,
    `power` INT,
    `money` INT,
    `members` TEXT,
    `kill` INT,
    `xp` INT,
    `home` VARCHAR(255),
    `bio` TEXT,
    `visibility` INT
);")->execute();


        $query = $db->query("SELECT * FROM quest_hebdo;");
        while ($row = $query->fetch_assoc()) {
            if (!is_null($row)) {
                $this->questCacheFaction[$row['faction']] = [
                    'item_number' => $row['item_number'],
                    'members' => unserialize(base64_decode($row['data'])),
                ];
            }
        }


        $query = $db->query("SELECT * FROM quest_today;")->fetch_all(MYSQLI_ASSOC);
        if (isset($query[0])) {
            $this->questToday = $query[0]['item_string_ids'];
        }


        $db->prepare("CREATE TABLE IF NOT EXISTS `players_power` (xuid VARCHAR(255) PRIMARY KEY, power INT)")->execute();
        $query = $db->query("SELECT * FROM players_power;");
        while ($row = $query->fetch_assoc()) {
            $this->powerPlayers[$row['xuid']] = $row['power'];
        }

        $db->prepare("CREATE TABLE IF NOT EXISTS `claims` (`factionName` VARCHAR(255), `pos` VARCHAR(255));")->execute();

        $selectAllFactionPrepare = $db->prepare("SELECT * FROM `factions`;");
        $selectAllFactionPrepare->execute();
        $selectAllFactionResult = $selectAllFactionPrepare->get_result();

        while ($array = $selectAllFactionResult->fetch_array(1)) {
            $allMembers = unserialize(base64_decode($array['members']));
            if ($array['home'] === 'null') $array['home'] = null;
            $this->fastCacheFaction[$array['name']] = [
                'xuidOwner' => $array['xuidOwner'],
                'power' => $array['power'],
                'money' => $array['money'],
                'members' => $allMembers,
                'kill' => $array['kill'],
                'xp' => $array['xp'],
                'home' => $array['home'],
                'bio' => $array['bio'],
                'visibility' => $array['visibility']
            ];
            $this->fastCachePlayerFactionNotSaveMySQL[$array['xuidOwner']] = $array['name'];
            foreach ($allMembers as $xuid => $rank) {
                $this->fastCachePlayerFactionNotSaveMySQL[$xuid] = $array['name'];
            }
        }

        $selectAllClaimsPrepare = $db->prepare("SELECT * FROM `claims`;");
        $selectAllClaimsPrepare->execute();
        $selectAllClaimsResult = $selectAllClaimsPrepare->get_result();

        while ($array = $selectAllClaimsResult->fetch_array(1)) {
            $this->fastCacheClaims[$array['pos']] = $array['factionName'];
        }



        $query = $db->query("SELECT * FROM factions_bank;");

        while ($row = $query->fetch_assoc()) {
            $this->fastCacheFactionsBank[$row['faction']] = [
                'money' => $row['money'],
                'members' => unserialize(base64_decode($row['data']))
            ];
        }


        $db->close();


        if ($this->questToday === "") {
            $this->setNewQuestHebdo();
        }
        parent::__construct($plugin);
    }


    public function setNewQuestHebdo(): void {
        $items = self::QUEST;

        $itemToday = $this->questToday;


        SQL::async(static function (RequestAsync $async, \mysqli $db) use ($items, $itemToday) : void {


            if ($itemToday !== "") {
                $db->query("INSERT INTO quest_hebdo_realized (item_string_ids) VALUES ('$itemToday');");
            }

            $query = $db->query("SELECT * FROM quest_hebdo_realized;")->fetch_all(MYSQLI_ASSOC);
            foreach ($query as $row => $values) {
                if (in_array($values['item_string_ids'], $items)) {
                    unset($items[array_search($values['item_string_ids'], $items)]);
                }
            }
            $item = $items[array_rand($items)];
            $async->setResult($item);
        }, static function(RequestAsync $async): void {
            $itemStringId = $async->getResult();
            Main::getInstance()->getFactionManager()->questToday = (string)$itemStringId;

            foreach (Main::getInstance()->getFactionManager()->questCacheFaction as $factionName => $values) {
                Main::getInstance()->getFactionManager()->questCacheFaction[$factionName]['item_id'] = $itemStringId;
                Main::getInstance()->getFactionManager()->questCacheFaction[$factionName]['item_number'] = 0;

                foreach ($values['members'] as $xuid => $itemNumber) {
                    Main::getInstance()->getFactionManager()->questCacheFaction[$factionName]['members'][$xuid] = 0;
                }
            }

            Main::getInstance()->getFactionManager()->saveAllDataAsync();
        });
    }


    public function sendRecompenseFactionQuest(): void {
        $arrayQueried = [];
        foreach ($this->questCacheFaction as $factionName => $values) {
            $arrayQueried[$factionName] = $values['item_number'];
        }

        arsort($arrayQueried);

        $i = 1;
        foreach ($arrayQueried as $factionName => $itemNumber) {
            if ($i  === 4) break;

            $giftData = match ($i) {
                1 => [
                    'power' => 300,
                    'money' => 300000
                ],
                2 => [
                    'power' => 200,
                    'money' => 200000
                ],
                3 => [
                    'power' => 100,
                    'money' => 100000
                ],
            };

            $this->addMoneyFactionBank($factionName, $giftData['money']);



            $arrayMembers = $this->questCacheFaction[$factionName]['members'];
            $arrayMembersActive = [];
            foreach ($arrayMembers as $xuid => $itemNumberPlayer) {
                if ($itemNumberPlayer > 0) $arrayMembersActive[] = $xuid;
            }

            $power = $giftData['power'];
            $power = floor($power / (count($arrayMembersActive) === 0 ? 1 : count($arrayMembersActive)));
            foreach ($arrayMembersActive as $xuid) {
                $this->addPowerPlayerXuid($factionName, $power, $xuid);
            }

            $i++;
        }
    }


    public function getItemQuest(): Item {
        $itemIdString = $this->questToday;
        if (str_contains($itemIdString, 'goldrush:')) {
            $item = CustomiesItemFactory::getInstance()->get($itemIdString);
        }else {
            $item = StringToItemParser::getInstance()->parse($itemIdString);
        }

        return $item;
    }


    public function addItemQuestFaction(Player $player, int $number): void {
        $faction = $this->getFactionName($player->getXuid());


        if (!isset($this->questCacheFaction[$faction])) {
            $this->questCacheFaction[$faction] = [
                'item_number' => 0,
                'members' => []
            ];
        }


        $this->questCacheFaction[$faction]['item_number'] += $number;
        if (!isset($this->questCacheFaction[$faction]['members'][$player->getXuid()])) {
            $this->questCacheFaction[$faction]['members'][$player->getXuid()] = $number;
        } else $this->questCacheFaction[$faction]['members'][$player->getXuid()] += $number;
    }

    public function getItemNumberFactionQuestPlayer(Player $player): int {
        return $this->questCacheFaction[$this->getFactionName($player->getXuid())]['members'][$player->getXuid()] ?? 0;
    }

    public function getItemNumberFactionQuest(string $faction): int {
        return $this->questCacheFaction[$faction]['item_number'] ?? 0;
    }

    public function wypePower(): void {
        foreach ($this->fastCacheFaction as $name => $values) {
            $this->fastCacheFaction[$name]['power'] = 0;
        }
    }

    // function for delete faction
    public function deleteMailsFaction(string $factionName): void {
        SQL::async(static function (RequestAsync $async, \mysqli $db) use ($factionName): void  {
            $prepare = $db->prepare("DELETE FROM mails_factions WHERE name = ?;");
            $prepare->bind_param('s', $factionName);
            $prepare->execute();
            $prepare->close();
        });
    }

    // function for delete faction
    public function deleteQuestFaction(string $factionName): void {
        SQL::async(static function (RequestAsync $async, \mysqli $db) use ($factionName): void  {
            $prepare = $db->prepare("DELETE FROM quest_hebdo WHERE faction = ?;");
            $prepare->bind_param('s', $factionName);
            $prepare->execute();
            $prepare->close();
        });
    }


    public function sendEmails(string $factionName, string $msg, string $factionNameSender): void {
        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($factionName, $msg, $factionNameSender) : void {
            $prepare = $db->prepare("INSERT INTO mails_factions (id, name, msg, faction_name_sender, lue) VALUES (?, ?, ?, ?, ?);");
            $id = uniqid();
            $lue = 0;
            $prepare->bind_param('isssi', $id, $factionName, $msg, $factionNameSender, $lue);
            $prepare->execute();
            $prepare->close();
        });
    }


    public function hasNotifFaction(Player $player, callable $callback): void {


        if (!$this->isInFaction($player->getXuid())) {
            $callback(false);
            return;
        }
        $factionName = $this->getFactionName($player->getXuid());

        if ($this->getRankMember($player->getXuid(), $factionName) === self::RECRUE) {
            $callback(false);
            return;
        }



        SQL::async(static function (RequestAsync $async, \mysqli $db) use ($factionName): void {
            $prepare = $db->prepare("SELECT * FROM mails_factions WHERE name = ?;");
            $prepare->bind_param('s', $factionName);
            $prepare->execute();
            $result = $prepare->get_result();
            $result = $result->fetch_all(MYSQLI_ASSOC);

            $mails = [];
            $hasNotif = false;
            foreach ($result as $index => $values) {
                if ($values['lue'] === 0) $hasNotif = true;
                break;
            }

            $async->setResult($hasNotif);
        }, static function(RequestAsync $async) use ($callback): void {
            $callback($async->getResult());
        });
    }


    public function getMailsFactions(string $factions, callable $callback): void {
        SQL::async(static function (RequestAsync $async, \mysqli $db) use ($factions): void {
            $prepare = $db->prepare("SELECT * FROM mails_factions WHERE name = ?;");
            $prepare->bind_param('s', $factions);
            $prepare->execute();
            $result = $prepare->get_result();
            $result = $result->fetch_all(MYSQLI_ASSOC);

            $mails = [];
            foreach ($result as $index => $values) {
                $mails[$values['id']] = [
                    'faction_name_sender' => $values['faction_name_sender'],
                    'msg' => $values['msg'],
                    'id' => $values['id'],
                    'lue' => ($values['lue'] === 1 ? true : false)
                ];
            }

            $async->setResult($mails);
        }, static function(RequestAsync $async) use ($callback): void {
            $callback($async->getResult());
        });
    }



    public function setEmailLue(int $id): void {
        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($id): void {
            $db->query("UPDATE mails_factions SET lue = 1 WHERE id = $id;");
        });
    }



    public function transferFaction(Player $leader, Player $newLeader, string $factionName): void
    {
        $this->fastCacheFaction[$factionName]['xuidOwner'] = $newLeader->getXuid();
        $this->addMemberFaction($leader->getXuid(), $factionName);
        $this->setRankMember($leader->getXuid(), self::OFFICIER);
        $this->setRankMember($newLeader->getXuid(), self::OWNER);

        foreach ($this->getMembersFaction($factionName) as $xuid => $rank) {
            $player = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
            if (!is_null($player)) {
                if ($player->getXuid() !== $newLeader->getXuid()) $player->sendMessage(Prefix::PREFIX_GOOD . "§aVotre nouveau chef de faction est §f{$newLeader->getName()}§a !");
            }
        }
        $this->saveAllDataAsync();
    }

    public function addMemberFaction(string $xuid, string $faction): void
    {
        $this->fastCachePlayerFactionNotSaveMySQL[$xuid] = $faction;
        $this->fastCacheFaction[$faction]['members'][$xuid] = 'Recrue';
        $this->saveAllDataAsync();
    }

    public function setRankMember(string $xuid, $rank): void
    {
        $this->fastCacheFaction[$this->getFactionName($xuid)]['members'][$xuid] = $rank;
        $this->saveAllDataAsync();
    }

    public function getConnectedInt(string $factionName): int {
        $onLine = 0;
        foreach ($this->fastCacheFaction[$factionName]['members'] as $xuid => $rank) {
            $player = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
            if ($player instanceof CustomPlayer) $onLine++;
        }

        $owner = $this->getXuidOwnerFaction($factionName);
        $owner = Main::getInstance()->getDataManager()->getPlayerXuid($owner);
        if ($owner instanceof CustomPlayer) $onLine++;

        return $onLine;
    }


    public function getMaxMembers(string $factionName): int {
        $onLine = 1;
        $ownerFound = false;
        foreach ($this->fastCacheFaction[$factionName]['members'] as $xuid => $rank) {
            if ($rank === self::OWNER) {
                $ownerFound = true;
            } else $onLine++;
        }
        if (!$ownerFound) {
            $xuid = $this->getXuidOwnerFaction($factionName);
            $player = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
            if ($player instanceof CustomPlayer) {
                $onLine++;
            }
        }

        return $onLine - 1;
    }

    public function getFactionName(string $xuid): string
    {
        if (isset($this->fastCachePlayerFactionNotSaveMySQL[$xuid])) {
            return $this->fastCachePlayerFactionNotSaveMySQL[$xuid];
        }
        return '...';
    }

    public function getMembersFaction(string $factionName): array
    {
        return $this->fastCacheFaction[$factionName]['members'] ?? [];
    }

    public function hasInvited(Player $player): bool
    {
        $xuid = $player->getXuid();
        if (isset($this->invitation[$xuid])) return true;
        return false;
    }

    public function getFactionInvited(Player $player): string
    {
        $xuid = $player->getXuid();
        return $this->invitation[$xuid]['faction'];
    }

    public function isTimeoutInvited(Player $player): bool
    {
        $xuid = $player->getXuid();
        $now = time();
        $inviteTime = $this->invitation[$xuid]['time'];
        if ($inviteTime <= $now) return true;
        return false;
    }

    public function getXuidLeaderInvite(Player $player): string
    {
        $xuid = $player->getXuid();
        return $this->invitation[$xuid]['owner'];
    }

    # ==== API ==== #

    public function invitePlayerInFaction(Player $player, string $factionName, string $xuidLeader): void
    {
        $xuid = $player->getXuid();
        $this->invitation[$xuid] = ['time' => time() + 180, 'faction' => $factionName, 'owner' => $xuidLeader];
    }

    public function removeMember(string $xuid, string $faction): void
    {
        unset($this->fastCachePlayerFactionNotSaveMySQL[$xuid]);
        unset($this->fastCacheFaction[$faction]['members'][$xuid]);
        if (isset($this->fastCacheFactionsBank[$faction]['members'][$xuid])) {
            unset($this->fastCacheFactionsBank[$faction]['members'][$xuid]);
        }
        if (isset($this->questCacheFaction[$faction]['members'][$xuid])) {
            unset($this->questCacheFaction[$faction]['members'][$xuid]);
        }
        $this->saveAllDataAsync();
    }

    public function acceptInvitation(Player $player): void
    {
        $xuid = $player->getXuid();
        $faction = $this->invitation[$xuid]['faction'];
        unset($this->invitation[$xuid]);
        $this->addMemberFaction($xuid, $faction);

    }

    public function denyInvitation(Player $player): void
    {
        $xuid = $player->getXuid();
        unset($this->invitation[$xuid]);
    }

    public function isInFaction(string $xuid): bool
    {
        if (isset($this->fastCachePlayerFactionNotSaveMySQL[$xuid])) return true;
        return false;
    }

    public function hasHome(string $factionName): bool
    {
        if (is_null($this->fastCacheFaction[$factionName]['home'])) return false;
        return true;
    }

    public function delhome(string $factionName): void
    {
        $this->fastCacheFaction[$factionName]['home'] = null;
        $this->saveAllDataAsync();
    }

    public function setHome(string $factionName, Position $pos): void
    {
        $hash = intval($pos->getFloorX()) . ':' . intval($pos->getY()) . ':' . intval($pos->getFloorZ()) . ':' . $pos->getWorld()->getFolderName();
        $this->fastCacheFaction[$factionName]['home'] = $hash;
        $this->saveAllDataAsync();
    }

    public function getHome(string $factionName): Position
    {
        $hashExplode = explode(':', $this->fastCacheFaction[$factionName]['home']);
        return new Position(intval($hashExplode[0]), intval($hashExplode[1]), intval($hashExplode[2]), $this->getPlugin()->getServer()->getWorldManager()->getWorldByName(strval($hashExplode[3])));

    }

    public function addMoneyFaction(string $factionName, int $amount, Player $player): void
    {
        if (isset($this->fastCacheFactionsBank[$factionName])) {
            $this->fastCacheFactionsBank[$factionName]['money'] += $amount;
            if (isset($this->fastCacheFactionsBank[$factionName]['members'][$player->getXuid()])) {
                $this->fastCacheFactionsBank[$factionName]['members'][$player->getXuid()] += $amount;
            } else $this->fastCacheFactionsBank[$factionName]['members'][$player->getXuid()] = $amount;
        } else {
            $this->fastCacheFactionsBank[$factionName]['money'] = $amount;
            if (isset($this->fastCacheFactionsBank[$factionName]['members'][$player->getXuid()])) {
                $this->fastCacheFactionsBank[$factionName]['members'][$player->getXuid()] += $amount;
            } else $this->fastCacheFactionsBank[$factionName]['members'][$player->getXuid()] = $amount;
        }
        $this->saveAllDataAsync();
    }


    public function addMoneyFactionBank(string $factionName, int $amount): void
    {
        if (isset($this->fastCacheFactionsBank[$factionName])) {
            $this->fastCacheFactionsBank[$factionName]['money'] += $amount;
        } else $this->fastCacheFactionsBank[$factionName]['money'] = $amount;
        $this->saveAllDataAsync();
    }



    public function removeMoneyFaction(string $factionName, int $amount, Player $player): void
    {
        if (isset($this->fastCacheFactionsBank[$factionName])) {
            $this->fastCacheFactionsBank[$factionName]['money'] -= $amount;
            if (isset($this->fastCacheFactionsBank[$factionName]['members'][$player->getXuid()])) {
                $this->fastCacheFactionsBank[$factionName]['members'][$player->getXuid()] -= $amount;
            }
        }
        $this->saveAllDataAsync();
    }


    public function removeMoneyFactionAll(string $factionName, int $amount): void
    {
        if (isset($this->fastCacheFactionsBank[$factionName])) {
            $this->fastCacheFactionsBank[$factionName]['money'] -= $amount;
        }
        $this->saveAllDataAsync();
    }

    public function getMoneyFaction(string $factionName): int {
        return $this->fastCacheFactionsBank[$factionName]['money'] ?? 0;
    }

    public function getMoneyPlayer(Player $player): int {
        return $this->fastCacheFactionsBank[$this->getFactionName($player->getXuid())]['members'][$player->getXuid()] ?? 0;
    }

    public function addXpFaction(string $factionName, int $amount): void
    {
        $this->fastCacheFaction[$factionName]['xp'] += $amount;
    }

    public function createFaction(Player $owner, string $factionName, string $description, int $visibility): void
    {
        $this->fastCacheFaction[$factionName] = [
            'xuidOwner' => $owner->getXuid(),
            'power' => 3,
            'money' => 0,
            'members' => [],
            'kill' => 0,
            'xp' => 0,
            'home' => null,
            'bio' => $description,
            'visibility' => $visibility
        ];

        $this->fastCachePlayerFactionNotSaveMySQL[$owner->getXuid()] = $factionName;

        foreach ($this->getPlugin()->getServer()->getOnlinePlayers() as $player) {
            if ($player->getXuid() !== $owner->getXuid()) {
                $player->sendMessage(Prefix::PREFIX_GOOD . "§eLe joueur §6" . $owner->getName() . "§e vient de créer la faction §6" . $factionName . "§e !");
            }
        }

        $this->fastCacheFactionsBank[$factionName] = [
            'money' => 0,
            'members' => [
                $owner->getXuid() => 0
            ]
        ];
        (new LogEvent($owner->getName()." a créé la faction ".$factionName, LogEvent::FACTION_TYPE))->call();
        $this->saveAllDataAsync();
    }


    public function getAllFactionByNamePrefix(string $name): array {
        $name = strtolower($name);
        $delta = PHP_INT_MAX;
        $founds = [];
        foreach($this->fastCacheFaction as $nameFac => $values){
            if(stripos($nameFac, $name) === 0){
                $curDelta = strlen($nameFac) - strlen($name);
                if($curDelta < $delta){
                    $founds[] = $nameFac;
                    $delta = $curDelta;
                }
                if($curDelta === 0){
                    break;
                }
            }
        }

        return $founds;
    }

    public function existFaction(string $factionName): bool
    {
        $factionName = strtolower($factionName);
        foreach ($this->fastCacheFaction as $faction => $value) {
            if (strtolower($factionName) === strtolower($faction)) return true;
        }
        return false;
    }

    public function isValidName(string $factionName): bool
    {
        if (strlen($factionName) >= 10) return false;
        return true;
    }

    public function getBio(string $factionName): string
    {
        foreach ($this->fastCacheFaction as $name => $values) {
            if (strtolower($factionName) === strtolower($name)) {
                return $values['bio'];
            }
        }
        return '404';
    }

    public function getAllFactionNameArgs(): array
    {
        $array = [];
        foreach ($this->fastCacheFaction as $name => $values) {
            if (str_contains(" ", $name)) {
                $name = "'" . strtolower($name) . "'";
            } else $name = strtolower($name);
            $array[$name] = $name;
        }
        return $array;
    }


    public function getAllFactionNames(): array
    {
        $array = [];
        foreach ($this->fastCacheFaction as $name => $values) {
            if (str_contains(" ", $name)) {
                $name = "'" . strtolower($name) . "'";
            } else $name = strtolower($name);
            $array[] = $name;
        }
        return $array;
    }

    public function isInClaim(Position $pos): bool
    {
        $pos = $this->hashClaimPos($pos);
        if (isset($this->fastCacheClaims[$pos])) return true;
        return false;
    }

    private function hashClaimPos(Position $pos): string
    {
        return $pos->getFloorX() >> 4 . ':' . $pos->getFloorZ() >> 4;
    }

    public function getFactionNameInClaim(Position $pos): string
    {
        $pos = $this->hashClaimPos($pos);
        return $this->fastCacheClaims[$pos];
    }

    public function getClaimCount(string $factionName): int
    {
        $i = 0;
        foreach ($this->fastCacheClaims as $pos => $name) {
            if ($name == $factionName) $i++;
        }
        return $i;
    }

    public function getPower(string $factionName): int
    {

        $members = $this->getMembersFaction($factionName);
        $power = 0;
        foreach ($members as $xuid => $rank) {
            $power += $this->powerPlayers[$xuid] ?? 0;
        }

        $owner = $this->getXuidOwnerFaction($factionName);
        $power += $this->powerPlayers[$owner] ?? 0;
        return $power;
    }

    public function addPower(string $factionName, int $amount, Player $player): void
    {
        if (isset($this->powerPlayers[$player->getXuid()])) {
            $this->powerPlayers[$player->getXuid()] += $amount;
        } else  $this->powerPlayers[$player->getXuid()] = $amount;
    }

    public function addPowerPlayerXuid(string $factionName, int $amount,string $playerXuid): void
    {
        if (isset($this->powerPlayers[$playerXuid])) {
            $this->powerPlayers[$playerXuid] += $amount;
        } else $this->powerPlayers[$playerXuid] = $amount;
    }

    public function reducePower(string $factionName, int $amount, Player $player): void
    {
        if (isset($this->powerPlayers[$player->getXuid()])) {
            $this->powerPlayers[$player->getXuid()] -= $amount;
        } else  $this->powerPlayers[$player->getXuid()] = 0;
    }

    public function getKill(string $factionName): int
    {
        foreach ($this->fastCacheFaction as $name => $values) {
            if (strtolower($factionName) === strtolower($name)) {
                return $values['kill'];
            }
        }
        return 0;
    }

    public function addKill(string $factionName, int $amount): void
    {
        $this->fastCacheFaction[$factionName]['kill'] += $amount;
    }

    public function reduceKill(string $factionName, int $amount): void
    {
        $this->fastCacheFaction[$factionName]['kill'] -= $amount;
    }

    public function getXp(string $factionName): int
    {
        foreach ($this->fastCacheFaction as $name => $values) {
            if (strtolower($factionName) === strtolower($name)) {
                return $values['xp'];
            }
        }
        return 0;
    }

    public function addXp(string $factionName, int $amount): void
    {
        $this->fastCacheFaction[$factionName]['xp'] += $amount;
    }


    public function generateTopFaction(): array {
        $top = 1;
        $array = $this->fastCacheFaction;

        foreach ($array as $factionName => $values) {
            $array[$factionName] = $this->getPower($factionName);
        }
        arsort($array);
        return $array;
    }

    public function reduceXp(string $factionName, int $amount): void
    {
        $this->fastCacheFaction[$factionName]['xp'] -= $amount;
    }

    public function claimChunk(string $factionName, Player $player): void
    {
        $pos = $this->hashClaimPos($player->getPosition());
        $this->fastCacheClaims[$pos] = $factionName;
        $this->saveAllDataAsync();
    }

    public function unclaimChunk(string $factionName, Player $player): void
    {
        $pos = $this->hashClaimPos($player->getPosition());
        unset($this->fastCacheClaims[$pos]);

        SQL::async(static function(RequestAsync $async, \mysqli $db) use ($factionName, $pos): void {
            $prepare = $db->prepare("DELETE FROM claims WHERE factionName = ? AND pos = ?;");
            $prepare->bind_param('ss', $factionName, $pos);
            $prepare->execute();
            $prepare->close();
        });
    }

    public function unclaimAll(string $factionName): void
    {
        foreach ($this->fastCacheClaims as $pos => $faction) {
            if ($factionName === $faction) {
                unset($this->fastCacheClaims[$pos]);
            }
        }
    }

    public function getRankMember(string $xuid, string $factionName): string
    {
        return $this->fastCacheFaction[$factionName]['members'][$xuid] ?? self::OWNER;
    }

    public function deleteFaction(string $factionName): void
    {
        $factionName = strtolower($factionName);
        $xuidOwner = $this->getXuidOwnerFaction($factionName);
        $members = $this->getMembersFaction($factionName);
        $money = $this->getMoneyFaction($factionName);

        # distribution money
        if ($money !== 0) {
            $counts = count($members) + 1;
            $moneyDistribution = intval($money / $counts);
            foreach ($members as $xuid => $rank) {
                $this->getPlugin()->getEconomyManager()->addMoneyNotName($xuid, $moneyDistribution);
                # --- delete player cache
                if (isset($this->fastCachePlayerFactionNotSaveMySQL[$xuid])) unset($this->fastCachePlayerFactionNotSaveMySQL[$xuid]);
            }
            $this->getPlugin()->getEconomyManager()->addMoneyNotName($xuidOwner, $moneyDistribution);
        }
        if (isset($this->fastCachePlayerFactionNotSaveMySQL[$xuidOwner])) unset($this->fastCachePlayerFactionNotSaveMySQL[$xuidOwner]);

        foreach ($this->getMembersFaction($factionName) as $xuid => $rank) {
            $player = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
            if (!is_null($player)) {
                $player->sendMessage(Prefix::PREFIX_GOOD . "§eVotre faction vient d'être supprimé par votre chef !");
            }
        }

        (new LogEvent("La faction ".$factionName." a été supprimé", LogEvent::FACTION_TYPE))->call();

        # delete faction
        unset($this->fastCacheFaction[$factionName]);

        # delete claims
        foreach ($this->fastCacheClaims as $pos => $faction) {
            if ($factionName === $faction) {
                unset($this->fastCacheClaims[$pos]);
            }
        }

        SQL::async(static function (RequestAsync $async, \mysqli $db) use ($factionName): void  {
            $prepare = $db->prepare("DELETE FROM claims WHERE factionName = ?;");
            $prepare->bind_param('s', $factionName);
            $prepare->execute();
            $prepare->close();
        });

        SQL::async(static function (RequestAsync $async, \mysqli $db) use ($factionName): void  {
            $prepare = $db->prepare("DELETE FROM factions WHERE name = ?;");
            $prepare->bind_param('s', $factionName);
            $prepare->execute();
            $prepare->close();
        });


        # deletes others data
        $this->deleteMailsFaction($factionName);
        $this->deleteQuestFaction($factionName);
        $this->deleteBankFaction($factionName);
    }


    public function deleteBankFaction(string $factionName) : void {
        if (!isset($this->fastCacheFactionsBank['members'])) return;
        foreach ($this->fastCacheFactionsBank['members'] as $xuid => $amount) {
            Main::getInstance()->getEconomyManager()->addMoneyOffline($xuid, $amount);
        }
        unset($this->fastCacheFactionsBank[$factionName]);

        SQL::async(static function (RequestAsync $async, \mysqli $db) use ($factionName): void  {
            $prepare = $db->prepare("DELETE FROM factions_bank WHERE faction = ?;");
            $prepare->bind_param('s', $factionName);
            $prepare->execute();
            $prepare->close();
        });
    }

    public function getXuidOwnerFaction(string $factionName): string
    {
        foreach ($this->fastCacheFaction as $name => $values) {
            if (strtolower($factionName) === strtolower($name)) {
                return $values['xuidOwner'];
            }
        }
        return '404';
    }

    # ==== SAUVEGARDE DES FACTIONS ==== #



    public function saveAllDataAsync(): void {
        $factions = $this->fastCacheFaction;
        $claims = $this->fastCacheClaims;
        $itemStringIds = $this->questToday;
        $questsFactions = $this->questCacheFaction;
        $factionBank = $this->fastCacheFactionsBank;

        SQL::async(static function(RequestAsync $thread, \mysqli $db) use ($factions, $claims, $questsFactions, $itemStringIds, $factionBank) : void {

            $db->query("DELETE FROM quest_today;");
            $db->query("INSERT INTO quest_today (item_string_ids) VALUES ('$itemStringIds');");



            foreach ($factions as $factionName => $values) {
                $factionName = $db->real_escape_string($factionName);
                $xuidOwner = $db->real_escape_string($values['xuidOwner']);
                $power = $values['power'];
                $money = $values['money'];
                $members = $db->real_escape_string(base64_encode(serialize($values['members'])));
                $kill = $values['kill'];
                $xp = $values['xp'];
                if ($values['home'] === null) $values['home'] = 'null';
                $home = $db->real_escape_string($values['home']);
                $bio = $db->real_escape_string($values['bio']);
                $visibility = $values['visibility'];

                // Utilisez ON DUPLICATE KEY UPDATE pour mettre à jour ou insérer
                $insertFactionDataPrepare = $db->prepare("INSERT INTO `factions` (`xuidOwner`, `name`, `power`, `money`, `members`, `kill`, `xp`, `home`, `bio`, `visibility`)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
    `xuidOwner` = VALUES(`xuidOwner`), `power` = VALUES(`power`), `money` = VALUES(`money`), `members` = VALUES(`members`), `kill` = VALUES(`kill`), `xp` = VALUES(`xp`), `home` = VALUES(`home`), `bio` = VALUES(`bio`), `visibility` = VALUES(`visibility`);");

                if (strlen($bio) >= 500) $bio = "";
                $insertFactionDataPrepare->bind_param('ssiisiissi', $xuidOwner, $factionName, $power, $money, $members, $kill, $xp, $home, $bio, $visibility);
                $insertFactionDataPrepare->execute();
                $insertFactionDataPrepare->close();
            }


            foreach ($claims as $pos => $factionName) {
                $factionName = $db->real_escape_string($factionName);
                $pos = $db->real_escape_string($pos);

                $search = $db->prepare("SELECT * FROM claims WHERE factionName = ? AND pos = ?;");
                $search->bind_param('ss', $factionName, $pos);
                $search->execute();
                $result = $search->get_result();
                if ($result->num_rows <= 0) {
                    $insertStatement = $db->prepare("INSERT INTO `claims` (`factionName`, `pos`) VALUES (?, ?);");
                    $insertStatement->bind_param('ss', $factionName, $pos);
                    $insertStatement->execute();
                    $insertStatement->close();
                }
            }

            foreach ($questsFactions as $factionName => $values) {
                $factionName = $db->real_escape_string($factionName);
                $itemNumber = $values['item_number'];
                $data = base64_encode(serialize($values['members']));

                $prepare = $db->prepare("INSERT INTO `quest_hebdo` (faction, item_number, data) VALUES ( ?, ?, ?) ON DUPLICATE KEY UPDATE item_number = VALUES(item_number), data = VALUES(data);");
                $prepare->bind_param('sis', $factionName, $itemNumber, $data);
                $prepare->execute();
                $prepare->close();
            }


            foreach ($factionBank as $factionName => $values) {
                $prepare = $db->prepare("INSERT INTO factions_bank (faction, money, data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE money = VALUES(money), data = VALUES(data);");
                $money = $values['money'];
                $data = $values['members'];
                $data = base64_encode(serialize($data));
                $prepare->bind_param('sis', $factionName, $money, $data);
                $prepare->execute();
                $prepare->close();
            }
        });
    }


    public function saveAllData(): void
    {
        $db = SQL::connection();
        $db->query("DELETE FROM quest_today;");
        $itemStringIds = $this->questToday;
        $db->query("INSERT INTO quest_today (item_string_ids) VALUES ('$itemStringIds');");
        $db->close();

        foreach ($this->fastCacheFaction as $factionName => $values) {
            if (strlen($factionName) <= 255) {
                $db = SQL::connection();
                $factionName = $db->real_escape_string($factionName);
                $xuidOwner = $db->real_escape_string($values['xuidOwner']);
                $power = $values['power'];
                $money = $values['money'];
                $members = $db->real_escape_string(base64_encode(serialize($values['members'])));
                $kill = $values['kill'];
                $xp = $values['xp'];
                if ($values['home'] === null) $values['home'] = 'null';
                $home = $db->real_escape_string($values['home']);
                $bio = $db->real_escape_string($values['bio']);
                $visibility = $values['visibility'];

                // Utilisez ON DUPLICATE KEY UPDATE pour mettre à jour ou insérer
                $insertFactionDataPrepare = $db->prepare("INSERT INTO `factions` (`xuidOwner`, `name`, `power`, `money`, `members`, `kill`, `xp`, `home`, `bio`, `visibility`)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
    `xuidOwner` = VALUES(`xuidOwner`), `power` = VALUES(`power`), `money` = VALUES(`money`), `members` = VALUES(`members`), `kill` = VALUES(`kill`), `xp` = VALUES(`xp`), `home` = VALUES(`home`), `bio` = VALUES(`bio`), `visibility` = VALUES(`visibility`);");

                if (strlen($bio) >= 500) $bio = "";
                $insertFactionDataPrepare->bind_param('ssiisiissi', $xuidOwner, $factionName, $power, $money, $members, $kill, $xp, $home, $bio, $visibility);
                $insertFactionDataPrepare->execute();
                $insertFactionDataPrepare->close();
                $db->close();
            }
        }


        foreach ($this->fastCacheClaims as $pos => $factionName) {
            $db = SQL::connection();
            $factionName = $db->real_escape_string($factionName);
            $pos = $db->real_escape_string($pos);

            $search = $db->prepare("SELECT * FROM claims WHERE factionName = ? AND pos = ?;");
            $search->bind_param('ss', $factionName, $pos);
            $search->execute();
            $result = $search->get_result();
            if ($result->num_rows <= 0) {
                $insertStatement = $db->prepare("INSERT INTO `claims` (`factionName`, `pos`) VALUES (?, ?);");
                $insertStatement->bind_param('ss', $factionName, $pos);
                $insertStatement->execute();
            }
            $db->close();
        }


        foreach ($this->questCacheFaction as $factionName => $values) {
            $db = SQL::connection();
            $factionName = $db->real_escape_string($factionName);
            $itemNumber = $values['item_number'];
            $data = base64_encode(serialize($values['members']));

            $prepare = $db->prepare("INSERT INTO `quest_hebdo` (faction, item_number, data) VALUES ( ?, ?, ?) ON DUPLICATE KEY UPDATE item_number = VALUES(item_number), data = VALUES(data);");
            $prepare->bind_param('sis', $factionName, $itemNumber, $data);
            $prepare->execute();
            $prepare->close();
            $db->close();
        }


        foreach ($this->fastCacheFactionsBank as $factionName => $values) {
            $db = SQL::connection();
            $prepare = $db->prepare("INSERT INTO factions_bank (faction, money, data) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE money = VALUES(money), data = VALUES(data);");
            $money = $values['money'];
            $data = $values['members'];
            $data = base64_encode(serialize($data));
            $prepare->bind_param('sis', $factionName, $money, $data);
            $prepare->execute();
            $prepare->close();
            $db->close();
        }

        foreach ($this->powerPlayers as $xuid => $power) {
            $db = SQL::connection();
            $prepare = $db->prepare("INSERT INTO players_power (xuid, power) VALUES (?, ?) ON DUPLICATE KEY UPDATE power = VALUES(power);");
            $prepare->bind_param('si', $xuid, $power);
            $prepare->execute();
            $prepare->close();
            $db->close();
        }
    }
}