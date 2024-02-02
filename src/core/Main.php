<?php

namespace core;

use core\blocks\BlockHistoryData;
use core\blocks\BlockManager;
use core\blocks\DurabilityObsidian;
use core\commands\CommandsManager;
use core\crafts\Furnace;
use core\crafts\Recipes;
use core\enchantments\EnchantmentManager;
use core\entities\EntityManager;
use core\events\LogEvent;
use core\items\ExtraVanillaItem;
use core\items\ItemManager;
use core\listeners\ListenerManager;
use core\managers\area\AreaManager;
use core\managers\blacklist\BlacklistManager;
use core\managers\bourse\BourseManager;
use core\managers\box\BoxManager;
use core\managers\break\BlockBreak;
use core\managers\chestRefill\ChestRefill;
use core\managers\connexion\ConnexionManager;
use core\managers\cosmetic\CosmeticManager;
use core\managers\crafts\CraftsManager;
use core\managers\data\DataManager;
use core\managers\dc\DcManager;
use core\managers\discord\DiscordManager;
use core\managers\easteregg\EasterEggManager;
use core\managers\economy\EconomyManager;
use core\managers\enderChest\EnderChestSlot;
use core\managers\exchange\ExchangeManager;
use core\managers\factions\FactionManager;
use core\managers\grafana\GrafanaManager;
use core\managers\hdv\HdvManager;
use core\managers\homes\HomeManager;
use core\managers\inventory\InventoryManager;
use core\managers\jobs\JobsManager;
use core\managers\kits\KitManager;
use core\managers\portal\PortalManager;
use core\managers\primes\PrimeManager;
use core\managers\ranks\RankManager;
use core\managers\sanctions\SanctionManager;
use core\managers\scoreboard\ScoreboardManager;
use core\managers\settings\SettingsManager;
use core\managers\shop\ShopManager;
use core\managers\skin\SkinManager;
use core\managers\skins\SkinManager2;
use core\managers\staff\StaffManager;
use core\managers\stats\StatsManager;
use core\managers\sync\SyncDatabaseManager;
use core\managers\tebex\TebexManager;
use core\managers\topluck\TopLuckManager;
use core\managers\user\UserManager;
use core\managers\vote\VoteParty;
use core\managers\warp\WarpManager;
use core\managers\wiki\WikiManager;
use core\managers\xp\XpManager;
use core\services\TpaService;
use core\storage\JobsStorageCache;
use core\storage\PlayerData;
use core\storage\QuestStorageCache;
use core\tasks\ClearlaggTask;
use core\tasks\RecurentMessageTask;
use core\tasks\SeeChunkTask;
use core\tasks\staff\VanishTask;
use core\tasks\TaskCheckupHours;
use core\tasks\TaskSeconds;
use core\thread\curl\Curl;
use core\thread\ThreadManager;
use core\traits\ManagerTrait;
use core\traits\ServiceTrait;
use core\traits\SoundTrait;
use core\utils\Utils;
use cosmicpe\blockdata\BlockDataFactory;
use cosmicpe\blockdata\world\BlockDataWorldManager;
use IvanCraft623\MobPlugin\MobPlugin;
use muqsit\tebex\Loader;
use pocketmine\data\bedrock\item\ItemTypeNames;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\event\EventPriority;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackInfoEntry;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginDescription;
use pocketmine\plugin\PluginLoader;
use pocketmine\plugin\ResourceProvider;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\ServerProperties;
use pocketmine\utils\Config;
use pocketmine\utils\InternetRequestResult;
use pocketmine\utils\TextFormat;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use tedo0627\inventoryui\InventoryUI;

class Main extends PluginBase
{
    use SoundTrait;

    const XUID_SERVER = "XUID-GOLDRUSH-SERVER-GAME1";

    public const DEFAULT_CHUNK_SIZE = 512 * 1024;
    public const DEFAULT_PACKET_SEND_INTERVAL = 25;

    public static array $TELL = [];
    public static array $packSendQueue = [];
    public static array $inCraftingTableCommand = [];
    public static array $ANTI_FREE_XP = [];

    private static self $instance;
    public PlayerData $storageData;
    public string $file;

    public JobsStorageCache $jobsStorage;
    public QuestStorageCache $questStorage;
    public BlockDataWorldManager $blockDataService;
    public ClearlaggTask $clearlagg;

    public EnchantmentManager $enchantManager;

    public ThreadManager $threadManager;

    use ManagerTrait;
    use ServiceTrait;
+
    public array $troll = [];


    public MobPlugin $mobPlugin;

    public function __construct(PluginLoader $loader, Server $server, PluginDescription $description, string $dataFolder, string $file, ResourceProvider $resourceProvider)
    {
        $this->mobPlugin = new MobPlugin($loader, $server, $description, $dataFolder, $file, $resourceProvider);
        parent::__construct($loader, $server, $description, $dataFolder, $file, $resourceProvider);
    }

    protected function onLoad(): void
    {
        $this->mobPlugin->onLoad();



        # feature de fou
        @mkdir($this->getDataFolder() . "temp/");
        @mkdir($this->getDataFolder() . "data/");
        self::$instance = $this;
        $this->file = $this->getFile();
        $this->storageData = new PlayerData();

        $this->saveDefaultConfig();


        $this->setConnexionManager(new ConnexionManager($this));
        $this->setTebexManager(new TebexManager($this));
        $this->setXpManager(new XpManager($this));
        $this->setChestRefillManager(new ChestRefill($this));
        $this->setDiscordManager(new DiscordManager($this));
        $this->setCosmeticManager(new CosmeticManager($this));
        $this->setDatabaseSyncManager(new SyncDatabaseManager($this));
        $this->setGrafanaManager(new GrafanaManager($this));
        $this->setInventoryManager(new InventoryManager($this));
        $this->setWarpManager(new WarpManager($this));
        $this->setHomeManager(new HomeManager($this));
        $this->setDataManager(new DataManager($this));
        $this->setAreaManager(new AreaManager($this));
        $this->setRankManager(new RankManager($this));
        $this->setEconomyManager(new EconomyManager($this));
        $this->setPrimeManager(new PrimeManager($this));
        $this->setStatsManager(new StatsManager($this));
        $this->setSanctionManager(new SanctionManager($this));
        $this->setFactionManager(new FactionManager($this));
        $this->setEnderChestManager(new EnderChestSlot($this));
        $this->setBoxManager(new BoxManager($this));
        $this->setCraftManager(new CraftsManager($this));
        $this->setShopManager(new ShopManager($this));
        $this->setJobsManager(new JobsManager($this));
        $this->setPortalManager(new PortalManager($this));
        $this->setKitManager(new KitManager($this));
        $this->setBourseManager(new BourseManager($this));
        $this->setVotePartyManager(new VoteParty($this));
        $this->setHdvManager(new HdvManager($this));
        $this->setWikiManager(new WikiManager($this));
        $this->setSkinManager(new SkinManager($this));
        $this->setSkinManager2(new SkinManager2($this));
        $this->setSettingsManager(new SettingsManager($this));
        $this->setExchangeManager(new ExchangeManager($this));
        $this->setScoreboardManager(new ScoreboardManager($this));
        $this->setStaffManager(new StaffManager($this));
        $this->setTopLuckManager(new TopLuckManager($this));
        $this->setEasterEggManager(new EasterEggManager($this));
        $this->setBlacklistManager(new BlacklistManager($this));
        $this->setUserManager(new UserManager($this));
        $this->setDcManager(new DcManager($this));

        $this->tpa = new TpaService();
        $this->questStorage = new QuestStorageCache();
        $this->jobsStorage = new JobsStorageCache();

        $this->enchantManager = new EnchantmentManager();
        $this->threadManager = new ThreadManager();

        foreach ($this->getServer()->getResourcePackManager()->getResourceStack() as $resourcePack) {
            $resourcePack->getPackName();
        }

        $this->getScoreboardManager()->getScoreboardApi()->createScoreboard("objectif", "§a");

    }


    public function unregisterCommands(): void
    {
        $unregister = ["ban", "tell", 'me'];
        foreach ($unregister as $cmd) {
            $this->unregisterCmd($cmd);
        }
    }

    private function unregisterCmd(string $cmd): bool
    {
        $map = $this->getServer()->getCommandMap();
        $command = $map->getCommand($cmd);
        if ($command !== null) {
            $command->setLabel("old_" . $cmd);
            $map->unregister($command);
            return true;
        }
        $this->getServer()->getLogger()->error('Could not delete "' . $cmd . '" command.');
        return false;
    }


    private array $encryptionKeys = [

    ];

    protected function onEnable(): void
    {
        $this->mobPlugin->onEnable();

        Curl::register($this);
        Server::getInstance()->getConfigGroup()->setConfigString(ServerProperties::MOTD, "§6Gold§fRush §cV1");
        $this->unregisterCommands();
        $this->enchantManager->startup();
        InventoryUI::setup($this);
        $this->saveDefaultConfig();
        $this->saveResource("img.png");
        @mkdir($this->getDataFolder() . "data/");
        @mkdir($this->getDataFolder() . "saveskin/");
        @mkdir($this->getDataFolder() . "logs/");
        @mkdir($this->getDataFolder() . "skins/players/", 0777, true);
        $this->saveResource("data/wiki.json");

        foreach (array_diff(scandir($this->getServer()->getDataPath() . "worlds"), ["..", "."]) as $levelName) {
            $this->getServer()->getWorldManager()->loadWorld($levelName);
        }

        $this->saveResource('cosmetic/cape_alpha.png');
        $this->saveResource('cosmetic/cape_beta.png');
        $this->saveResource('cosmetic/cape_bugs.png');
        $this->saveResource('cosmetic/cape_bugs_beta.png');
        $this->saveResource('cosmetic/cape_bugs_gold.png');
        $this->saveResource('cosmetic/cape_goldrush.png');
        $this->saveResource('cosmetic/cape_love.png');
        $this->saveResource('cosmetic/cape_noel.png');



        # NE PAS SUPPRIMER | CODE POUR GEN LES IMAGES DE CRAFT
        /*
        @mkdir($this->getDataFolder() . "crafts/items");
        foreach (array_diff(scandir($this->getServer()->getDataPath() . "plugins/goldrush-v1/resources/crafts/items/"), ["..", "."]) as $textureFile) {
            $this->saveResource("crafts/items/" . $textureFile);
        }
        */

        (new ListenerManager())->init();
        (new CommandsManager())->init();
        (new BlockManager());
        (new ItemManager())->init();
        (new EntityManager())->init();
        (new Furnace())->init();
        (new Recipes())->init();
        $this->setBlockBreakManager(new BlockBreak($this));


        // pack solution for download big MO
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(static function (): void {
            foreach (self::$packSendQueue as $entry) {
                $entry->tick(Server::getInstance()->getTick());
            }
        }), 1);

        $this->getScheduler()->scheduleRepeatingTask(new TaskCheckupHours(), 20 * 50);
        $this->getScheduler()->scheduleRepeatingTask(new TaskSeconds(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new VanishTask(), 20);
        $this->getScheduler()->scheduleRepeatingTask(new SeeChunkTask(), 10);
        $this->getScheduler()->scheduleRepeatingTask(new RecurentMessageTask(), 10);

        $this->clearlagg = new ClearlaggTask();
        $this->getScheduler()->scheduleRepeatingTask($this->clearlagg, 20);

        BlockDataFactory::register("blockhistory", BlockHistoryData::class);
        BlockDataFactory::register("durabilityobsidian", DurabilityObsidian::class);
        $this->blockDataService = BlockDataWorldManager::create($this);

        (new LogEvent("Server started", "SERVER"))->call();

        Utils::timeout(function (): void {
            $bourse = $this->getBourseManager()->getBourse();

            $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
            $sell = $config->get('shop');
            foreach ($sell['Farming']['items'] as $index => $values) {
                $id = $values['idMeta'];
                if (in_array($id, array_keys($bourse))) {
                    if (isset($values['sell'])) {
                        $sell['Farming']['items'][$index]['sell'] = $bourse[$id];
                    }
                }
            }

            $config->set('shop', $sell);
            $config->save();
        }, 40);




    }


    public function checkVPN(string $username, string $address) : void{
        $this->getLogger()->info("CHECK VPN/PROXY : $username:$address");
        $all = $this->getConfig()->getAll();
        if (in_array($address, $all['by-pass-ip'])) return;

        if(($key = $this->getConfig()->get("api-key")) === ""){
            $url = "https://vpnapi.io/api/$address?key=6b614c7f023e4a8abd89104a6216dfc1";
        } else {
            $url = "https://vpnapi.io/api/$address?key=6b614c7f023e4a8abd89104a6216dfc1";
        }
        Curl::getRequest($url, 10, ["Content-Type: application/json"], function(?InternetRequestResult $result) use ($username, $address) : void {
            if($result !== null){
                if(($response = json_decode($result->getBody(), true)) !== null){

                    if(in_array($address, $this->getConfig()->get("by-pass-ip"), true)) return;

                    if(isset($response["message"]) && $response["message"] !== ""){
                        $this->getLogger()->notice(TextFormat::RED . "Unable to check ip: " . TextFormat::AQUA . $address . TextFormat::RED . " Error: " . TextFormat::DARK_RED . $response["message"]);
                        return;
                    }



                    if ($this->getConfig()->get('proxy-check')) {
                        if(isset($response["security"]["vpn"]) && isset($response["security"]["proxy"]) && isset($response["security"]["tor"]) && isset($response["security"]["relay"])){
                            if($response["security"]["vpn"] === true || $response["security"]["proxy"] === true || $response["security"]["tor"] === true || $response["security"]["relay"] === true){
                                if(($player = Main::getInstance()->getServer()->getPlayerExact($username)) !== null && $player->isOnline() && $player->spawned){
                                    $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player){
                                        Main::getInstance()->getSanctionManager()->ban($player->getXuid(), "VPN/PROXY : Discord, discord.gg/goldrush", time() + 60 * 60 * 24 * 7);
                                    }), 2);
                                    return;
                                }
                            }
                        }
                    } else {
                        if(isset($response["security"]["vpn"]) && isset($response["security"]["tor"]) && isset($response["security"]["relay"])){
                            if($response["security"]["vpn"] === true || $response["security"]["tor"] === true || $response["security"]["relay"] === true){
                                if(($player = Main::getInstance()->getServer()->getPlayerExact($username)) !== null && $player->isOnline() && $player->spawned){
                                    $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player){
                                        Main::getInstance()->getSanctionManager()->ban($player->getXuid(), "VPN : Discord, discord.gg/goldrush", time() + 60 * 60 * 24 * 7);
                                    }), 2);
                                    return;
                                }
                            }
                        }
                    }
                }
            }
        });
    }



    protected function onDisable(): void
    {
        $this->mobPlugin->onDisable();


        foreach ($this->getServer()->getWorldManager()->getWorlds() as $world) {
            $world->save(true);
        }


        $this->getDatabaseSyncManager()->stop();
        $this->getPortalManager()->save();
        $this->getAreaManager()->saveAllData();
        $this->getFactionManager()->saveAllData();
        $this->getSanctionManager()->saveAllData(true);
        $this->getBourseManager()->saveAllData();


        # BIG CLEAN CODE OPTIMIZE MDR
        $allPlayers = Server::getInstance()->getOnlinePlayers();
        foreach ($allPlayers as $player) {
            $this->getInventoryManager()->saveInventory($player, false, true);
            $this->getJobsManager()->saveData($player, false);
            $this->getEconomyManager()->saveData($player, false);
            $this->getStatsManager()->saveData($player, false);
            $this->getRankManager()->saveData($player, false);
            $this->getDataManager()->saveUser($player, false);
            $this->getHomeManager()->saveDataNotAsync($player);
            $this->getSettingsManager()->saveData($player, false);
            $this->getEnderChestManager()->saveUser($player, false);
            $this->getScoreboardManager()->saveData($player, false);
            $this->getSkinManager2()->saveData($player, false);
            $this->getPrimeManager()->saveData($player, false);
            $this->jobsStorage->saveUserCache($player, false);
        }


        (new LogEvent("Server stopped", "SERVER"))->call();
        sleep(1);
        $this->threadManager->stop();


        Server::getInstance()->getLogger()->info("§a[INFORMATION] §fLa sauvegarde SQL/worlds du plugin s'est effectué sans erreur.");
    }

    public static function getInstance(): Main
    {
        return self::$instance;
    }
}