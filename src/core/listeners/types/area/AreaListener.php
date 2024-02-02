<?php

namespace core\listeners\types\area;

use anticheat\Loader;
use anticheat\sessions\CheatType;
use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Input;
use core\api\form\elements\Toggle;
use core\cinematic\Cinematics;
use core\commands\executors\AreaCommands;
use core\commands\executors\PortalCommands;
use core\entities\Peste;
use core\items\backpacks\BackpackFarm;
use core\items\backpacks\BackpackFossil;
use core\items\backpacks\BackpackOre;
use core\items\buckets\BucketCopper;
use core\items\buckets\BucketCopperLava;
use core\items\buckets\BucketEmptyCopper;
use core\items\buckets\BucketEmptyGold;
use core\items\buckets\BucketEmptyPlatinum;
use core\items\buckets\BucketGold;
use core\items\buckets\BucketGoldLava;
use core\items\buckets\BucketPlatinum;
use core\items\buckets\BucketPlatinumLava;
use core\items\tools\VoidStone;
use core\Main;
use core\managers\area\AreaBuild;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\UtilsTrait;
use core\utils\Utils;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Chest;
use pocketmine\block\Lava;
use pocketmine\block\NetherPortal;
use pocketmine\block\Opaque;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\event\block\BlockItemPickupEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\item\Axe;
use pocketmine\item\Durable;
use pocketmine\item\ItemTypeIds;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;

class AreaListener implements Listener
{
    const max_x = 9999;
    const max_z = 9999;
    const min_x = -9999;
    const min_z = -9999;
    public static array $handler = [];
    private static array $isInPortal = [];
    private array $microCache;

    public function __construct()
    {
        $this->microCache = ['name' => []];
    }

    public function onMove(PlayerMoveEvent $event): void {

        if ($event->getTo()->distance($event->getFrom()) >= 0.1) {
            $player = $event->getPlayer();
            if ($player->getGamemode()->equals(GameMode::SPECTATOR())) return;

            $blockDown = $player->getWorld()->getBlock($player->getPosition());
            $blockTop = $player->getWorld()->getBlock($player->getEyePos());
            if ($blockDown instanceof Opaque || $blockTop instanceof Opaque) {
                $event->cancel();
            }
        }
    }

    public function onGang(EntityItemPickupEvent $event): void
    {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            if (isset(Main::getInstance()->sanction->cacheStaff[$player->getXuid()])) {
                $event->cancel();
            }
        }
    }

    public function enSignChange(SignChangeEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $api = Main::getInstance()->getAreaManager();

        if ($api->isInArea($block->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($block->getPosition());
            if (!$flags['break']) {
                {
                    $event->cancel();
                }
            }
        }
    }


    public function onGang2(BlockItemPickupEvent $event): void
    {
        $player = $event->getOrigin();
        if ($player instanceof Player) {
            if (isset(Main::getInstance()->sanction->cacheStaff[$player->getXuid()])) {
                $event->cancel();
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param BlockBreakEvent $event
     */
    public function onBreak(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $api = Main::getInstance()->getAreaManager();

        if ($api->isInArea($block->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($block->getPosition());
            if (!$flags['break']) {
                if (Server::getInstance()->isOp($player->getName()) || $player->hasPermission('area.event')) {

                } else {
                    $event->cancel();
                }
            }
        }
        $uuid = $player->getUniqueId()->getBytes();
        if (isset(AreaCommands::$fastCache[$uuid])) {
            if (is_null(AreaCommands::$fastCache[$uuid]['1'])) {
                AreaCommands::$fastCache[$uuid]['1'] = $block->getPosition();
                $player->sendMessage(Messages::message("§aLa première position de votre zone est définie."));
                $event->cancel();
            } else if (is_null(AreaCommands::$fastCache[$uuid]['2'])) {
                AreaCommands::$fastCache[$uuid]['2'] = $block->getPosition();
                $player->sendMessage(Messages::message("§aLa deuxième position de votre zone est créée, allez à la création de votre zone !"));
                $event->cancel();
                $player->sendForm(new CustomForm(
                    '§6- §eCréation de la zone §6-',
                    [
                        new Input('§6» §eNom de la zone', 'Spawn'),
                        new Toggle('§6» §ePVP', false),
                        new Toggle('§6» §ePlacer des blocs', false),
                        new Toggle('§6» §eCasser des blocs', false),
                        new Toggle('§6» §eFaim', true),
                        new Toggle('§6» §eJeter des items', true),
                        new Toggle('§6» §eLes tnt exploses', false),
                        new Toggle('§6» §eCommands [/]', true),
                        new Toggle('§6» §eEnvoi de message dans le chat', true),
                        new Toggle('§6» §eConsommer un item', false),
                        new Input('§6» §ePriorité', '0'),
                    ],
                    function (Player $player, CustomFormResponse $response) use ($uuid, $api): void {
                        list($name, $pvp, $place, $break, $hunger, $drop, $tnt, $cmd, $chat, $consume, $prio) = $response->getValues();
                        if (isset($api->cache[$name])) {
                            $player->sendMessage(Messages::message("§cLe nom de la zone existe déjà !"));
                            return;
                        }
                        $flags = AreaBuild::createBaseFlags();
                        $flags['pvp'] = $pvp;
                        $flags['place'] = $place;
                        $flags['break'] = $break;
                        $flags['hunger'] = $hunger;
                        $flags['dropItem'] = $drop;
                        $flags['tnt'] = $tnt;
                        $flags['cmd'] = $cmd;
                        $flags['chat'] = $chat;
                        $flags['consume'] = $consume;
                        if (!(int)$prio) $prio = 0;
                        $area = new AreaBuild(AreaCommands::$fastCache[$uuid]['1'], AreaCommands::$fastCache[$uuid]['2'], $flags, $name, intval($prio) ?? 0);
                        Main::getInstance()->getAreaManager()->createArea($area);
                        $player->sendMessage(Messages::message("§aLa zone §6$name §aa été créé avec succès !"));
                        unset(AreaCommands::$fastCache[$uuid]);
                    }));
            }
        } else if (isset(AreaCommands::$fastCache2[$uuid])) {
            if (is_null(AreaCommands::$fastCache2[$uuid]['1'])) {
                AreaCommands::$fastCache2[$uuid]['1'] = $block->getPosition();
                $player->sendMessage("§6[1 (modification)] §aLa première position de votre zone est définie.");
                $event->cancel();
            } else if (is_null(AreaCommands::$fastCache2[$uuid]['2'])) {
                AreaCommands::$fastCache2[$uuid]['2'] = $block->getPosition();
                Main::getInstance()->getAreaManager()->setPositionByName(AreaCommands::$fastCache2[$uuid]['name'], AreaCommands::$fastCache2[$uuid]['1'], AreaCommands::$fastCache2[$uuid]['2']);
                $player->sendMessage(Messages::message("§aLes nouvelles positions de la zone §6" . AreaCommands::$fastCache2[$uuid]['name'] . '§a sont mises à jour !'));
                $event->cancel();
                unset(AreaCommands::$fastCache2[$uuid]);
            }
        } else if (isset(PortalCommands::$fastCache[$player->getUniqueId()->toString()])) {
            if (is_null(PortalCommands::$fastCache[$player->getUniqueId()->toString()]['1'])) {
                PortalCommands::$fastCache[$player->getUniqueId()->toString()]['1'] = $block->getPosition();
                $player->sendMessage(Messages::message("§aLa première position de votre zone est définie."));
                $event->cancel();
            } else if (is_null(PortalCommands::$fastCache[$player->getUniqueId()->toString()]['2'])) {
                PortalCommands::$fastCache[$player->getUniqueId()->toString()]['2'] = $block->getPosition();
                $event->cancel();
                $player->sendForm(
                    new CustomForm('§f- §6GoldRush Portail §f-', [
                        new Input('§6» §eNom du portail', 'Spawn'),
                        new Input("§6» §ePositions", 'x:y:z:world'),
                    ], function (Player $player, CustomFormResponse $response): void {
                        $api = Main::getInstance()->getPortalManager();
                        list($name, $pos) = $response->getValues();
                        if ($api->existPortal($name)) {
                            $player->sendMessage("§cLe portail existe déjà.");
                            return;
                        }
                        $api->createPortal(PortalCommands::$fastCache[$player->getUniqueId()->toString()]['1'], PortalCommands::$fastCache[$player->getUniqueId()->toString()]['2'], $name, $pos);
                        unset(PortalCommands::$fastCache[$player->getUniqueId()->toString()]);
                    })
                );
            }
        } else if (isset(PortalCommands::$fastCache2[$uuid = $player->getUniqueId()->toString()])) {
            if (is_null(PortalCommands::$fastCache2[$uuid]['1'])) {
                PortalCommands::$fastCache2[$uuid]['1'] = $block->getPosition();
                $player->sendMessage("§6[1 (modification)] §aLa première position de votre portail est définie.");
                $event->cancel();
            } else if (is_null(PortalCommands::$fastCache2[$uuid]['2'])) {
                PortalCommands::$fastCache2[$uuid]['2'] = $block->getPosition();
                Main::getInstance()->getPortalManager()->setPositionByName(PortalCommands::$fastCache2[$uuid]['name'], PortalCommands::$fastCache2[$uuid]['1'], PortalCommands::$fastCache2[$uuid]['2']);
                $player->sendMessage(Messages::message("§aLes nouvelles positions de votre portail §6" . PortalCommands::$fastCache2[$uuid]['name'] . '§a sont mises à jour !'));
                $event->cancel();
                unset(PortalCommands::$fastCache2[$uuid]);
            }
        }
    }

    public function onUpdateeeee(BlockGrowEvent $event): void
    {
        $pos = $event->getBlock()->getPosition();
        if (Main::getInstance()->getAreaManager()->isInArea($pos)) {
            if (Main::getInstance()->getAreaManager()->getAreaNamePriority($pos) === 'Spawn' || Main::getInstance()->getAreaManager()->getAreaNamePriority($pos) === 'Minage') {
                $event->cancel();
            }
        }
    }

    public function onDeath(PlayerDeathEvent $event): void
    {
        if (isset(self::$handler[$event->getPlayer()->getXuid()])) {
            unset(self::$handler[$event->getPlayer()->getXuid()]);
        }
    }

    public function onRespawn(PlayerRespawnEvent $event): void
    {
        if (isset(self::$handler[$event->getPlayer()->getXuid()])) {
            unset(self::$handler[$event->getPlayer()->getXuid()]);
        }
    }

    public function onSpawn(PlayerJoinEvent $event): void
    {
        if (isset(self::$handler[$event->getPlayer()->getXuid()])) {
            unset(self::$handler[$event->getPlayer()->getXuid()]);
        }
    }

    use UtilsTrait;

    public function onQuit(PlayerQuitEvent $event): void
    {
        if (isset(self::$handler[$event->getPlayer()->getXuid()])) {
            unset(self::$handler[$event->getPlayer()->getXuid()]);
        }
    }


    public function onMove2(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $api = Main::getInstance()->getAreaManager();
        $pos = $player->getPosition();
        if (!in_array(Main::getInstance()->getRankManager()->getRankPriority($player), ["MODO+" => "MODO+", "ADMIN" => "ADMIN"])) {
            if ($pos->getX() <= self::min_x || $pos->getX() >= self::max_x) {
                $player->sendMessage(Messages::message("§cLimite de la map atteinte."));
                $event->cancel();
            }
            if ($pos->getZ() <= self::min_z || $pos->getZ() >= self::max_z) {
                $player->sendMessage(Messages::message("§cLimite de la map atteinte."));
                $event->cancel();
            }
        }

        if (Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($player->getPosition())['pvp']) {
            if ($player->getGamemode()->equals(GameMode::ADVENTURE())) {
                $player->setGamemode(GameMode::SURVIVAL());
            }
        } else {
            if ($player->getGamemode()->equals(GameMode::SURVIVAL())) {
                $player->setGamemode(GameMode::ADVENTURE());

            }
        }



        if ($event->getTo()->distance($event->getFrom()) >= 0.1) {
            if ($player->getWorld()->getFolderName() === 'crystal_maudit') {
                $block = $player->getWorld()->getBlock($event->getTo());
                if ($block instanceof Lava) {
                    $player->teleport(new Position(255, 56, 157, Server::getInstance()->getWorldManager()->getWorldByName("crystal_maudit")));
                    $pk = StopSoundPacket::create("music.peste.jump", true);
                    $player->getNetworkSession()->sendDataPacket($pk);
                    $pk = StopSoundPacket::create("music.peste.scary", true);
                    $player->getNetworkSession()->sendDataPacket($pk);

                    Utils::timeout(function () use ($player) : void {
                        if (!$player->isConnected() || !$player->isAlive()) return;
                        $pk = new PlaySoundPacket();
                        $pk->soundName = "music.peste.scary";
                        $pk->pitch = 1;
                        $pk->x = $player->getPosition()->getX();
                        $pk->y = $player->getPosition()->getY();
                        $pk->z = $player->getPosition()->getZ();
                        $pk->volume = 50;
                        $player->getNetworkSession()->sendDataPacket($pk);
                    }, 20);
                }
            }



            if (Main::getInstance()->getPortalManager()->isInPortal($player->getPosition())) {
                $block = $player->getWorld()->getBlock($player->getPosition());
                if ($player->hasTagged()) return;
                if ($block instanceof NetherPortal || $block->getTypeId() === BlockTypeIds::WATER) {
                    $tp = Main::getInstance()->getPortalManager()->getTp($player->getPosition());
                    $name = Main::getInstance()->getPortalManager()->getNamePortal($player->getPosition());


                    if ($name === 'minage') {
                        $player->teleport(new Position(7, 137, 103, $player->getWorld()));
                        $player->transfer('goldrushmc.fun', '19133');
                        return;
                    }

                    if ($name === 'vers_crystal_maudit') {
                        if ($player->getEffects()->has(VanillaEffects::NIGHT_VISION())) {
                            $player->getEffects()->remove(VanillaEffects::NIGHT_VISION());
                        }
                        $player->sendMessage("§4[§cSylvanar§4] §cBienvenue dans les enfers de GoldRush...");

                        if (!isset(Peste::$isInBoss[array_search($player, Peste::$isInBoss)])) {
                            Peste::$isInBoss[] = $player;
                        }

                        $tpStr = $tp;
                        $tpExplode = explode(':', $tpStr);
                        $world = Server::getInstance()->getWorldManager()->getWorldByName($tpExplode[3]);
                        if (!$world->isLoaded()) Server::getInstance()->getWorldManager()->loadWorld($world->getFolderName());
                        $player->teleport(new Position((int)$tpExplode[0], (int)$tpExplode[1], (int)$tpExplode[2], $world));

                        Utils::timeout(function () use ($player) : void {
                            if (!$player->isConnected() || !$player->isAlive()) return;
                            $pk = new PlaySoundPacket();
                            $pk->soundName = "music.peste.scary";
                            $pk->pitch = 1;
                            $pk->x = $player->getPosition()->getX();
                            $pk->y = $player->getPosition()->getY();
                            $pk->z = $player->getPosition()->getZ();
                            $pk->volume = 50;
                            $player->getNetworkSession()->sendDataPacket($pk);
                        }, 20);
                        return;
                    }

                    if ($name === "vers_boss_jump") {
                        Cinematics::sendCinematicBossJumpSylvanar($player);
                        $player->teleport(new Position(103, 44, 235, Server::getInstance()->getWorldManager()->getWorldByName("crystal_maudit")));
                        return;
                    }

                    if ($tp !== '404') {
                        $tpStr = $tp;
                        $tpExplode = explode(':', $tpStr);
                        $world = Server::getInstance()->getWorldManager()->getWorldByName($tpExplode[3]);
                        if (!$world->isLoaded()) Server::getInstance()->getWorldManager()->loadWorld($world->getFolderName());
                        $player->teleport(new Position((int)$tpExplode[0], (int)$tpExplode[1], (int)$tpExplode[2], $world));
                    }
                }
            }
        }
    }

    public function onFall(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();
        if ($entity->getWorld()->getFolderName() === 'minage-map') {
            if ($entity instanceof Player) $event->cancel();
            return;
        }
        if (Main::getInstance()->getAreaManager()->isInArea($entity->getPosition())) {

            if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                $event->cancel();
            }


            if (Main::getInstance()->getAreaManager()->getAreaNamePriority($entity->getPosition()) === "vengeur_ame") {
                $event->cancel();
            }

            if (!Main::getInstance()->getAreaManager()->getFlagsByName(Main::getInstance()->getAreaManager()->getAreaNamePriority($entity->getPosition()))['pvp']) {
                if ($entity instanceof CustomPlayer) {
                    $handItem = $entity->getInventory()->getItemInHand();
                    if ($handItem instanceof Durable) {
                        $handItem->setDamage($handItem->getDamage());
                        $entity->getInventory()->setItemInHand($handItem);
                    }
                    $event->cancel();
                }
            }
        }
    }


    /**"
     * @priority HIGHEST
     * @param BlockPlaceEvent $event
     */
    public function onPlace(BlockPlaceEvent $event): void
    {
        $block = $event->getBlockAgainst();
        $player = $event->getPlayer();
        $api = Main::getInstance()->getAreaManager();

        if ($api->isInArea($block->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($block->getPosition());
            if (!$flags['place']) {
                if (Server::getInstance()->isOp($player->getName()) || $player->hasPermission('area.event')) return;
                $event->cancel();
            }
        }
    }


    public function onInteract2(PlayerInteractEvent $event): void
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $api = Main::getInstance()->getAreaManager();


        $antibuginv = [
            BackpackFarm::class,
            BackpackFossil::class,
            BackpackOre::class,
            VoidStone::class,
            BucketCopper::class,
            BucketCopperLava::class,
            BucketEmptyCopper::class,
            BucketGold::class,
            BucketEmptyGold::class,
            BucketGoldLava::class,
            BucketPlatinum::class,
            BucketPlatinumLava::class,
            BucketEmptyPlatinum::class
        ];


        if($block instanceof Chest && in_array($player->getInventory()->getItemInHand()::class, $antibuginv)) {
            $player->sendMessage("§cVous ne pouvez pas ouvrir de coffre avec cette item en main !");
            $event->cancel();
        }

        if ($api->isInArea($block->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($block->getPosition());
            if (!$flags['place']) {
                if (Server::getInstance()->isOp($player->getName()) || $player->hasPermission('area.event')) return;
                if ($block instanceof Water) {
                    $event->cancel();
                }

                if (in_array($player->getInventory()->getItemInHand()->getTypeId(), [ItemTypeIds::FLINT_AND_STEEL, ItemTypeIds::BUCKET, ItemTypeIds::LAVA_BUCKET, ItemTypeIds::WATER_BUCKET, ItemTypeIds::ENDER_PEARL])) {
                    $event->cancel();
                }

                if (!in_array($block->getTypeId(), [
                    VanillaBlocks::ENDER_CHEST()->getTypeId()
                ])) {
                    $event->cancel();
                }
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param EntityExplodeEvent $event
     */
    public function onEntityExplode(EntityExplodeEvent $event): void
    {
        $api = Main::getInstance()->getAreaManager();
        if ($api->isInArea($event->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($event->getPosition());
            if (!$flags['tnt']) $event->cancel();
        }
    }

    public function onLaunch(ProjectileLaunchEvent $event): void
    {
        $entity = $event->getEntity();

        if (Main::getInstance()->getAreaManager()->isInArea($entity->getPosition())) {
            if (!Main::getInstance()->getAreaManager()->getFlagsAreaByPosition($entity->getPosition())['pvp']) {
                $event->cancel();
            }
        }
    }


    /**
     * @priority HIGHEST
     * @param PlayerExhaustEvent $event
     */
    public function onHunger(PlayerExhaustEvent $event): void
    {
        $player = $event->getPlayer();
        if (!$player instanceof Player) return;
        $api = Main::getInstance()->getAreaManager();


        if ($api->isInArea($player->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($player->getPosition());
            if (!$flags['hunger']) {
                $event->cancel();
            }
        }
    }


    /**
     * @priority HIGHEST
     * @param PlayerDropItemEvent $event
     */
    public function onDrop(PlayerDropItemEvent $event): void
    {
        $player = $event->getPlayer();
        $api = Main::getInstance()->getAreaManager();

        if ($api->isInArea($player->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($player->getPosition());
            if (!$flags['dropItem']) {
                if (Server::getInstance()->isOp($player->getName()) || $player->hasPermission('area.event')) return;
                $event->cancel();
            }
        }
    }


    /**
     * @priority HIGHEST
     * @param PlayerItemConsumeEvent $event
     */
    public function onConsume(PlayerItemConsumeEvent $event): void
    {
        $player = $event->getPlayer();
        $api = Main::getInstance()->getAreaManager();

        if ($api->isInArea($player->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($player->getPosition());
            if (!$flags['consume']) {
                if (Server::getInstance()->isOp($player->getName()) || $player->hasPermission('area.event')) return;
                $event->cancel();
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param PlayerInteractEvent $event
     */
    public function onInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $api = Main::getInstance()->getAreaManager();

        if ($api->isInArea($player->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($player->getPosition());
            if (!$flags['place']) {
                if (Server::getInstance()->isOp($player->getName()) || $player->hasPermission('area.event')) return;
                if ($event->getBlock()->getTypeId() === BlockTypeIds::ITEM_FRAME || $event->getBlock()->getTypeId() === BlockTypeIds::ITEM_FRAME) {
                    $event->cancel();
                }
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param PlayerChatEvent $event
     */
    public function onChatSend(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $api = Main::getInstance()->getAreaManager();

        if ($api->isInArea($player->getPosition())) {
            $flags = $api->getFlagsAreaByPosition($player->getPosition());
            if (!$flags['chat']) {
                if (Server::getInstance()->isOp($player->getName()) || $player->hasPermission('area.event')) return;
                $event->cancel();
            }
        }
    }

    /**
     * @priority HIGHEST
     * @param EntityDamageByEntityEvent $event
     */
    public function onDamage(EntityDamageByEntityEvent $event): void
    {
        $victim = $event->getEntity();
        $damager = $event->getDamager();
        if ($victim->getWorld()->getFolderName() === 'minage-map') {
            $event->cancel();
            return;
        }
        if ($victim instanceof Player && $damager instanceof Player) {
            $api = Main::getInstance()->getAreaManager();
            if ($api->isInArea($victim->getPosition())) {
                $flags = $api->getFlagsAreaByPosition($victim->getPosition());
                if (!$flags['pvp']) {
                    if ($api->getAreaNamePriority($victim->getPosition()) !== 'koth') {
                        $event->cancel();
                    }
                }
            }
        }
    }
}