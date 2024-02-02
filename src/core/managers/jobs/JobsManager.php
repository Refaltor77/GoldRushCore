<?php


namespace core\managers\jobs;

use core\async\RequestAsync;
use core\entities\XpAndDamage;
use core\events\BossBarReloadEvent;
use core\events\LogEvent;
use core\items\tools\lumberjack\AbstractWoodenAxe;
use core\Main;
use core\managers\Manager;
use core\managers\sync\SyncTypes;
use core\messages\Messages;
use core\particles\BucheronParticle;
use core\particles\FarmerParticle;
use core\particles\HunterParticle;
use core\particles\MinorParticle;
use core\player\CustomPlayer;
use core\settings\BlockIds;
use core\settings\Ids;
use core\sql\SQL;
use core\utils\Utils;
use customiesdevs\customies\item\CustomiesItemFactory;
use mysqli;
use pocketmine\block\Block;
use pocketmine\command\defaults\EnchantCommand;
use pocketmine\data\bedrock\EffectIds;
use pocketmine\entity\animation\TotemUseAnimation;
use pocketmine\entity\Location;
use pocketmine\item\Axe;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\sound\TotemUseSound;
use pocketmine\world\sound\XpCollectSound;

class JobsManager extends Manager
{
    const JOBS = ['bucheron', 'farmer', 'miner', 'assassin', 'chasseur', 'pecheur'];

    const LUMBERJACK = 'lumberjack';


    const FARMER = 'farmer';
    const MINOR = 'minor';
    const MURDER = 'murder';
    const HUNTER = 'hunter';
    const FISHERMAN = 'fisherman';


    const PATTERN = [
        'lumberjack' => [
            'xp' => 0,
            'lvl' => 1
        ],
        'farmer' => [
            'xp' => 0,
            'lvl' => 1
        ],
        'minor' => [
            'xp' => 0,
            'lvl' => 1
        ],
        'murder' => [
            'xp' => 0,
            'lvl' => 1
        ],
        'hunter' => [
            'xp' => 0,
            'lvl' => 1
        ],
        'fisherman' => [
            'xp' => 0,
            'lvl' => 1
        ],
    ];
    const XP_LVL = [
        2 => 1000,
        3 => 4000,
        4 => 8000,
        5 => 10000,
        6 => 20000,
        7 => 30000,
        8 => 40000,
        9 => 50000,
        10 => 60000,
        11 => 70000,
        12 => 80000,
        13 => 90000,
        14 => 100000,
        15 => 110000,
        16 => 120000,
        17 => 130000,
        18 => 140000,
        19 => 150000,
        20 => 160000,
        21 => 170000,
        22 => 180000,
        23 => 190000,
        24 => 200000,
        25 => 210000,
        26 => 410000,
        27 => 810000,
        28 => 1010000,
        29 => 2010000,
        30 => 30010000,
    ];
    public array $cache = [];
    public array $cacheXp = [];
    public string $jobX2Xp = "";
    public int $jobX2Time = 0;

    const XP_QUOTI = 5000000000;

    public function __construct(Main $plugin)
    {
        $db = SQL::connection();

        $db->query("CREATE TABLE IF NOT EXISTS `lumberjack` (xuid VARCHAR(255) PRIMARY KEY,  xp int,  lvl int);");
        $db->query("CREATE TABLE IF NOT EXISTS `farmer` (xuid VARCHAR(255) PRIMARY KEY,  xp int,  lvl int);");
        $db->query("CREATE TABLE IF NOT EXISTS `minor` (xuid VARCHAR(255) PRIMARY KEY,  xp int,  lvl int);");
        $db->query("CREATE TABLE IF NOT EXISTS `murder` (xuid VARCHAR(255) PRIMARY KEY,  xp int,  lvl int);");
        $db->query("CREATE TABLE IF NOT EXISTS `hunter` (xuid VARCHAR(255) PRIMARY KEY,  xp int,  lvl int);");
        $db->query("CREATE TABLE IF NOT EXISTS `fisherman` (xuid VARCHAR(255) PRIMARY KEY,  xp int,  lvl int);");
        $db->query("CREATE TABLE IF NOT EXISTS `xp_max` (`xuid` VARCHAR(255) PRIMARY KEY, `xp` INT, `time` INT);");
        $db->query("CREATE TABLE IF NOT EXISTS `xp_event` (job VARCHAR(255) PRIMARY KEY, time INT);");


        $result = $db->query("SELECT * FROM `xp_event`;");
        if ($result->num_rows > 0) {
            $result = $result->fetch_all(MYSQLI_ASSOC);
            foreach ($result as $row => $value) {
                if ($value['time'] <= time()) {
                    $job = $value['job'];
                    $db->query("DELETE FROM xp_event WHERE job = '$job';");
                } else {
                    $this->jobX2Xp = $value['job'];
                    $this->jobX2Time = $value['time'];
                    break;
                }
            }
        }

        $db->close();

        parent::__construct($plugin);
    }


    public function setXpJobX2(string $job): void
    {
        $this->jobX2Xp = $job;
        $this->jobX2Time = time() + 60 * 60;

        SQL::async(static function (RequestAsync $async, mysqli $db) use ($job): void {
            $db->query("DELETE FROM xp_event;");
            $time = time() + 60 * 60;
            $db->query("INSERT INTO xp_event (job, time) VALUES ('$job', $time);");
        });
    }

    public function hasX2Xp(string $job): bool
    {
        if ($this->jobX2Xp == $job && $this->jobX2Time > time()) return true;
        return false;
    }


    public function getAllStats(callable $callback): void
    {
        SQL::async(static function (RequestAsync $async, mysqli $db): void {

            $data = [];
            $result = $db->query("SELECT * FROM `lumberjack`;")->fetch_all(MYSQLI_ASSOC);
            foreach ($result as $row => $value) {
                if (isset($data[$value['xuid']])) {
                    $data[$value['xuid']] += $value['lvl'];
                } else {
                    $data[$value['xuid']] = $value['lvl'];
                }
            }

            $result = $db->query("SELECT * FROM `farmer`;")->fetch_all(MYSQLI_ASSOC);
            foreach ($result as $row => $value) {
                if (isset($data[$value['xuid']])) {
                    $data[$value['xuid']] += $value['lvl'];
                } else {
                    $data[$value['xuid']] = $value['lvl'];
                }
            }

            $result = $db->query("SELECT * FROM `minor`;")->fetch_all(MYSQLI_ASSOC);
            foreach ($result as $row => $value) {
                if (isset($data[$value['xuid']])) {
                    $data[$value['xuid']] += $value['lvl'];
                } else {
                    $data[$value['xuid']] = $value['lvl'];
                }
            }

            $result = $db->query("SELECT * FROM `murder`;")->fetch_all(MYSQLI_ASSOC);
            foreach ($result as $row => $value) {
                if (isset($data[$value['xuid']])) {
                    $data[$value['xuid']] += $value['lvl'];
                } else {
                    $data[$value['xuid']] = $value['lvl'];
                }
            }

            $result = $db->query("SELECT * FROM `hunter`;")->fetch_all(MYSQLI_ASSOC);
            foreach ($result as $row => $value) {
                if (isset($data[$value['xuid']])) {
                    $data[$value['xuid']] += $value['lvl'];
                } else {
                    $data[$value['xuid']] = $value['lvl'];
                }
            }

            $result = $db->query("SELECT * FROM `fisherman`;")->fetch_all(MYSQLI_ASSOC);
            foreach ($result as $row => $value) {
                if (isset($data[$value['xuid']])) {
                    $data[$value['xuid']] += $value['lvl'];
                } else {
                    $data[$value['xuid']] = $value['lvl'];
                }
            }

            $async->setResult($data);
        }, static function (RequestAsync $async) use ($callback): void {
            $callback($async->getResult());
        });
    }


    public function loadData(CustomPlayer $player): void
    {
        $xuid = $player->getXuid();
        SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid): void {

            $data = self::PATTERN;
            $prepare = $db->prepare("SELECT * FROM `xp_max` WHERE `xuid` = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result();
            if ($result->num_rows > 0) {
                $fetch = $result->fetch_all(MYSQLI_ASSOC)[0];
                $xp = $fetch['xp'];
                $time = $fetch['time'];
                if ($time >= time()) {
                    $xp = 0;
                    $time = time() + 60 * 60 * 24;
                }
            } else {
                $xp = 0;
            }

            $data['xp'] = $xp;
            $data['time'] = $time ?? time() + 60 * 60 * 24;


            $prepare = $db->prepare("SELECT * FROM `lumberjack` WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_all(MYSQLI_ASSOC)[0] ?? [];
            $data['lumberjack']['xp'] = $result['xp'] ?? 0;
            $data['lumberjack']['lvl'] = $result['lvl'] ?? 1;

            $prepare = $db->prepare("SELECT * FROM `farmer` WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_all(MYSQLI_ASSOC)[0] ?? [];
            $data['farmer']['xp'] = $result['xp'] ?? 0;
            $data['farmer']['lvl'] = $result['lvl'] ?? 1;

            $prepare = $db->prepare("SELECT * FROM `minor` WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_all(MYSQLI_ASSOC)[0] ?? [];
            $data['minor']['xp'] = $result['xp'] ?? 0;
            $data['minor']['lvl'] = $result['lvl'] ?? 1;

            $prepare = $db->prepare("SELECT * FROM `murder` WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_all(MYSQLI_ASSOC)[0] ?? [];
            $data['murder']['xp'] = $result['xp'] ?? 0;
            $data['murder']['lvl'] = $result['lvl'] ?? 1;

            $prepare = $db->prepare("SELECT * FROM `hunter` WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_all(MYSQLI_ASSOC)[0] ?? [];
            $data['hunter']['xp'] = $result['xp'] ?? 0;
            $data['hunter']['lvl'] = $result['lvl'] ?? 1;

            $prepare = $db->prepare("SELECT * FROM `fisherman` WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_all(MYSQLI_ASSOC)[0] ?? [];
            $data['fisherman']['xp'] = $result['xp'] ?? 0;
            $data['fisherman']['lvl'] = $result['lvl'] ?? 1;

            $thread->setResult($data);
        }, static function (RequestAsync $thread) use ($player): void {
            $data = $thread->getResult();
            if ($player->isConnected()) {
                Main::getInstance()->getJobsManager()->cacheXp[$player->getXuid()] = [
                    'xp' => $data['xp'],
                    'time' => $data['time']
                ];
                unset($data['xp']);
                unset($data['time']);
                Main::getInstance()->getJobsManager()->cache[$player->getXuid()] = $data;
                $player->hasJobsLoaded = true;
            }
        });
    }





    public function getLvlJobs(Player $player, string $jobs): int
    {
        return $this->cache[$player->getXuid()][$jobs]['lvl'];
    }

    public function getAllJobs(Player $player): array
    {
        if (!isset($this->cache[$player->getXuid()])) return self::PATTERN;
        return $this->cache[$player->getXuid()];
    }



    public function generateTopJobs(string $job, callable $callback): void {
        $this->getAllJobsSql(function (array $jobs) use ($callback, $job): void {
            $arrayQueried = [];
            foreach ($jobs as $xuid => $values) {
                $data = $values[$job];
                $lvl = $data['lvl'];
                $arrayQueried[$xuid] = $lvl;
            }
            arsort($arrayQueried);
            $callback($arrayQueried);
        });
    }



    public function getAllJobsXuidSql(string $xuid, callable $callback): void {
        SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid): void {

            $data = self::PATTERN;
            $prepare = $db->prepare("SELECT * FROM `xp_max` WHERE `xuid` = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result();
            if ($result->num_rows > 0) {
                $fetch = $result->fetch_all(MYSQLI_ASSOC)[0];
                $xp = $fetch['xp'];
                $time = $fetch['time'];
                if ($time >= time()) {
                    $xp = 0;
                    $time = time() + 60 * 60 * 24;
                }
            } else {
                $xp = 0;
            }

            $data['xp'] = $xp;
            $data['time'] = $time ?? time() + 60 * 60 * 24;


            $prepare = $db->prepare("SELECT * FROM `lumberjack` WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_all(MYSQLI_ASSOC)[0] ?? [];
            $data['lumberjack']['xp'] = $result['xp'] ?? 0;
            $data['lumberjack']['lvl'] = $result['lvl'] ?? 1;

            $prepare = $db->prepare("SELECT * FROM `farmer` WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_all(MYSQLI_ASSOC)[0] ?? [];
            $data['farmer']['xp'] = $result['xp'] ?? 0;
            $data['farmer']['lvl'] = $result['lvl'] ?? 1;

            $prepare = $db->prepare("SELECT * FROM `minor` WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_all(MYSQLI_ASSOC)[0] ?? [];
            $data['minor']['xp'] = $result['xp'] ?? 0;
            $data['minor']['lvl'] = $result['lvl'] ?? 1;

            $prepare = $db->prepare("SELECT * FROM `murder` WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_all(MYSQLI_ASSOC)[0] ?? [];
            $data['murder']['xp'] = $result['xp'] ?? 0;
            $data['murder']['lvl'] = $result['lvl'] ?? 1;

            $prepare = $db->prepare("SELECT * FROM `hunter` WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_all(MYSQLI_ASSOC)[0] ?? [];
            $data['hunter']['xp'] = $result['xp'] ?? 0;
            $data['hunter']['lvl'] = $result['lvl'] ?? 1;

            $prepare = $db->prepare("SELECT * FROM `fisherman` WHERE xuid = ?;");
            $prepare->bind_param('s', $xuid);
            $prepare->execute();
            $result = $prepare->get_result()->fetch_all(MYSQLI_ASSOC)[0] ?? [];
            $data['fisherman']['xp'] = $result['xp'] ?? 0;
            $data['fisherman']['lvl'] = $result['lvl'] ?? 1;

            $thread->setResult($data);
        }, static function (RequestAsync $thread) use ($callback): void {
            $data = $thread->getResult();
            unset($data['xp']);
            unset($data['time']);
            $callback($data);
        });
    }


    public function getAllJobsSql(callable $callback): void {
        SQL::async(static function (RequestAsync $thread, mysqli $db): void {


            $prepare = $db->prepare("SELECT * FROM `lumberjack`;");
            $prepare->execute();
            $result = $prepare->get_result();

            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[$row['xuid']]['lumberjack'] = [
                    'xp' => $row['xp'] ?? 0,
                    'lvl' => $row['lvl'] ?? 1
                ];
            }


            $prepare = $db->prepare("SELECT * FROM `farmer`;");
            $prepare->execute();
            $result = $prepare->get_result();

            while ($row = $result->fetch_assoc()) {
                $data[$row['xuid']]['farmer'] = [
                    'xp' => $row['xp'] ?? 0,
                    'lvl' => $row['lvl'] ?? 1
                ];
            }

            $prepare = $db->prepare("SELECT * FROM `minor`;");
            $prepare->execute();
            $result = $prepare->get_result();

            while ($row = $result->fetch_assoc()) {
                $data[$row['xuid']]['minor'] = [
                    'xp' => $row['xp'] ?? 0,
                    'lvl' => $row['lvl'] ?? 1
                ];
            }

            $prepare = $db->prepare("SELECT * FROM `murder`;");
            $prepare->execute();
            $result = $prepare->get_result();

            while ($row = $result->fetch_assoc()) {
                $data[$row['xuid']]['murder'] = [
                    'xp' => $row['xp'] ?? 0,
                    'lvl' => $row['lvl'] ?? 1
                ];
            }

            $prepare = $db->prepare("SELECT * FROM `hunter`;");
            $prepare->execute();
            $result = $prepare->get_result();

            while ($row = $result->fetch_assoc()) {
                $data[$row['xuid']]['hunter'] = [
                    'xp' => $row['xp'] ?? 0,
                    'lvl' => $row['lvl'] ?? 1
                ];
            }

            $prepare = $db->prepare("SELECT * FROM `fisherman`;");
            $prepare->execute();
            $result = $prepare->get_result();

            while ($row = $result->fetch_assoc()) {
                $data[$row['xuid']]['fisherman'] = [
                    'xp' => $row['xp'] ?? 0,
                    'lvl' => $row['lvl'] ?? 1
                ];
            }

            $thread->setResult($data);
        }, static function (RequestAsync $thread) use ($callback): void {
            $data = $thread->getResult();
            $callback($data);
        });
    }


    public function giveReward(Player $player, int $lvl, string $job): void
    {
        $economy = Main::getInstance()->getEconomyManager();
        $itemList = CustomiesItemFactory::getInstance();
        $inventoryJobs = Main::getInstance()->jobsStorage;
        $craftUnlock = Main::getInstance()->getCraftManager();


        (new LogEvent($player->getName() . " a passé le niveau $lvl dans le métier $job", LogEvent::JOB_TYPE))->call();

        switch ($job) {
            case self::FARMER:
                $player->broadcastAnimation(new TotemUseAnimation($player));
                $player->broadcastSound(new TotemUseSound());
                Utils::AminationTexture($player, EffectIds::VILLAGE_HERO);
                $player->sendTitle("§6Bravo ! §fNiveau §6$lvl §fmétier §6Farmeur");
                FarmerParticle::spawn($player->getParticlePos());
                if ($player instanceof CustomPlayer) {
                    $player->sendNotification("§eBravo ! Tu viens de passer au niveau §6$lvl §edans le métier de §6Farmeur", CustomPlayer::NOTIF_TYPE_FARMER);
                }
                switch ($lvl) {
                    case 2:
                        $item = $itemList->get(Ids::BUCKET_COPPER_EMPTY);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 3:
                        $item = CustomiesItemFactory::getInstance()->get(BlockIds::DIRT_COMPRESSED)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $inventoryJobs->saveUserCache($player, true);
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 4:
                        $item = VanillaItems::BONE_MEAL()->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = VanillaItems::BONE_MEAL()->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = VanillaItems::BONE_MEAL()->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = VanillaItems::BONE_MEAL()->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $inventoryJobs->saveUserCache($player, true);
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 5:
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::EMERALD_CHESTPLATE);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::EMERALD_SHOVEL);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                        });
                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Pelle en émeraude, plastron en émeraude."));
                        break;
                    case 6:
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $inventoryJobs->saveUserCache($player, true);
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 7:
                        $economy->addMoney($player, 30000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §630 000$"));
                        break;
                    case 8:
                        $item = $itemList->get(Ids::SEEDS_WHEAT_OBSIDIAN)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $inventoryJobs->saveUserCache($player, true);
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 9:
                        $paper = VanillaItems::PAPER()->setCustomName("§fTicket Forgeron : §6Sac du fermier");
                        $paper->setLore([
                            "§6§l---",
                            "§6§eDescription:§r§f a parler au forgeron, échange ce ticket contre un objet.",
                            "§6§l---"
                        ]);
                        $paper->getNamedTag()->setString('item', 'backpack_farm');
                        $inventoryJobs->addItemInStorage($player, $paper);

                        $paper = VanillaItems::PAPER()->setCustomName("§fTicket Forgeron : §6Sac de l'archéologue");
                        $paper->setLore([
                            "§6§l---",
                            "§6§eDescription:§r§f a parler au forgeron, échange ce ticket contre un objet.",
                            "§6§l---"
                        ]);
                        $paper->getNamedTag()->setString('item', 'backpack_fossil');
                        $inventoryJobs->addItemInStorage($player, $paper);
                        $inventoryJobs->saveUserCache($player, true);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, CustomiesItemFactory::getInstance()->get(Ids::BACKPACK_FARM)->getTextureString());
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, CustomiesItemFactory::getInstance()->get(Ids::BACKPACK_FOSSIL)->getTextureString());
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 10:
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_CHESTPLATE);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_SHOVEL);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                        });
                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Pelle en améthyste, plastron en améthyste."));
                        break;
                    case 11:
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 12:
                        $economy->addMoney($player, 60000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §660 000$"));
                        break;
                    case 13:
                        $item = $itemList->get(Ids::UNCLAIM_FINDER_AMETHYST)->setCount(1);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 14:
                        $item = $itemList->get(Ids::AMETHYST_AXE)->setCount(1);
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 5));
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 15:
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_CHESTPLATE);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SHOVEL);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                        });
                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Pelle en platine, plastron en platine."));
                        break;
                    case 16:
                        $economy->addMoney($player, 120000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6120 000$"));
                        break;
                    case 17:
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $inventoryJobs->saveUserCache($player, true);
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 18:
                        $item = $itemList->get(Ids::PLATINUM_AXE)->setCount(1);
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 5));
                        $inventoryJobs->addItemInStorage($player, $item);
                        $inventoryJobs->saveUserCache($player, true);
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 19:
                        $item = $itemList->get(Ids::BUCKET_GOLD_EMPTY)->setCount(1);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $inventoryJobs->saveUserCache($player, true);
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 20:
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::GOLD_CHESTPLATE);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::GOLD_SHOVEL);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                        });
                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Pelle en or, plastron en or."));
                        break;
                    case 21:
                        $economy->addMoney($player, 300000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6300 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(1);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 22:
                        $economy->addMoney($player, 500000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6500 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(2);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 23:
                        $economy->addMoney($player, 600000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6600 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(3);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 24:
                        $economy->addMoney($player, 800000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6800 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(4);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 25:
                        $economy->addMoney($player, 1000000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §61 000 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(5);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                }
                break;
            case self::FISHERMAN:
                if ($player instanceof CustomPlayer) {
                    $player->sendMessage(Messages::message("§eBravo ! Tu viens de passer au niveau §6$lvl §edans le métier de §6Pêcheur"));
                }
                switch ($lvl) {
                    case 2:

                        break;
                }
                break;
            case self::HUNTER:
                $player->broadcastAnimation(new TotemUseAnimation($player));
                $player->broadcastSound(new TotemUseSound());
                Utils::AminationTexture($player, EffectIds::VILLAGE_HERO);
                $player->sendTitle("§6Bravo ! §fNiveau §6$lvl §fmétier §6Chasseur");
                HunterParticle::spawn($player->getParticlePos());
                if ($player instanceof CustomPlayer) {
                    $player->sendNotification("§eBravo ! Tu viens de passer au niveau §6$lvl §edans le métier de §6Chasseur", CustomPlayer::NOTIF_TYPE_HUNTER);
                }
                switch ($lvl) {
                    case 2:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(32);
                        $inventoryJobs->addItemInStorage($player, $item);
                        if ($player->getInventory()->canAddItem(VanillaItems::PAPER())) {
                            $player->getInventory()->addItem(VanillaItems::PAPER()->setCustomName("§cBon courage !"));
                        }

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 3:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(32);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = VanillaItems::DIAMOND_SWORD();
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 5));
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 4:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(32);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::RAISIN)->setCount(16);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 5:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(32);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::EMERALD_BOOTS);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::EMERALD_SWORD);

                        $inventoryJobs->saveUserCache($player, true);

                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                        });
                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Épée en émeraude, bottes en émeraude."));
                        break;
                    case 6:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(32);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $economy->addMoney($player, 50000);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fTu viens de gagner §650 000$"));
                        break;
                    case 7:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(32);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::EMPTY_BOTTLE)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::EMPTY_BOTTLE)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::EMPTY_BOTTLE)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::EMPTY_BOTTLE)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 8:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(32);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 9:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(32);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::ALCOOL_PUISSANT_HEAL)->setCount(5);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BERRY_YELLOW)->setCount(16);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BERRY_BLUE)->setCount(16);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BERRY_BLACK)->setCount(16);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 10:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(32);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_BOOTS);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_SWORD);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                        });

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Épée en améthyste, bottes en améthyste."));
                        break;
                    case 11:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $economy->addMoney($player, 50000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §650 000$"));

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 12:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::KEY_RARE)->setCount(16);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 13:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::SEEDS_WHEAT_OBSIDIAN)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 14:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::ALCOOL_PUISSANT_FORCE)->setCount(3);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 15:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_BOOTS);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SWORD);

                        $inventoryJobs->saveUserCache($player, true);

                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                        });
                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Épée en platine, bottes en platine."));
                        break;
                    case 16:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::ALCOOL_PUISSANT_HEAL)->setCount(8);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 17:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::KEY_FORTUNE)->setCount(1);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 18:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $economy->addMoney($player, 50000);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fTu viens de gagner §650 000$"));
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 19:
                        $item = $itemList->get(Ids::ALCOOL_HEAL)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::PLATINUM_DYNAMITE)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 20:
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::GOLD_BOOTS);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::GOLD_SWORD);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                        });
                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Épée en or, bottes en or."));
                        break;
                    case 21:
                        $economy->addMoney($player, 300000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6300 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(1);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 22:
                        $economy->addMoney($player, 500000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6500 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(2);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 23:
                        $economy->addMoney($player, 600000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6600 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(3);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 24:
                        $economy->addMoney($player, 800000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6800 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(4);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 25:
                        $economy->addMoney($player, 1000000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §61 000 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(5);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                }
                break;
            case self::LUMBERJACK:
                $player->broadcastAnimation(new TotemUseAnimation($player));
                $player->broadcastSound(new TotemUseSound());
                Utils::AminationTexture($player, EffectIds::VILLAGE_HERO);
                $player->sendTitle("§6Bravo ! §fNiveau §6$lvl §fmétier §6Bûcheron");
                BucheronParticle::spawn($player->getParticlePos());
                if ($player instanceof CustomPlayer) {
                    $player->sendNotification("§eBravo ! Tu viens de passer au niveau §6$lvl §edans le métier de §6Bûcheron", CustomPlayer::NOTIF_TYPE_BUCHERON);
                }
                switch ($lvl) {
                    case 2:
                        $paper = VanillaItems::PAPER()->setCustomName("§fTicket Forgeron : §6Hache du bûcheron");
                        $paper->setLore([
                            "§6§l---",
                            "§6§eDescription:§r§f a parler au forgeron, échange ce ticket contre un objet.",
                            "§6§l---"
                        ]);
                        $paper->getNamedTag()->setString('item', 'bucheron_axe');
                        $inventoryJobs->addItemInStorage($player, $paper);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, CustomiesItemFactory::getInstance()->get(Ids::BONE_AXE_1)->getTextureString());
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 3:
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 4:
                        $economy->addMoney($player, 30000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §630 000$"));
                        break;
                    case 5:
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::EMERALD_AXE);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::EMERALD_LEGGINGS);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                        });


                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Hache en émeraude, jambières en émeraude."));
                        break;
                    case 6:
                        $item = $itemList->get(Ids::KEY_RARE)->setCount(1);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 7:
                        $item = $itemList->get(BlockIds::AMETHYST_CHEST)->setCount(1);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 8:
                        $item = $itemList->get(Ids::WATER_DYNAMITE)->setCount(16);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 9:
                        $economy->addMoney($player, 60000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §660 000$"));
                        break;
                    case 10:
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_AXE);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_LEGGINGS);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                        });
                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Hache en améthyste, jambières en améthyste."));
                        break;
                    case 11:
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 12:
                        $economy->addMoney($player, 120000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6120 000$"));
                        break;
                    case 13:
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 14:
                        $item = $itemList->get(Ids::SEEDS_WHEAT_OBSIDIAN)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::SEEDS_WHEAT_OBSIDIAN)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 15:
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_AXE);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_LEGGINGS);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                        });
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 16:
                        $item = $itemList->get(Ids::ALCOOL_PUISSANT_HASTE)->setCount(16);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 17:
                        $item = $itemList->get(Ids::KEY_FORTUNE)->setCount(1);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 18:

                        $item = $itemList->get(Ids::FLOWER_PERCENT)->setCount(1);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $inventoryJobs->saveUserCache($player, true);
                        $item = $itemList->get(BlockIds::FLOWER_PERCENT)->setCount(1);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 19:
                        $item = $itemList->get(Ids::KEY_MYTHICAL)->setCount(1);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 20:
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::GOLD_AXE);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::GOLD_LEGGINGS);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                        });
                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Hache en or, jambières en or."));
                        break;
                    case 21:
                        $economy->addMoney($player, 300000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6300 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(1);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 22:
                        $economy->addMoney($player, 500000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6500 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(2);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 23:
                        $economy->addMoney($player, 600000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6600 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(3);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 24:
                        $economy->addMoney($player, 800000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6800 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(4);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 25:
                        $economy->addMoney($player, 1000000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §61 000 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(5);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                }
                break;
            case self::MINOR:
                $player->broadcastAnimation(new TotemUseAnimation($player));
                $player->broadcastSound(new TotemUseSound());
                Utils::AminationTexture($player, EffectIds::VILLAGE_HERO);
                $player->sendTitle("§6Bravo ! §fNiveau §6$lvl §fmétier §6Mineur");
                MinorParticle::spawn($player->getParticlePos());
                if ($player instanceof CustomPlayer) {
                    $player->sendNotification("§eBravo ! Tu viens de passer au niveau §6$lvl §edans le métier de §6Mineur", CustomPlayer::NOTIF_TYPE_MINEUR);
                }
                switch ($lvl) {
                    case 2:
                        /** @var Item $item */
                        $item = $itemList->get(Ids::COPPER_PICKAXE);
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2));
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 3:
                        /** @var Item $item */
                        $item = $itemList->get(Ids::BOTTLE_XP)->setCount(32);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 4:
                        $item = $itemList->get(Ids::EMERALD_INGOT)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::EMERALD_INGOT)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 5:
                        $hammer = CustomiesItemFactory::getInstance()->get(Ids::EMERALD_HAMMER);
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::EMERALD_HELMET);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::EMERALD_PICKAXE);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $hammer->getTextureString(), function () use ($player, $helmet, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $helmet, $pickaxe): void {
                                Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                            });
                        });
                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Hammer en émeraude, pioche en émeraude, casque en émeraude."));
                        break;
                    case 6:

                        $paper = VanillaItems::PAPER()->setCustomName("§fTicket Forgeron : §6VoidStone");
                        $paper->setLore([
                            "§6§l---",
                            "§6§eDescription:§r§f a parler au forgeron, échange ce ticket contre un objet.",
                            "§6§l---"
                        ]);
                        $paper->getNamedTag()->setString('item', 'voidstone');

                        $inventoryJobs->addItemInStorage($player, $paper);
                        $inventoryJobs->saveUserCache($player, true);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, CustomiesItemFactory::getInstance()->get(Ids::VOIDSTONE)->getTextureString());
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 7:
                        $economy->addMoney($player, 30000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §630 000$"));
                        break;
                    case 8:
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 9:
                        $item = $itemList->get(Ids::EMERALD_PICKAXE);
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 5));
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                        $inventoryJobs->addItemInStorage($player, $item);
                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 10:
                        $hammer = CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HAMMER);
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_HELMET);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_PICKAXE);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $hammer->getTextureString(), function () use ($player, $helmet, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                                Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());

                            });
                        });
                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Hammer en améthyste, pioche en améthyste, casque en améthyste."));
                        break;
                    case 11:
                        $paper = VanillaItems::PAPER()->setCustomName("§fTicket Forgeron : §6Sac du mineur");
                        $paper->setLore([
                            "§6§l---",
                            "§6§eDescription:§r§f a parler au forgeron, échange ce ticket contre un objet.",
                            "§6§l---"
                        ]);
                        $paper->getNamedTag()->setString('item', 'backpack_ore');
                        $inventoryJobs->addItemInStorage($player, $paper);
                        $inventoryJobs->saveUserCache($player, true);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, CustomiesItemFactory::getInstance()->get(Ids::BACKPACK_ORE)->getTextureString());

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 12:
                        $economy->addMoney($player, 60000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §660 000$"));
                        break;
                    case 13:
                        $item = $itemList->get(Ids::EMERALD_DYNAMITE)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 14:
                        $item = $itemList->get(Ids::FLOWER_PERCENT);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 15:
                        $hammer = CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HAMMER);
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_HELMET);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_PICKAXE);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $hammer->getTextureString(), function () use ($player, $helmet, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                                Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                            });
                        });

                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Hammer en platine, pioche en platine, casque en platine."));
                        break;
                    case 16:
                        $item = $itemList->get(BlockIds::OBSIDIAN_PLATINUM)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(BlockIds::OBSIDIAN_PLATINUM)->setCount(64);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 17:
                        $economy->addMoney($player, 120000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6120 000$"));
                        break;
                    case 18:
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);
                        $item = $itemList->get(Ids::BOTTLE_XP);
                        $item->getNamedTag()->setString("total_xp", 10 * 64);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 19:
                        $item = $itemList->get(Ids::UNCLAIM_FINDER_GOLD)->setCount(1);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 20:
                        $hammer = CustomiesItemFactory::getInstance()->get(Ids::GOLD_HAMMER);
                        $helmet = CustomiesItemFactory::getInstance()->get(Ids::GOLD_HELMET);
                        $pickaxe = CustomiesItemFactory::getInstance()->get(Ids::GOLD_PICKAXE);
                        Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $hammer->getTextureString(), function () use ($player, $helmet, $pickaxe): void {
                            Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $helmet->getTextureString(), function () use ($player, $pickaxe): void {
                                Main::getInstance()->getCraftManager()->unlockCraftForPlayerAsync($player, $pickaxe->getTextureString());
                            });
                        });
                        $player->sendMessage(Messages::message("§fTu viens de débloquer les crafts : §6Hammer en or, pioche en or, casque en or."));
                        break;
                    case 21:
                        $economy->addMoney($player, 300000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6300 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(1);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 22:
                        $economy->addMoney($player, 500000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6500 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(2);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 23:
                        $economy->addMoney($player, 600000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6600 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(3);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 24:
                        $economy->addMoney($player, 800000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §6800 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(4);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                    case 25:
                        $economy->addMoney($player, 1000000);
                        $player->sendMessage(Messages::message("§fTu viens de gagner §61 000 000$"));
                        $item = $itemList->get(Ids::GOLD_NUGGET)->setCount(5);
                        $inventoryJobs->addItemInStorage($player, $item);

                        $inventoryJobs->saveUserCache($player, true);

                        $player->sendMessage(Messages::message("§fDes §6items§f ont été ajoutés à ton inventaire de récompense. §6§l/rewards"));
                        break;
                }
                break;
            case self::MURDER:
                if ($player instanceof CustomPlayer) {
                    $player->sendMessage(Messages::message("§eBravo ! Tu viens de passer au niveau §6$lvl §edans le métier §6d'assassin"));
                }
                switch ($lvl) {
                    case 2:

                        break;
                }
                break;
        }
    }


    public function xpNotif(Player $player, Position $pos, int $xp, string $jobs): void
    {
        $xpData = $this->cacheXp[$player->getXuid()];
        if ($this->hasX2Xp($jobs)) $xp = $xp * 2;
        if (Main::getInstance()->getXpManager()->hasDobble($player)) $xp = $xp * 2;
        if ($jobs === self::FARMER && $player->getInventory()->getItemInHand() instanceof AbstractWoodenAxe) {
            $xp = $xp * 2;
        }
        if(Main::getInstance()->getSettingsManager()->getSetting($player,"xp-jobs")) {
            $entity = new XpAndDamage(new Location($pos->getX(), $pos->getY(), $pos->getZ(), $pos->getWorld(), 0, 0));
            $entity->showIndicator($player, "§f+§6" . strval($xp) . '§fxp');
            $player->getWorld()->addSound($player->getPosition(), new XpCollectSound(), [$player]);
        }
    }

    public function getContent(Player $player, string $job)
    {

        if ($job == "Mineur") {
            $xpNeed = self::XP_LVL[$this->cache[$player->getXuid()][self::MINOR]['lvl'] + 1];
            return "§fVoici les informations du métier de §6Mineur\n\n§fNiveau: §6{$this->cache[$player->getXuid()][self::MINOR]['lvl']}§f/§650\n§6XP§f: §6{$this->cache[$player->getXuid()][self::MINOR]['xp']}§f/{$xpNeed}\n\n§fComment §6XP§f de ce métier ?\n\n§7- Charbon : 5XP\n§7- Fer : 7XP\n§7- Or : 50XP\n§7- Netherite : 22XP\n§7- Diamant : 8XP\n§7- Quartz : 3XP\n§7- Minerais d'xp : 9XP\n§7- Glowstone : 2XP\n§7- Geode : 4XP\n§7- Platine : 25XP\n§7- Lucky Ore : 6XP\n§7- Fossil : 12XP\n§7- Orihalcon : 500XP\n§7- Or rose : 200XP"; // \n\n§fChaque §6niveau§f, §6+1% §fde chance de doubler ses lots en minage.\nChaque §6niveau§f, §6+1% §fd'augment"ation d'XP quand tu mines un minerai."
        }
        if ($job == "Farmeur") {
            $xpNeed = self::XP_LVL[$this->cache[$player->getXuid()][self::FARMER]['lvl'] + 1];
            return "§fVoici les informations du métier de §6Farmeur\n\n§fNiveau: §6{$this->cache[$player->getXuid()][self::FARMER]['lvl']}§f/§650\n§6XP§f: §6{$this->cache[$player->getXuid()][self::FARMER]['xp']}§f/{$xpNeed}\n\n§fComment §6XP§f de ce métier ?\n\n§7- Blé §f:§6 8XP\n§7- Melon §f:§6 3XP\n§7- Patate §f:§6 9XP\n§7- Carotte §f:§6 9XP\n§7- Citrouille §f:§6 4XP\n§7- Canne a sucre §f:§6 3XP\n§7- Baies §f:§6 7XP"; // \n\n§fChaque §610 niveaux§f, §6+5% §fde chance de doubler ses lots.\n§fChaque §610 niveaux§f, §6+2§f,§65% §fde chance d'obtenir de l'XP en cassant des récoltes/melons/citrouilles."
        }
        if ($job == "Assassin") {
            $xpNeed = self::XP_LVL[$this->cache[$player->getXuid()][self::MURDER]['lvl'] + 1];
            return "§fVoici les informations du métier d'§6Assassin.\n\n§fNiveau: §6{$this->cache[$player->getXuid()][self::MURDER]['lvl']}§f/§650\n§6XP§f: §6{$this->cache[$player->getXuid()][self::MURDER]['xp']}§f/{$xpNeed}\n\n§fComment §6XP§f de ce métier ?\n\n§7- Tuer un joueur §f:§6 50XP\n\n§7- Assister à un kill §f:§6 30XP"; //§7- Obtenir une série de kills §f:§6 25XP\n \n\n§fChaque §6niveau§f, §6+1%§f de l'expérience d'un joueur que tu tueras te sera donné.\n§fChaque §6niveau§f, §6+1%§f de point te sera attribué lors d'un §6kill§f."
        }
        if ($job == "Bucheron") {
            $xpNeed = self::XP_LVL[$this->cache[$player->getXuid()][self::LUMBERJACK]['lvl'] + 1];
            return "§fVoici les informations du métier de §6Bucheron.\n\n§fNiveau: §6{$this->cache[$player->getXuid()][self::LUMBERJACK]['lvl']}§f/§650\n§6XP§f: §6{$this->cache[$player->getXuid()][self::LUMBERJACK]['xp']}§f/{$xpNeed}\n\n§fComment §6XP§f de ce métier ?\n\n§7- Bois de chêne §f:§6 5XP\n§7- Bois de bouleau §f:§6 7XP\n§7- Bois de la jungle §f:§6 9XP\n§7- Bois d'acacia §f:§6 12XP\n§7- Bois de chêne noir §f:§6 9XP\n";
        }
        if ($job == "Chasseur") {
            $xpNeed = self::XP_LVL[$this->cache[$player->getXuid()][self::HUNTER]['lvl'] + 1];
            return "§fVoici les informations du métier de §6Chasseur.\n\n§fNiveau: §6{$this->cache[$player->getXuid()][self::HUNTER]['lvl']}§f/§650\n§6XP§f: §6{$this->cache[$player->getXuid()][self::HUNTER]['xp']}§f/{$xpNeed}\n\n§fComment §6XP§f de ce métier ?\n\n§7- Tuer un mob §f:§6 100XP"; // \n\n§fChaque §6niveau§f, §6+1%§f de l'expérience d'un joueur que tu tueras te sera donné.\n§fChaque §6niveau§f, §6+1%§f de point te sera attribué lors d'un §6kill§f."
        }
        if ($job == "Fisherman") {
            $xpNeed = self::XP_LVL[$this->cache[$player->getXuid()][self::FISHERMAN]['lvl'] + 1];
            return "§fVoici les informations du métier de §6Pêcheur.\n\n§fNiveau: §6{$this->cache[$player->getXuid()][self::FISHERMAN]['lvl']}§f/§650\n§6XP§f: §6{$this->cache[$player->getXuid()][self::FISHERMAN]['xp']}§f/{$xpNeed}\n\n§fComment §6XP§f de ce métier ?\n\n§7- Pêcher un poisson §f:§6 3 - 30xp"; // \n\n§fChaque §6niveau§f, §6+1%§f de l'expérience d'un joueur que tu tueras te sera donné.\n§fChaque §6niveau§f, §6+1%§f de point te sera attribué lors d'un §6kill§f."
        }
        return null;
    }

    public function addXp(Player $player, string $job, int $xp = 1, $adminJob = false, ?Block $block = null): bool
    {
        $xuid = $player->getXuid();
        if ($player instanceof CustomPlayer) {
            if ($player->getInventory()->getItemInHand() instanceof AbstractWoodenAxe && $job === self::LUMBERJACK) $xp = $xp * 2;
            if (!$adminJob) {
                if ($this->hasX2Xp($job)) {
                    $xp = $xp * 2;
                } else {
                    if (Main::getInstance()->getXpManager()->hasDobble($player)) {
                        $xp = $xp * 2;
                    }
                }
            }
            $this->cacheXp[$player->getXuid()]['xp'] += $xp;
            $jobsPopup = "";
            if ($job === 'minor') $jobsPopup = "Mineur";
            if ($job === self::LUMBERJACK) $jobsPopup = "Bucheron";
            if ($job === self::FISHERMAN) $jobsPopup = "Pêcheur";
            if ($job === self::MURDER) $jobsPopup = "Assassin";
            if ($job === self::FARMER) $jobsPopup = "Farmeur";
            if ($job === self::HUNTER) $jobsPopup = "Chasseur";

            if (!$adminJob) {
                BossBarReloadEvent::$hasJob[$player->getXuid()] = [
                    'jobName' => $jobsPopup,
                    'time' => time() + 30,
                    'xptotal' => $this->cache[$player->getXuid()][$job]['xp'],
                    'xptarget' => self::XP_LVL[$this->cache[$player->getXuid()][$job]['lvl'] + 1]
                ];
            }

            //$player->sendPopup("§e[§6$jobsPopup"."§e] §f| §a+$xp"."xp");
        }


        if (isset($this->cache[$xuid])) {
            $xpOld = $this->cache[$xuid][$job]['xp'];
            $lvlOld = $this->cache[$xuid][$job]['lvl'];
            $xpForNextLvl = self::XP_LVL[$lvlOld + 1];

            if (($xpOld + $xp) >= $xpForNextLvl) {
                $this->cache[$xuid][$job]['lvl']++;
                $this->cache[$xuid][$job]['xp'] = 0;
                $this->giveReward($player, $this->cache[$xuid][$job]['lvl'], $job);
            } else {
                $this->cache[$xuid][$job]['xp'] += $xp;
            }
        }
        return true;
    }

    public function saveAllData(bool $async = false): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $this->saveData($player, $async);
        }
    }

    public function resetXpQuoti(): void
    {
        foreach ($this->cacheXp as $xuid => $values) {
            $this->cacheXp[$xuid]['xp'] = 0;
        }

        SQL::async(static function (RequestAsync $async, mysqli $db): void {
            $db->query("DELETE FROM xp_max;");
        });
    }

    public function safelySaveData(Player $player): void
    {
        if (!isset($this->cache[$player->getXuid()]) || empty($this->cache[$player->getXuid()])) return;
        if (!isset($this->cache[$player->getXuid()])) return;
        $data = $this->cache[$player->getXuid()];
        $xpData = $this->cacheXp[$player->getXuid()] ?? ['time' => time(), 'xp' => 0];
        $xuid = $player->getXuid();


            SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid, $data, $xpData): void {
                $jobs = ['lumberjack', 'farmer', 'minor', 'fisherman', 'hunter', 'murder'];
                foreach ($jobs as $job) {

                    $value = $data[$job];
                    $xp = $value['xp'];
                    $lvl = $value['lvl'];



                    $prepare = $db->prepare("INSERT INTO " . $job . " (xuid, xp, lvl) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE xp = VALUES(xp), lvl = VALUES(lvl)");
                    $prepare->bind_param('sii', $xuid, $xp, $lvl);
                    $prepare->execute();
                }
            });
    }



    public function saveData(Player $player, bool $async = true, ?mysqli $db = null): void
    {
        if (!isset($this->cache[$player->getXuid()]) || empty($this->cache[$player->getXuid()])) return;
        $data = $this->cache[$player->getXuid()] ?? self::PATTERN;
        $xpData = $this->cacheXp[$player->getXuid()] ?? ['time' => time(), 'xp' => 0];
        $xuid = $player->getXuid();
        if ($async) {
            SQL::async(static function (RequestAsync $thread, mysqli $db) use ($xuid, $data, $xpData): void {


                $jobs = ['lumberjack', 'farmer', 'minor', 'fisherman', 'hunter', 'murder'];
                foreach ($jobs as $job) {

                    $value = $data[$job];
                    $xp = $value['xp'];
                    $lvl = $value['lvl'];



                    $prepare = $db->prepare("INSERT INTO " . $job . " (xuid, xp, lvl) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE xp = VALUES(xp), lvl = VALUES(lvl)");
                    $prepare->bind_param('sii', $xuid, $xp, $lvl);
                    $prepare->execute();
                }
            }, static function (RequestAsync $async) use ($player): void {
                if ($player->quit) {
                    unset(Main::getInstance()->getJobsManager()->cacheXp[$player->getXuid()]);
                    unset(Main::getInstance()->getJobsManager()->cache[$player->getXuid()]);
                }
            });
        } else {
            if (is_null($db)) {
                $db = SQL::connection();
                $jobs = ['lumberjack', 'farmer', 'minor', 'fisherman', 'hunter', 'murder'];
                foreach ($jobs as $job) {

                    $value = $data[$job];
                    $xp = $value['xp'];
                    $lvl = $value['lvl'];


                    $prepare = $db->prepare("INSERT INTO " . $job . " (xuid, xp, lvl) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE xp = VALUES(xp), lvl = VALUES(lvl)");
                    $prepare->bind_param('sii', $xuid, $xp, $lvl);
                    $prepare->execute();
                }
                $db->close();
            } else {

                $jobs = ['lumberjack', 'farmer', 'minor', 'fisherman', 'hunter', 'murder'];
                foreach ($jobs as $job) {

                    $value = $data[$job];
                    $xp = $value['xp'];
                    $lvl = $value['lvl'];


                    $prepare = $db->prepare("INSERT INTO " . $job . " (xuid, xp, lvl) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE xp = VALUES(xp), lvl = VALUES(lvl)");
                    $prepare->bind_param('sii', $xuid, $xp, $lvl);
                    $prepare->execute();
                }
            }
        }
    }

    public function resetJob(Player $player, string $job): void
    {
        $this->cache[$player->getXuid()][$job]["xp"] = 0;
        $this->cache[$player->getXuid()][$job]["lvl"] = 0;
        //TODO : reset les craft
    }

    public function addLevel(Player $player, string $job, int $amount = 1): void
    {
        $lvl = $this->cache[$player->getXuid()][$job]['lvl'] + $amount;
        if (isset(self::XP_LVL[$lvl])) {
            $this->cache[$player->getXuid()][$job]["xp"] = 0;
            $this->cache[$player->getXuid()][$job]["lvl"] = $lvl;

        }
        // TODO: reset les craft
    }

    public function removeLevel(Player $player, string $job, int $amount = 1): void
    {
        $lvl = $this->cache[$player->getXuid()][$job]['lvl'] - $amount;
        $this->cache[$player->getXuid()][$job]["xp"] = 0;
        if (isset(self::XP_LVL[$lvl])) {
            $this->cache[$player->getXuid()][$job]["lvl"] = $lvl;
        } else {
            $this->cache[$player->getXuid()][$job]["lvl"] = 0;
        }
        //TODO: reset les craft
    }
}