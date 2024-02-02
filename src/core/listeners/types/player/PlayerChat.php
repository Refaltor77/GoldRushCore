<?php

namespace core\listeners\types\player;

use core\events\LogEvent;
use core\formatters\BasicChatFormatter;
use core\listeners\BaseEvent;
use core\Main;
use core\managers\stats\StatsManager;
use core\messages\Prefix;
use core\player\CustomPlayer;
use core\traits\FactionTrait;
use core\utils\Message;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Snowball;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class PlayerChat extends BaseEvent
{

    use FactionTrait;
    public array $msgCache = [];
    public array $lastMessage = [];


    public function onEntityTouch(EntityDamageByEntityEvent $event): void {
        $damager = $event->getDamager();
        $player = $event->getEntity();
        if ($player instanceof CustomPlayer) {
            if ($damager instanceof \pocketmine\entity\projectile\Snowball) {
                $event->setBaseDamage(0.0);
                $owned = $damager->getOwningEntity();
                if ($owned instanceof CustomPlayer) {
                    $owned->setTagged(true);
                    $player->setTagged(true);
                }
            }
        }
    }

    public function onChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $player->checkupBug();


        $rank = $this->getPlugin()->getRankManager()->getSupremeRankPriorityChat($player->getXuid());
        $format = $this->getPlugin()->getRankManager()->getFormatChat($rank);
        $faction = Main::getInstance()->getFactionManager()->getFactionName($player->getXuid());

        if ($this->getPlugin()->getSanctionManager()->hasMute($player->getXuid())) {
            $player->sendMessage(Prefix::PREFIX_GOOD . "§cVous êtes mute !");
            $event->cancel();
            return;
        }

        $blackList = Main::getInstance()->getBlacklistManager()->getBlacklist();
        foreach (explode(" ", strtolower($message)) as $word) {
            if (in_array($word, $blackList)) {
                $message = str_replace($word, $this->transformWord($word), $message);
            }
        }
        $event->setMessage($message);
        $message = $event->getMessage();

        if (str_contains($message, "§k")) {
            $message = str_replace("§k", "", $message);
        }

        if ($this->isFactionChat($player->getXuid())) {
            $event->cancel();
            $factionMembers = Main::getInstance()->getFactionManager()->getMembersFaction($faction);
            foreach ($factionMembers as $xuid => $rank) {
                $factionMember = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                if ($factionMember instanceof CustomPlayer && $factionMember->isOnline()) {
                    $factionMember->sendMessage("§7[§cFaction§7] §r" . $player->getName() . " §7» §r" . $message);
                }
            }
            return;
        }

        if (!$player->hasPermission('chat.delay.use') && !Server::getInstance()->isOp($player->getName())) {

            if (str_contains($message, "twitch.tv") || str_contains($message, "youtube.com") || str_contains($message, "youtu.be")) {
                $player->sendMessage(Prefix::PREFIX_ERROR . "§cLa publicité est interdite sur le serveur!");
                $event->cancel();
                return;
            }

            if (isset($this->msgCache[$player->getXuid()])) {
                $time = $this->msgCache[$player->getXuid()];
                if ($time > time()) {
                    if (!$this->getPlugin()->getServer()->isOp($player->getName())) {
                        $player->sendMessage(Prefix::PREFIX_GOOD . "§cDoucement !");
                        $event->cancel();
                    }
                } else {
                    $this->msgCache[$player->getXuid()] = time() + 3;
                }
            } else $this->msgCache[$player->getXuid()] = time() + 3;

            if (isset($this->lastMessage[$player->getXuid()])) {
                $lastMessage = $this->lastMessage[$player->getXuid()];
                if (strtolower($message) === $lastMessage) {
                    if (!$this->getPlugin()->getServer()->isOp($player->getName())) {
                        $player->sendMessage(Prefix::PREFIX_GOOD . "§cVous ne pouvez pas envoyer le même message deux fois !");
                        $event->cancel();
                    }
                } else $this->lastMessage[$player->getXuid()] = strtolower($message);
            } else $this->lastMessage[$player->getXuid()] = strtolower($message);
        }

        if (!$event->isCancelled()) {
            $this->getPlugin()->getStatsManager()->addValue($player->getXuid(), StatsManager::MSG);
        }
        $event->setFormatter(new BasicChatFormatter($faction, $rank, $format));
        $msg = str_replace(['{faction}', '{pseudo}', '{msg}'], [$faction, $player->getName(), TextFormat::clean($message)], $format);
        (new LogEvent($msg, LogEvent::MESSAGE_TYPE))->call();



        Main::getInstance()->getGrafanaManager()->addMessageQueue($player->getXuid(), $message);
        Main::getInstance()->threadManager->messagerThread->messages[] = $msg;
    }

    private function transformWord(string $word): string
    {
        for ($i = 0; $i < strlen($word); $i++) {
            $word[$i] = "*";
        }
        return $word;
    }
}