<?php

namespace core\listeners\types\staff;

use core\api\gui\ChestInventory;
use core\commands\executors\staff\Vanish;
use core\listeners\BaseEvent;
use core\Main;
use pocketmine\block\Chest;
use pocketmine\block\inventory\DoubleChestInventory;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityCombustEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\event\world\WorldSoundEvent;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class VanishListeners extends BaseEvent
{
    private static array $silentBlocks = [];


    public function pickUp(EntityItemPickupEvent $event) {
        if ($event->getEntity() instanceof Player) {
            if (in_array($event->getEntity()->getXuid(), Vanish::$inVanish)) {
                $event->cancel();
            }
        }
    }



    public function onDamage(EntityDamageEvent $event) {
        $player = $event->getEntity();
        if($player instanceof Player) {
            $name = $player->getXuid();
            if(in_array($name, Vanish::$inVanish)) {
                $event->cancel();
            }
        }
    }

    public function onPlayerBurn(EntityCombustEvent $event) {
        $player = $event->getEntity();
        if($player instanceof Player) {
            $name = $player->getXuid();
            if(in_array($name, Vanish::$inVanish)) {
                $event->cancel();
            }
        }
    }


    /**
     * @param PlayerJoinEvent $event
     * @priority HIGHEST
     */
    public function setNametag(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        if (in_array($player->getXuid(), Vanish::$inVanish)){
            $player->setNameTag(TextFormat::GOLD . "[V] " . TextFormat::RESET . $player->getNameTag());
        }
    }


    public static array $cooldown = [];

    public function onInteractVanish(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $itemInHand = $event->getItem();

        if (Main::getInstance()->getStaffManager()->isInStaffMode($player)) {
            if ($itemInHand->getCustomName() === "vanish - §cOFF") {
                if (isset(self::$cooldown[$player->getXuid()])) {
                    if (self::$cooldown[$player->getXuid()] <= time()) {
                        $player->getInventory()->setItemInHand(VanillaItems::BLAZE_POWDER()->setCustomName("vanish - §aON"));
                        $player->sendMessage("§c[§fSTAFF§c] §fVanish §aon");
                        Server::getInstance()->broadcastMessage("§7[§c-§7] " . $player->getName());
                        Vanish::$inVanish[] = $player->getXuid();
                        self::$cooldown[$player->getXuid()] = time() + 1;
                    } else return;
                } else {
                    $player->getInventory()->setItemInHand(VanillaItems::BLAZE_POWDER()->setCustomName("vanish - §aON"));
                    $player->sendMessage("§c[§fSTAFF§c] §fVanish §aon");
                    Server::getInstance()->broadcastMessage("§7[§c-§7] " . $player->getName());
                    Vanish::$inVanish[] = $player->getXuid();
                    self::$cooldown[$player->getXuid()] = time() + 1;
                }
            } elseif ($itemInHand->getCustomName() === "vanish - §aON") {
                if (isset(self::$cooldown[$player->getXuid()])) {
                    if (self::$cooldown[$player->getXuid()] <= time()) {
                        foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                            $onlinePlayer->showPlayer($player);
                        }

                        foreach(Server::getInstance()->getOnlinePlayers() as $p) {
                            $networkSession = $p->getNetworkSession();
                            $networkSession->sendDataPacket(
                                PlayerListPacket::add([
                                    PlayerListEntry::createAdditionEntry(
                                        $player->getUniqueId(),
                                        $player->getId(),
                                        $player->getDisplayName(),
                                        $networkSession->getTypeConverter()->getSkinAdapter()->toSkinData($player->getSkin()),
                                        $player->getXuid()
                                    )]));
                        }

                        Server::getInstance()->broadcastMessage("§7[§a+§7] " . $player->getName());
                        $player->setSilent(false);
                        $player->getXpManager()->setCanAttractXpOrbs(true);
                        $player->sendMessage("§c[§fSTAFF§c] §fVanish §coff");
                        $player->getInventory()->setItemInHand(VanillaItems::GUNPOWDER()->setCustomName("vanish - §cOFF"));
                        unset(Vanish::$inVanish[array_search($player->getXuid(), Vanish::$inVanish)]);
                        self::$cooldown[$player->getXuid()] = time() + 1;
                    } else return;
                } else {
                    foreach (Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                        $onlinePlayer->showPlayer($player);
                    }

                    foreach(Server::getInstance()->getOnlinePlayers() as $p) {
                        $networkSession = $p->getNetworkSession();
                        $networkSession->sendDataPacket(
                            PlayerListPacket::add([
                                PlayerListEntry::createAdditionEntry(
                                    $player->getUniqueId(),
                                    $player->getId(),
                                    $player->getDisplayName(),
                                    $networkSession->getTypeConverter()->getSkinAdapter()->toSkinData($player->getSkin()),
                                    $player->getXuid()
                                )]));
                    }

                    Server::getInstance()->broadcastMessage("§7[§a+§7] " . $player->getName());
                    $player->setSilent(false);
                    $player->getXpManager()->setCanAttractXpOrbs(true);
                    $player->sendMessage("§c[§fSTAFF§c] §fVanish §coff");
                    $player->getInventory()->setItemInHand(VanillaItems::GUNPOWDER()->setCustomName("vanish - §cOFF"));
                    unset(Vanish::$inVanish[array_search($player->getXuid(), Vanish::$inVanish)]);
                    self::$cooldown[$player->getXuid()] = time() + 1;
                }
            }
        }
    }

    public function onQuery(QueryRegenerateEvent $event) {


        $list =  [];
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (!in_array($player->getXuid(), Vanish::$inVanish)) {
                $list[] = $player->getName();
            }
        }

        $event->getQueryInfo()->setPlayerList($list);



        foreach(Server::getInstance()->getOnlinePlayers() as $p) {
            if(in_array($p->getXuid(), Vanish::$inVanish)) {
                $online = $event->getQueryInfo()->getPlayerCount();
                $event->getQueryInfo()->setPlayerCount($online - 1);
            }
        }
    }

    public function onInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        /** @var \pocketmine\block\tile\Chest $tile */
        $tile =  $block->getPosition()->getWorld()->getTile($block->getPosition());
        $action = $event->getAction();

        if (in_array($player->getXuid(), Vanish::$inVanish)) {
            if($block instanceof Chest) {
                if($action === $event::RIGHT_CLICK_BLOCK) {
                    if(!$player->isSneaking()) {
                        $event->cancel();
                        $inv = $tile->getInventory();
                        $content = $inv->getContents();
                        if($content !== null) {
                            $player->setCurrentWindow($inv);
                        }else{
                            $player->sendMessage("§c[§fSTAFF§c] §fLe coffre est vide.");
                        }
                    }
                }else{
                    $event->cancel();
                }
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     * @priority HIGHEST
     */
    public function silentJoin(PlayerJoinEvent $event) {
        if (in_array($event->getPlayer()->getXuid(), Vanish::$inVanish)){
            $event->setJoinMessage("");
        }
    }

    /**
     * @param PlayerQuitEvent $event
     * @priority HIGHEST
     */
    public function silentLeave(PlayerQuitEvent $event) {
        if (in_array($event->getPlayer()->getXuid(), Vanish::$inVanish)){
            $event->setQuitMessage("");
        }
    }


    /**
     * @param PlayerInteractEvent $event
     * @priority LOWEST
     */
    public function onBlockInteract(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        if(in_array($player->getXuid(), Vanish::$inVanish) && $event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK){
            $position = $event->getBlock()->getPosition();
            $delay = round($event->getBlock()->getBreakInfo()->getBreakTime($player->getInventory()->getItemInHand())) * 20;
            self::$silentBlocks[] = $position;
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($position): void{
                unset(self::$silentBlocks[array_search($position, self::$silentBlocks)]);
            }), $delay);
        }
    }

    /**
     * @param WorldSoundEvent $event
     * @priority HIGHEST
     */
    public function onWorldSoundBroadcast(WorldSoundEvent $event){
        foreach (self::$silentBlocks as $silentBlock){
            if ($event->getPosition()->equals($silentBlock)){
                $event->cancel();
            }
        }
    }

    /**
     * @param BlockBreakEvent $event
     * @priority HIGHEST
     */
    public function onBlockBreak(BlockBreakEvent $event){
        if($event->isCancelled()){
            return;
        }
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if (in_array($player->getXuid(), Vanish::$inVanish)){
            $event->cancel();
            $player->getWorld()->setBlock($block->getPosition(), VanillaBlocks::AIR());
            if($player->isSurvival(true)){
                $drops = $event->getDrops();
                $xpDrop = $event->getXpDropAmount();
                $player->getXpManager()->addXp($xpDrop);
                foreach ($drops as $drop){
                    if($player->getInventory()->canAddItem($drop)){
                        $player->getInventory()->addItem($drop);
                    }else{
                        $player->getWorld()->dropItem($event->getBlock()->getPosition()->add(0.5, 0.5, 0.5), $drop);
                    }
                }
                $item = $player->getInventory()->getItemInHand();
                $returnedItems = [];
                $item->onDestroyBlock($block, $returnedItems);
                $player->getInventory()->setItemInHand($item);
            }
        }
    }

    /**
     * @param BlockPlaceEvent $event
     * @priority HIGHEST
     */
    public function onBlockPlace(BlockPlaceEvent $event){
        if($event->isCancelled()){
            return;
        }
        $player = $event->getPlayer();
        if(in_array($player->getXuid(), Vanish::$inVanish)){
            $event->cancel();
            $event->getTransaction()->apply();
            if($player->isSurvival(true)){
                $player->getInventory()->removeItem($event->getItem()->setCount(1));
            }
        }
    }
}