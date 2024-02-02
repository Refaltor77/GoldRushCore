<?php

namespace core\traits;

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
use core\managers\Manager;
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

trait ManagerTrait
{

    private InventoryManager|Manager $inventoryManager;
    private HomeManager|Manager $homeManager;
    private DataManager|Manager $dataManager;
    private AreaManager|Manager $areaManager;
    private RankManager|Manager $rankManager;
    private EconomyManager|Manager $economyManager;
    private SanctionManager|Manager $sanctionManager;
    private StatsManager|Manager $statsManager;
    private FactionManager|Manager $factionManager;
    private CraftsManager|Manager $craftManager;
    private JobsManager|Manager $jobsManager;
    private ShopManager|Manager $shopManager;
    private PortalManager|Manager $portalManager;
    private EnderChestSlot|Manager $enderChestManager;
    private KitManager|Manager $kitManager;
    private VoteParty|Manager $voteParty;
    private HdvManager|Manager $hdvManager;
    private WikiManager|Manager $wikiManager;
    private SkinManager|Manager $skinManager;
    private SettingsManager|Manager $settingsManager;
    private ExchangeManager|Manager $exchangeManager;
    private ScoreboardManager|Manager $scoreboardManager;
    private StaffManager|Manager $staffManager;
    private TopLuckManager|Manager $topLuckManager;
    private WarpManager|Manager $warpManager;
    private EasterEggManager|Manager $eastereggManager;
    private CosmeticManager|Manager $cosmeticManager;
    private SyncDatabaseManager|Manager $syncManager;
    private BlacklistManager|Manager $blacklistManager;
    private UserManager|Manager $userManager;
    private BourseManager|Manager $bourseManager;
    private SkinManager2|Manager $skinManager2;
    private PrimeManager|Manager $primeManager;
    private BoxManager|Manager $boxManager;
    private DiscordManager|Manager $discordManager;
    private GrafanaManager|Manager $grafanaManager;
    private ChestRefill|Manager $chestRefill;
    private BlockBreak|Manager $blockBreak;
    private XpManager|Manager $xpManager;
    private TebexManager|Manager $tebexManager;
    private ConnexionManager|Manager $connexionManager;
    private DcManager|Manager $dcManager;



    public function setConnexionManager(Manager $manager): void {$this->connexionManager = $manager;}
    public function getConnexionManager(): ConnexionManager {return $this->connexionManager;}

    public function setTebexManager(Manager $manager): void {$this->tebexManager = $manager;}
    public function getTebexManager(): TebexManager {return $this->tebexManager;}

    public function setBlockBreakManager(Manager $manager): void {$this->blockBreak = $manager;}
    public function getBlockBreakManager(): BlockBreak {return $this->blockBreak;}

    public function setXpManager(Manager $manager): void {$this->xpManager = $manager;}
    public function getXpManager(): XpManager {return $this->xpManager;}

    public function setChestRefillManager(Manager $manager): void {$this->chestRefill = $manager;}
    public function getChestRefillManager(): ChestRefill {return $this->chestRefill;}

    public function setGrafanaManager(Manager $manager): void {$this->grafanaManager = $manager;}
    public function getGrafanaManager(): GrafanaManager {return $this->grafanaManager;}

    public function setDiscordManager(Manager $manager): void {$this->discordManager = $manager;}
    public function getDiscordManager(): DiscordManager {return $this->discordManager;}

    public function setBoxManager(Manager $manager): void {$this->boxManager = $manager;}
    public function getBoxManager(): BoxManager {return $this->boxManager;}

    public function setSkinManager2(Manager $manager): void {$this->skinManager2 = $manager;}
    public function getSkinManager2(): SkinManager2 {return $this->skinManager2;}

    public function setPrimeManager(Manager $manager): void {$this->primeManager = $manager;}
    public function getPrimeManager(): PrimeManager {return $this->primeManager;}

    public function setDatabaseSyncManager(Manager $manager): void {$this->syncManager = $manager;}
    public function getDatabaseSyncManager(): SyncDatabaseManager {return $this->syncManager;}

    public function setBourseManager(Manager $manager): void {$this->bourseManager = $manager;}
    public function getBourseManager(): BourseManager {return $this->bourseManager;}

    public function setCosmeticManager(Manager $manager): void {$this->cosmeticManager = $manager;}
    public function getCosmeticManager(): CosmeticManager {return $this->cosmeticManager;}

    public function setEasterEggManager(Manager $manager): void {$this->eastereggManager = $manager;}
    public function getEasterEggManager(): EasterEggManager {return $this->eastereggManager;}

    public function setWarpManager(Manager $manager): void {$this->warpManager = $manager;}
    public function getWarpManager(): WarpManager {return $this->warpManager;}

    public function setTopLuckManager(Manager $manager): void {$this->topLuckManager = $manager;}
    public function getTopLuckManager(): TopLuckManager {return $this->topLuckManager;}

    public function setScoreboardManager(Manager $manager): void {$this->scoreboardManager = $manager;}
    public function getScoreboardManager(): ScoreboardManager {return $this->scoreboardManager;}

    public function setStaffManager(Manager $manager): void {$this->staffManager = $manager;}
    public function getStaffManager(): StaffManager {return $this->staffManager;}

    public function setExchangeManager(Manager $manager): void {$this->exchangeManager = $manager;}
    public function getExchangeManager(): ExchangeManager {return $this->exchangeManager;}

    public function setInventoryManager(Manager $manager): void {$this->inventoryManager = $manager;}
    public function getInventoryManager(): InventoryManager {return $this->inventoryManager;}

    public function setHdvManager(Manager $manager): void {$this->hdvManager = $manager;}
    public function getHdvManager(): HdvManager {return $this->hdvManager;}

    public function setVotePartyManager(Manager $manager): void {$this->voteParty = $manager;}
    public function getVotePartyManager(): VoteParty {return $this->voteParty;}

    public function setKitManager(Manager $manager): void {$this->kitManager = $manager;}
    public function getKitManager(): KitManager {return $this->kitManager;}

    public function setEnderChestManager(Manager $manager): void {$this->enderChestManager = $manager;}
    public function getEnderChestManager(): EnderChestSlot {return $this->enderChestManager;}

    public function setJobsManager(Manager $manager): void {$this->jobsManager = $manager;}
    public function getJobsManager(): JobsManager {return $this->jobsManager;}


    public function setHomeManager(Manager $manager): void {$this->homeManager = $manager;}
    public function getHomeManager(): HomeManager {return $this->homeManager;}

    public function setDataManager(Manager $manager): void {$this->dataManager = $manager;}
    public function getDataManager(): DataManager {return $this->dataManager;}

    public function setAreaManager(Manager $manager): void {$this->areaManager = $manager;}
    public function getAreaManager(): AreaManager {return $this->areaManager;}

    public function setRankManager(Manager $manager): void {$this->rankManager = $manager;}
    public function getRankManager(): RankManager {return $this->rankManager;}

    public function setEconomyManager(Manager $manager): void {$this->economyManager = $manager;}
    public function getEconomyManager(): EconomyManager {return $this->economyManager;}

    public function setSanctionManager(Manager $manager): void {$this->sanctionManager = $manager;}
    public function getSanctionManager(): SanctionManager {return $this->sanctionManager;}

    public function setStatsManager(Manager $manager): void {$this->statsManager = $manager;}
    public function getStatsManager(): StatsManager {return $this->statsManager;}

    public function setFactionManager(Manager $manager): void {$this->factionManager = $manager;}
    public function getFactionManager(): FactionManager {return $this->factionManager;}

    public function setCraftManager(Manager $manager): void {$this->craftManager = $manager;}
    public function getCraftManager(): CraftsManager{return $this->craftManager;}

    public function setShopManager(Manager $manager): void {$this->shopManager = $manager;}
    public function getShopManager(): ShopManager{return $this->shopManager;}

    public function setPortalManager(Manager $manager): void {$this->portalManager = $manager;}
    public function getPortalManager(): PortalManager{return $this->portalManager;}
    public function getWikiManager(): WikiManager{return $this->wikiManager;}
    public function setWikiManager(Manager $manager): void {$this->wikiManager = $manager;}

    public function getSkinManager(): SkinManager{return $this->skinManager;}
    public function setSkinManager(Manager $manager): void {$this->skinManager = $manager;}

    public function setSettingsManager(Manager $manager): void {$this->settingsManager = $manager;}
    public function getSettingsManager(): SettingsManager {return $this->settingsManager;}

    public function setBlacklistManager(Manager $manager): void {$this->blacklistManager = $manager;}
    public function getBlacklistManager(): BlacklistManager {return $this->blacklistManager;}
    public function getUserManager(): UserManager {return $this->userManager;}
    public function setUserManager(Manager $manager): void {$this->userManager = $manager;}

    public function getDcManager(): DcManager {return $this->dcManager;}
    public function setDcManager(Manager $manager): void {$this->dcManager = $manager;}
}