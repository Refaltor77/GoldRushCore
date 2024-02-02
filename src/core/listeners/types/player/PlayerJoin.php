<?php

namespace core\listeners\types\player;

use core\api\camera\CameraSystem;
use core\api\camera\EaseTypes;
use core\api\timings\TimingsSystem;
use core\api\webhook\Embed;
use core\api\webhook\Message;
use core\api\webhook\Webhook;
use core\async\RequestAsync;
use core\commands\CommandTrait;
use core\events\BossBarReloadEvent;
use core\events\LogEvent;
use core\events\ScoreboardReloadEvent;
use core\listeners\BaseEvent;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use core\sql\SQL;
use core\traits\SoundTrait;
use core\utils\Utils;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\item\Armor;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackInfoEntry;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackStackEntry;
use pocketmine\network\query\QueryInfo;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\Internet;
use pocketmine\world\Position;

class PlayerJoin extends BaseEvent
{
    use SoundTrait;
    use CommandTrait;

    public function onCreate(PlayerCreationEvent $event): void
    {
        $event->setPlayerClass(CustomPlayer::class);
    }

    public function onQueryREegenerate(QueryRegenerateEvent $event): void {
        $queryu = Internet::getURL("https://api.mcsrvstat.us/bedrock/3/goldrushmc.fun:19133");
        if (is_null($queryu)) return;
        $body = $queryu->getBody();
        $json = json_decode($body, true);
        $online = 10;
        if (isset($json['online'])) {
            if ($json['online']) $online = $json['players']['online'];
        }
        $countTotal = $online + count(Server::getInstance()->getOnlinePlayers());
        $event->getQueryInfo()->setPlayerCount($countTotal);
    }



    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $event->setJoinMessage("");

        if ($player instanceof CustomPlayer) {
            $player->setNoClientPredictions(true);
        }

        if (Main::getInstance()->getUserManager()->ipIsTaken($player->getName(), $player->getNetworkSession()->getIp()) && !in_array($player->getNetworkSession()->getIp(), ["::1", "127.0.0.1", "localhost"])) {
            if(!Main::getInstance()->getDcManager()->isDc($player->getXuid())) {

                $webhook = new Webhook("https://discord.com/api/webhooks/1167600370726469743/1on12dK1-5xIAWfcdEzEoHA9OuyNUcQIF42ZYuFQ_gixR-GeNioyVW6JsIzQzKYiu7F1");

                $embed = new Embed();
                $embed->setTitle("IP déjà utilisé");
                $embed->setDescription("Un joueur a rejoint le serveur avec une IP déjà utilisé");
                $users = $player->getName();
                foreach (Main::getInstance()->getUserManager()->getSameIp($player->getName(), $player->getNetworkSession()->getIp()) as $user) {
                    $users .= " " . $user['pseudo'];
                }
                $embed->addField("Joueurs", $users, true);
                $embed->setColor(0x00FF00);
                $msg = new Message();
                $msg->addEmbed($embed);
                $webhook->send($msg);

                $player->kick("Double compte détecté ! viens discord: discord.gg/goldrush", false);
            }
        }

        if (!$player instanceof CustomPlayer) return;
        $event->setJoinMessage("");

        $skinManager = Main::getInstance()->getSkinManager();
        if (!$skinManager->skinIsSave($player)) {
            $skinManager->saveSkin($player);
        }


        $this->getPlugin()->getSanctionManager()->checkBanService($player, function () use ($event): void {

            $player = $event->getPlayer();
            if (!$player instanceof CustomPlayer) return;
            if (!$player->isConnected()) return;

            if ($player->getGamemode()->equals(GameMode::SPECTATOR())) {
                $player->setGamemode(GameMode::SURVIVAL());
            }

            Utils::timeout(function () use ($player): void {
                if (!$player->isConnected()) return;

                Main::getInstance()->getConnexionManager()->checkOtherServerConnected($player, function (Player $player): void {
                    Main::getInstance()->checkVPN($player->getName(), $player->getNetworkSession()->getIp());
                });
            }, 20 * 5);



            Server::getInstance()->broadcastMessage("§7[§a+§7] §7" . $player->getName());

            $player->hasReallyConnected = true;

            Utils::timeout(function () use ($player): void {
                (new ScoreboardReloadEvent($player))->call();
            }, 40);


            $event->getPlayer()->addAttachment(Main::getInstance(), "base.permission.user", true);
            $player = $event->getPlayer();

            Utils::timeout(function () use ($player): void {
                if ($player->isConnected()) {
                    $pk = $this->createPacket($player);
                    $player->getNetworkSession()->sendDataPacket($pk);
                }

            }, 20);

            # stats suivie
            $this->getPlugin()->getGrafanaManager()->addConnexionEntry($player->getXuid());

            # data load



            TimingsSystem::schedule(function (TimingsSystem $timingsSystem, int $second) use ($player) : void {
                if (!$player->isConnected()) {
                    $timingsSystem->stopTiming();
                    return;
                }
                if ($second === 60) {
                    $this->getPlugin()->getTebexManager()->checkTebexPurshace($player);
                }
            }, 20);

            $this->getPlugin()->getTopLuckManager()->enableSession($player);
            $this->getPlugin()->getDataManager()->loadDataUser($player);
            $this->getPlugin()->getRankManager()->loadData($player);
            $this->getPlugin()->getHomeManager()->loadData($player);

            $this->getPlugin()->getInventoryManager()->checkingDatabase($player, function (Player $player): void {
                if ($player instanceof CustomPlayer) {
                    $player->setNoClientPredictions(false);
                }
                $player->checkupBug();
            });


            $this->getPlugin()->getEconomyManager()->loadData($player);
            $this->getPlugin()->getStatsManager()->loadData($player);
            $this->getPlugin()->getJobsManager()->loadData($player);
            $this->getPlugin()->getCraftManager()->loadPlayer($player);
            $this->getPlugin()->getEnderChestManager()->loadUser($player);
            $this->getPlugin()->getKitManager()->checkupUser($player);
            $this->getPlugin()->jobsStorage->loadUserCache($player);
            $this->getPlugin()->getSettingsManager()->loadData($player);
            $this->getPlugin()->questStorage->loadUserCache($player);
            $this->getPlugin()->getScoreboardManager()->loadData($player);
            $this->getPlugin()->getSkinManager2()->loadData($player);
            $this->getPlugin()->getPrimeManager()->loadData($player);
            $this->getPlugin()->getXpManager()->loadData($player);
            $this->getPlugin()->getBlockBreakManager()->loadData($player, function (Player $player): void {
                if (Main::XUID_SERVER === "XUID-GOLDRUSH-SERVER-GAME1") {
                    Main::getInstance()->getBlockBreakManager()->checkupFactionQuest($player);
                }
            });


            Utils::timeout(function () use ($player): void {
                if ($player->isConnected()) {
                    $settings = Main::getInstance()->getSettingsManager()->getSetting($player, "coordinates");
                    if ($settings) {
                        $pk = new GameRulesChangedPacket();
                        $pk->gameRules = [
                            "showcoordinates" => new BoolGameRule(true, false)
                        ];
                        $player->getNetworkSession()->sendDataPacket($pk);
                    }
                }
            }, 40);


            if (!$player->hasPlayedBefore()) {

                $this->sendCinematicFirstConnexion($player);

                # KITS PLAYER
                $content = [
                    VanillaItems::IRON_HELMET()->setCustomName("§l§eKit joueur"),
                    VanillaItems::IRON_CHESTPLATE()->setCustomName("§l§eKit joueur"),
                    VanillaItems::IRON_LEGGINGS()->setCustomName("§l§eKit joueur"),
                    VanillaItems::IRON_BOOTS()->setCustomName("§l§eKit joueur"),
                    VanillaItems::DIAMOND_PICKAXE()->setCustomName("§l§eKit joueur"),
                    VanillaItems::DIAMOND_PICKAXE()->setCustomName("§l§eKit joueur"),
                    VanillaItems::STEAK()->setCount(64)->setCustomName("§l§eKit joueur"),
                    VanillaBlocks::TORCH()->asItem()->setCount(64)->setCustomName("§l§eKit joueur"),
                    CustomiesItemFactory::getInstance()->get(Ids::RTP)
                ];

                foreach ($content as $item) {
                    if ($item instanceof Armor) {
                        $slot = $item->getArmorSlot();
                        $player->getArmorInventory()->setItem($slot, $item);
                    } else {
                        $player->getInventory()->addItem($item);
                    }
                }


                $player->sendTitle("§6- §eBienvenue sur GoldRush §6-", "https://goldrushmc.fun/discord");
                $this->sendSuccessSound($player);
            }
            (new LogEvent($player->getName() . " a rejoint le serveur", LogEvent::JOIN_TYPE))->call();
        });
    }


    public function sendCinematicFirstConnexion(Player $player): void
    {
        $player->isInCinematic = true;
        $camera = new CameraSystem($player);


        BossBarReloadEvent::$notSendTimePlayer[$player->getXuid()] = time() + 60;
        $player->getServer()->unsubscribeFromBroadcastChannel(Server::BROADCAST_CHANNEL_USERS, $player);

        $camera->createTiming(function (CameraSystem $camera, int $seconds, Player $player): void {
            switch ($seconds) {
                case 0:
                    $player->sendTitle("cinematic", "", 0, 100, 0);
                    $player->setInvisible(true);
                    $player->setGamemode(GameMode::SPECTATOR());
                    $camera->sendFade(1, 7, 1, DyeColor::BLACK());
                    break;
                case 1:
                    $pk = PlaySoundPacket::create(
                        "music.cinematic.goldrush",
                        $player->getPosition()->getX(),
                        $player->getPosition()->getY(),
                        $player->getPosition()->getZ(),
                        70,
                        1
                    );
                    $player->getNetworkSession()->sendDataPacket($pk);
                    break;
                case 2:
                    $player->teleport(new Position(61, 117, -121, $player->getWorld()));
                    break;
                case 4:
                    $player->teleport(new Position(-105, 116, 3, $player->getWorld()));
                    break;
                case 5:
                    $camera->setCameraPosition(new Vector3(9, 189, 85), EaseTypes::LINEAR, 0, new Vector3(9, 189, 87));
                    break;
                case 6:
                    $player->teleport(new Position(7, 137, 90, $player->getWorld()));
                    $camera->setCameraPosition(new Vector3(9, 189, 54), EaseTypes::LINEAR, 9, new Vector3(9, 189, 87));
                    break;
                case 13:
                    $camera->sendFade(2, 1, 1, DyeColor::BLACK());
                    break;
                case 15:
                    $camera->setCameraPosition(new Vector3(-90, 104, 125), EaseTypes::LINEAR, 0, new Vector3(10, 104, 125));
                    break;
                case 16:
                    $camera->setCameraPosition(new Vector3(8, 104, 125), EaseTypes::LINEAR, 7, new Vector3(10, 104, 125));
                    break;
                case 20:
                    $camera->sendFade(2, 3, 1, DyeColor::BLACK());
                    break;
                case 22:
                    $camera->setCameraPosition(new Vector3(-44, 24, 74), EaseTypes::LINEAR, 0, new Vector3(-44, 23, 74));
                    break;
                case 25:
                    $camera->setCameraPosition(new Vector3(-44, 77, 74), EaseTypes::LINEAR, 6, new Vector3(-44, 23, 74));
                    break;
                case 27:
                    $camera->sendFade(2, 4, 1, DyeColor::BLACK());
                    break;
                case 29:
                    $player->teleport(new Position(-16, 109, -101, $player->getWorld()));
                    $camera->setCameraPosition(new Vector3(6, 86, -11), EaseTypes::LINEAR, 0, new Vector3(7, 83, -11));
                    break;
                case 33:
                    $camera->setCameraPosition(new Vector3(6, 250, -11), EaseTypes::LINEAR, 6, new Vector3(6, 52, -11));
                    break;
                case 37:
                    $camera->sendFade(1, 1, 1, DyeColor::BLACK());
                    break;
                case 38:
                    $camera->setCameraPosition(new Vector3(5, 196, 132), EaseTypes::LINEAR, 0, new Vector3(5, 196, 134));
                    break;
                case 39:
                    $camera->setCameraPosition(new Vector3(-89, 98, -44), EaseTypes::LINEAR, 6, new Vector3(5, 196, 134));
                    break;
                case 43:
                    $camera->sendFade(1, 2, 1, DyeColor::BLACK());
                    break;
                case 44:
                    $camera->setCameraPosition(new Vector3(6, 90, -119), EaseTypes::LINEAR, 0, new Vector3(6, 90, -116));
                    break;
                case 46:
                    $camera->setCameraPosition(new Vector3(6, 114, -179), EaseTypes::LINEAR, 5, new Vector3(6, 90, -116));
                    break;
                case 49:
                    $camera->sendFade(1, 2, 1, DyeColor::BLACK());
                    break;
                case 50:
                    $camera->setCameraPosition(new Vector3(63, 88, -207), EaseTypes::LINEAR, 0, new Vector3(63, 80, -207));
                    break;
                case 52:
                    $camera->setCameraPosition(new Vector3(95, 147, -164), EaseTypes::LINEAR, 5, new Vector3(63, 80, -207));
                    break;
                case 55:
                    $camera->sendFade(1, 1, 1, DyeColor::BLACK());
                    break;
                case 56:
                    $player->teleport(new Position(7, 137, 90, $player->getWorld()));
                    $camera->setCameraPosition(new Vector3(6, 103, -107), EaseTypes::LINEAR, 0, new Vector3(7, 203, 132));
                    break;
                case 57:
                    $camera->setCameraPosition(new Vector3(7, 203, 132), EaseTypes::LINEAR, 7, new Vector3(7, 203, 132));
                    break;
                case 63:
                    $camera->sendFade(1, 5, 1, DyeColor::BLACK());
                    break;
                case 64:
                    $player->sendTitle("§fBienvenue sur Gold§6Rush§f !", "https://goldrushmc.fun", 20, 20 * 5, 20);
                    break;
                case 68:
                    $player->setInvisible(false);
                    $player->sendMessage(Messages::message("§fBienvenue sur Gold§6Rush §f!"));


                    $player->teleport(new Position(
                        259, 55, 319, Server::getInstance()->getWorldManager()->getWorldByName("demo")
                    ));


                    $player->isInCinematic = false;
                    $player->getServer()->subscribeToBroadcastChannel(Server::BROADCAST_CHANNEL_USERS, $player);
                    $player->sendTitle("break", 1, 1, -1);
                    $player->setGamemode(GameMode::SURVIVAL());
                    $camera->stopTiming();
                    break;
            }

        });
    }
}