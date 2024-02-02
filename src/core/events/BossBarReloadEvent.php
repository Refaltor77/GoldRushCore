<?php

namespace core\events;

use core\entities\BossSouls;
use core\entities\Nexus;
use core\entities\Peste;
use core\entities\TrollBoss;
use core\listeners\types\events\KothListeners;
use core\Main;
use core\tasks\ClearlaggTask;
use core\tasks\KothScheduler;
use core\traits\UtilsTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\types\BossBarColor;
use pocketmine\player\Player;

class BossBarReloadEvent extends PlayerEvent
{

    public static array $hasJob = [];
    private int $time;
    public static array $notSendTimePlayer = [];

    public static bool $sylvanar = false;
    public static float $sylvanarLife = 0.0;

    public static bool $troll = false;
    public static float $trollLife = 0.0;

    use UtilsTrait;

    public function __construct(Player $player, int $time = 0)
    {
        $this->player = $player;
        $this->time = $time;
    }

    public function call(): void
    {
        parent::call();
        if ($this->player->isInCinematic) return;

        if(!Main::getInstance()->getSettingsManager()->getSetting($this->player, 'bossbar')) return;


        if (isset(self::$notSendTimePlayer[$this->player->getXuid()]) and self::$notSendTimePlayer[$this->player->getXuid()] >= time()) {
            return;
        }
        /*
        if (KothScheduler::$hasKoth) {
            if (isset(KothListener::$participateEvent[$this->getPlayer()->getXuid()])) {
                $xuid = KothScheduler::$dominant['xuid'];
                $name = Main::getInstance()->data->getName($xuid) ?? 'Aucun joueur';
                $title = '§fDominant: §6' . $name . " §8| §fPossession: §6" . KothScheduler::$dominant['i'] . '§f/§6100';
                $pk = BossEventPacket::hide($this->getPlayer()->getId());
                if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
                $percent = KothScheduler::$dominant['i'];
                if ($percent > 0) $percent = $percent / 100;
                $pk = BossEventPacket::show($this->getPlayer()->getId(), $title, $percent, 0, BossBarColor::RED);
                $pk->overlay = BossEventPacket::TYPE_TEXTURE;
                if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
                return;
            }
        }
        */


        /*if ($this->getPlayer()->inDual) {
            $title = "§fTemps restant: §f" . $this->calculTime($this->getPlayer()->dualTime);
            $time = $this->getPlayer()->dualTime;
            $percent = 100;
            if ($time > 0) {
                $percent = $time / 60 * 5;
            }
            $pk = BossEventPacket::hide($this->getPlayer()->getId());
            if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
            $pk = BossEventPacket::show($this->getPlayer()->getId(), $title, $percent, color: BossBarColor::RED);
            $pk->overlay = 1;
            if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
            return;
        } else*/if (isset(self::$hasJob[$this->getPlayer()->getXuid()])) {
            if (self::$hasJob[$this->getPlayer()->getXuid()]['time'] >= time()) {
                $jobName = self::$hasJob[$this->getPlayer()->getXuid()]['jobName'];
                $xpTotal = self::$hasJob[$this->getPlayer()->getXuid()]['xptotal'];
                $xpTarget = self::$hasJob[$this->getPlayer()->getXuid()]['xptarget'];
                $percent = 0;
                if ($xpTarget > 0 && $xpTotal > 0) {
                    $percent = $xpTotal / $xpTarget;
                }
                $pk = BossEventPacket::hide($this->getPlayer()->getId());
                if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
                $color = BossBarColor::WHITE;
                $title = "§f" . $jobName;
                if ($jobName === 'Mineur') {
                    $color = BossBarColor::YELLOW;
                }
                if ($jobName === 'Farmeur') $color = BossBarColor::GREEN;
                if ($jobName === 'Assassin') $color = BossBarColor::RED;
                if ($jobName === 'Bucheron') $color = BossBarColor::PURPLE;
                if ($jobName === 'Pêcheur') $color = BossBarColor::BLUE;
                if ($jobName === 'Chasseur') $color = BossBarColor::PINK;

                $pk = BossEventPacket::show($this->getPlayer()->getId(), $title, $percent, color: $color);
                $pk->overlay = 1;
                if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
                return;
            }
        }


        if (isset(BossSouls::$playersInBoss[$this->player->getXuid()]) && BossSouls::$hasStarted) {
        $pk = BossEventPacket::hide($this->getPlayer()->getId());
        if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
        $color = BossBarColor::PURPLE;
        $title = "§lBoss: §rLe vengeur d'âme";

        $percent = 0;
        if (BossSouls::$heal > 0 && BossSouls::$maxHeal > 0) {
            $percent = BossSouls::$heal / BossSouls::$maxHeal;
        }

        $pk = BossEventPacket::show($this->getPlayer()->getId(), $title, $percent, color: $color);
        $pk->overlay = 1;
        if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
        return;
    }

        if (KothScheduler::$hasKoth) {
            $player = Main::getInstance()->getDataManager()->getPlayerXuid(KothScheduler::$dominant['xuid']);
            if ($player instanceof Player) {
                $title = "§6§l- §r§fJoueur: §6" . $player->getName() . " §l§6-";
                $pk = BossEventPacket::hide($this->getPlayer()->getId());

               $this->player->getNetworkSession()->sendDataPacket($pk);

                $percent = KothScheduler::$dominant['i'];
                if ($percent > 0) $percent = $percent / 100;
                $pk = BossEventPacket::show($this->getPlayer()->getId(), $title, $percent, color: BossBarColor::YELLOW);
                $pk->overlay = 1;


                $this->player->getNetworkSession()->sendDataPacket($pk);
                }
            return;
        }


        if (self::$sylvanar && in_array($this->player, Peste::$isInBoss)) {
            $pk = BossEventPacket::hide($this->getPlayer()->getId());
            if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
            $color = BossBarColor::PURPLE;

            $percent = 0;
            if (self::$sylvanarLife > 0 && 5000 > 0) {
                $percent = self::$sylvanarLife / 5000;
            }

            if ($percent >= 0.5) {
                $title = "sylvanar_1";
            } elseif ($percent >= 0.25) {
                $title = "sylvanar_2";
            } else $title = "sylvanar_3";

            $pk = BossEventPacket::show($this->getPlayer()->getId(), $title, $percent, color: $color);
            $pk->overlay = 1;
            if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
            return;
        }


        if (self::$troll && in_array($this->player, TrollBoss::$isInBoss)) {
            $pk = BossEventPacket::hide($this->getPlayer()->getId());
            if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
            $color = BossBarColor::PURPLE;

            $percent = 0;
            if (self::$trollLife > 0 && 5000 > 0) {
                $percent = self::$trollLife / 5000;
            }

            $title = "TROLL";

            $pk = BossEventPacket::show($this->getPlayer()->getId(), $title, $percent, color: $color);
            $pk->overlay = 1;
            if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
            return;
        }


        if (Nexus::$isRunning && Nexus::$entity instanceof Nexus) {
            if (Nexus::$entity->getPosition()->distance($this->player->getPosition()) <= 50) {
                $pk = BossEventPacket::hide($this->getPlayer()->getId());
                if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
                $color = BossBarColor::PURPLE;

                $percent = 0;
                if (Nexus::$nexusLife > 0 && Nexus::$entity->getMaxHealth() > 0) {
                    $percent = Nexus::$nexusLife / Nexus::$entity->getMaxHealth();
                }

                $title = "NEXUS";

                $pk = BossEventPacket::show($this->getPlayer()->getId(), $title, $percent, color: $color);
                $pk->overlay = 1;
                if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
                return;
            }
        }


        $title = "§egoldrushmc.fun";
        $pk = BossEventPacket::hide($this->getPlayer()->getId());
        if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);
        $timeClearlagg = ClearlaggTask::$timee;
        if ($timeClearlagg > 0) {
            $percent = $timeClearlagg / 600 * 100;
            $percent = $percent / 100;
        } else $percent = 0.0;
        $pk = BossEventPacket::show($this->getPlayer()->getId(), "§l" . $title, $percent, color: BossBarColor::YELLOW);
        $pk->overlay = 1;
        if ($this->player->isConnected()) $this->player->getNetworkSession()->sendDataPacket($pk);

    }
}