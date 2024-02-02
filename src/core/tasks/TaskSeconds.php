<?php


namespace core\tasks;

use core\entities\AirDrops;
use core\entities\BossSouls;
use core\entities\Peste;
use core\entities\Totem;
use core\entities\TrollBoss;
use core\events\BossBarReloadEvent;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\entity\Location;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;

class TaskSeconds extends Task
{
    public int $seconds = 0;
    public int $secondsMusic = 0;
    public array $hasSendMusic = [];
    public int $secondBoss = 0;

    public function formatTemps($secondes)
    {
        $heures = floor($secondes / 3600);
        $minutes = floor(($secondes % 3600) / 60);
        $secondes = $secondes % 60;

        return sprintf("%02d:%02d:%02d", $heures, $minutes, $secondes);
    }


    public function onRun(): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (Main::getInstance()->getXpManager()->hasDobble($player)) {
                $startTime = Main::getInstance()->getXpManager()->cache[$player->getXuid()]['time_xp'];


                $timeS = $startTime - time();


                $player->sendTip("§6" . $this->formatTemps($timeS));
            }
        }


        $array_pos = [
            [212, 72, -16],
            [268, 74, 62],
            [235, 76, 136],
            [198, 73, 198],
            [196, 72, 256],
            [157, 70, 242],
            [117, 70, 278],
            [-92, 71, 275],
            [-128, 71, 205],
            [-244, 72, 232],
            [-209, 76, 138],
            [-262, 78, 61],
            [-185, 78, -69],
            [271, 36, 492],
            [6989, 63, -954],
        ];


        foreach ($array_pos as $index => $posArray) {
            Server::getInstance()->getWorldManager()->getDefaultWorld()->loadChunk($posArray[0] >> 4, $posArray[1] >> 4);
        }


        if (BossSouls::$hasStarted === false) {
            $pk = new PlaySoundPacket();
            $pk->soundName = "music.boss_souls.ambiance";
            $pk->pitch = 1;
            $pk->x = 6989;
            $pk->y = 63;
            $pk->z = -954;
            $pk->volume = 60;

            $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
            if (!is_null($world)) {
                $players = $world->getNearestEntity(new Position(
                    6989, 62, -954, $world
                ), 60, CustomPlayer::class);
                if (!is_null($players)) {
                    foreach ($players as $player) {
                        if ($player instanceof CustomPlayer) {
                            if (isset($this->hasSendMusic[$player->getXuid()])) {
                                if ($this->hasSendMusic[$player->getXuid()] <= time()) {
                                    $this->hasSendMusic[$player->getXuid()] = time() + (60 * 5) + 4;
                                    $player->getNetworkSession()->sendDataPacket($pk);
                                }
                            } else {
                                $this->hasSendMusic[$player->getXuid()] = time() + (60 * 5) + 4;
                                $player->getNetworkSession()->sendDataPacket($pk);
                            }
                        }
                    }
                }
            }
        } else {
            foreach ($this->hasSendMusic as $xuid => $time) {
                $player = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                if ($player instanceof Player) {
                    $pk = new StopSoundPacket();
                    $pk->soundName = "music.boss_souls.ambiance";
                    $pk->stopAll = true;
                    $player->getNetworkSession()->sendDataPacket($pk);
                }
            }

        }


        if ($this->secondBoss >= 60 * 60 * 6) {
            $this->secondBoss = 0;
            $world = Server::getInstance()->getWorldManager()->getDefaultWorld();


            $world->loadChunk(6989 >> 4, -954 >> 4);
            if (!BossSouls::$hasStarted) {
                $boss = new BossSouls(new Location(6989, 63, -954, $world, 0, 0));
                $boss->spawnToAll();
                Server::getInstance()->broadcastMessage("§6[§fALERT BOSS§6]\n§fVengeur des âmes : §r§fJe suis de retour pour\nhanter GoldRush...");
            }


            $world = Server::getInstance()->getWorldManager()->getWorldByName("crystal_maudit");
            if ($world->isChunkGenerated(260 >> 4, 502 >> 4)) {
                $world->loadChunk(260 >> 4, 502 >> 4);
                if (!BossBarReloadEvent::$sylvanar) {
                    $boss = new Peste(new Location(260, 35, 502, $world, 0, 0));
                    $boss->spawnToAll();
                    Server::getInstance()->broadcastMessage("§6[§fALERT BOSS§6]\n§r§fSylvanar est présent...");
                }
            }
        }


        if ($this->seconds === 60 * 60 || $this->seconds === 60 * 60 * 2) {
            $randomPos = AirDrops::getRandomPos();
            $largage = new AirDrops($randomPos);
            $largage->spawnToAll();
        }

        if ($this->seconds === 60 * 60 * 3) {

            // TODO: nexus

            $this->seconds = 0;
        }
        $this->seconds++;


        if ($this->secondsMusic === (60 * 5) + 4) $this->secondsMusic = 0;
        $this->secondsMusic++;
        $this->secondBoss++;
    }
}