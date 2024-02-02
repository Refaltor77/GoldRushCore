<?php

namespace core\tasks;

use core\entities\AirDrops;
use core\entities\Nexus;
use core\entities\TrollBoss;
use core\Main;
use core\managers\jobs\JobsManager;
use core\messages\Messages;
use core\services\Query;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use DateTime;
use pocketmine\block\tile\Chest;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;

class TaskCheckupHours extends Task
{

    const LUNDI = "1";
    const MARDI = "2";
    const MERCREDI = "3";
    const JEUDI = "4";
    const VENDREDI = "5";
    const SAMEDI = "6";
    const DIMANCHE = "7";


    public function onRun(): void
    {
        date_default_timezone_set('Europe/Paris');
        $heureActuelle = new DateTime();

        foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
            $world->setTime(World::TIME_DAY);
        }


        $moisActuel = $heureActuelle->format('m');


        $storage = Main::getInstance()->storageData;


        if ($storage->hasData('wype', 'last_month')) {
            // Obtenez la date de la dernière exécution
            $derniereExecution = new DateTime($storage->getData('wype', 'last_month'));

            // Comparez les mois de la dernière exécution et du mois actuel
            if ($derniereExecution->format('m') != $moisActuel) {
                // Un mois s'est écoulé, vous pouvez exécuter votre code ici

                // Mettez à jour la date de la dernière exécution
                $storage->setData('wype', 'last_month', $heureActuelle->format('Y-m-d H:i:s'));

                // Votre code à exécuter une fois par mois
            }
        } else {
            // La variable 'last_month' n'existe pas, vous pouvez l'initialiser
            $storage->setData('wype', 'last_month', $heureActuelle->format('Y-m-d H:i:s'));
            Main::getInstance()->getFactionManager()->wypePower();
        }

        switch ($heureActuelle->format("N")) {
            case self::LUNDI:
                switch ($heureActuelle->format("H:1")) {
                    case "00:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::HUNTER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6chasseur§f est désormais doublée pendant une heure !");
                        break;
                    case "01:00":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "01:30":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "01:45":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "02:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::FARMER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6farmeur§f est désormais doublée pendant une heure !");
                        break;
                    case "03:00":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "03:30":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "03:45":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "04:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::LUMBERJACK);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6bucheron§f est désormais doublée pendant une heure !");
                        break;
                    case "09:00":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "09:30":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "09:45":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "10:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::FARMER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6farmeur§f est désormais doublée pendant une heure !");
                        break;
                    case "11:00":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "11:30":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "11:45":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "12:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::MINOR);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6mineur§f est désormais doublée pendant une heure !");
                        break;
                    case "13:00":
                        Main::getInstance()->getChestRefillManager()->refill();
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f Les coffres en Warzone §6sont pleins de stuff !\n§fCoordonnées : §6x§f(§6192§f) §6z§f(§6-286§f)");
                        break;
                    case "13:30":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "13:45":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "14:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::LUMBERJACK);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Bucheron§f est désormais doublée pendant une heure !");
                        break;
                    case "15:00":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "15:30":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "15:45":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "16:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::FARMER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Farmeur§f est désormais doublée pendant une heure !");
                        break;
                    case "18:00":
                        $airdrop = new AirDrops(AirDrops::getRandomPos());
                        $airdrop->spawnToAll();
                        break;
                    case "19:00":
                        $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
        $world->loadChunk(-234 >> 4, -3 >>4);


        $oldTroll = $world->getNearestEntity(new Position(-234, 79, 3, $world), 100, TrollBoss::class);
        if ($oldTroll instanceof TrollBoss) {
            if (!$oldTroll->isFlaggedForDespawn()) {
                $oldTroll->flagForDespawn();
            }
        }

        $boss = new TrollBoss(new Location(-234, 79, 3, $world, 0, 0));
        $boss->spawnToAll();
        Server::getInstance()->broadcastMessage("§6[§fALERT BOSS§6]\n§r§fLe troll dans la \nWarZone vient de faire sont apparition ! §6[x-234 | z3]");
                        break;
                    case "20:00":
                        Main::getInstance()->getChestRefillManager()->refill();
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f Les coffres en Warzone §6sont pleins de stuff !\n§fCoordonnées : §6x§f(§6192§f) §6z§f(§6-286§f)");
                        break;
                    case "21:00":
                        $pos = Nexus::getSpawnPosition();
                        $pos->getWorld()->loadChunk($pos->getX() >> 4, $pos->getZ() >> 4);
                        $Nexus = new Nexus($pos);
                        $Nexus->spawnToAll();
                        break;
                }
                break;
            case self::MARDI:
                switch ($heureActuelle->format("H:i")) {
                    case "00:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::FARMER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6farmeur§f est désormais doublée pendant une heure !");
                        break;
                    case "01:00":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "01:30":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "01:45":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "02:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::MINOR);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6mineur§f est désormais doublée pendant une heure !");
                        break;
                    case "09:00":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "09:30":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "09:45":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "10:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::FARMER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6farmeur§f est désormais doublée pendant une heure !");
                        break;
                    case "11:00":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "11:30":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "11:45":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "12:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::LUMBERJACK);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6bucheron§f est désormais doublée pendant une heure !");
                        break;
                    case "13:00":
                        Main::getInstance()->getChestRefillManager()->refill();
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f Les coffres en Warzone §6sont pleins de stuff !\n§fCoordonnées : §6x§f(§6192§f) §6z§f(§6-286§f)");
                        break;
                    case "13:30":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "13:45":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "14:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::MINOR);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Mineur§f est désormais doublée pendant une heure !");
                        break;
                    case "15:00":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "15:30":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "15:45":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "16:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::HUNTER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Chasseur§f est désormais doublée pendant une heure !");
                        break;
                    case "18:00":
                        if (!KothScheduler::$hasKoth) {
                            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new KothScheduler(), 20);
                        }
                        break;
                    case "19:00":
                        $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
        $world->loadChunk(-234 >> 4, -3 >>4);


        $oldTroll = $world->getNearestEntity(new Position(-234, 79, 3, $world), 100, TrollBoss::class);
        if ($oldTroll instanceof TrollBoss) {
            if (!$oldTroll->isFlaggedForDespawn()) {
                $oldTroll->flagForDespawn();
            }
        }

        $boss = new TrollBoss(new Location(-234, 79, 3, $world, 0, 0));
        $boss->spawnToAll();
        Server::getInstance()->broadcastMessage("§6[§fALERT BOSS§6]\n§r§fLe troll dans la \nWarZone vient de faire sont apparition ! §6[x-234 | z3]");
                        break;
                    case "20:00":
                        Main::getInstance()->getChestRefillManager()->refill();
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f Les coffres en Warzone §6sont pleins de stuff !\n§fCoordonnées : §6x§f(§6192§f) §6z§f(§6-286§f)");
                        break;
                    case "22:00":
                        $airdrop = new AirDrops(AirDrops::getRandomPos());
                        $airdrop->spawnToAll();
                        break;
                }
                break;
            case self::MERCREDI:
                switch ($heureActuelle->format("H:i")) {
                    case "00:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::FARMER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6mineur§f est désormais doublée pendant une heure !");
                        break;
                    case "01:00":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "01:30":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "01:45":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "02:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::LUMBERJACK);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6chasseur§f est désormais doublée pendant une heure !");
                        break;
                    case "09:00":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "09:30":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "09:45":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "10:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::MINOR);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6mineur§f est désormais doublée pendant une heure !");
                        break;
                    case "11:00":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "11:30":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "11:45":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "12:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::HUNTER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6chasseur§f est désormais doublée pendant une heure !");
                        break;
                    case "13:00":
                        Main::getInstance()->getChestRefillManager()->refill();
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f Les coffres en Warzone §6sont pleins de stuff !\n§fCoordonnées : §6x§f(§6192§f) §6z§f(§6-286§f)");
                        break;
                    case "13:30":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "13:45":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "14:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::FARMER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Farmeur§f est désormais doublée pendant une heure !");
                        break;
                    case "14:30":
                        $airdrop = new AirDrops(AirDrops::getRandomPos());
                        $airdrop->spawnToAll();
                        break;
                    case "15:00":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "15:30":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "15:45":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "16:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::MINOR);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Mineur§f est désormais doublée pendant une heure !");
                        break;
                    case "18:00":
                        $pos = Nexus::getSpawnPosition();
                        $pos->getWorld()->loadChunk($pos->getX() >> 4, $pos->getZ() >> 4);
                        $Nexus = new Nexus($pos);
                        $Nexus->spawnToAll();
                        break;
                    case "19:00":
                        $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
        $world->loadChunk(-234 >> 4, -3 >>4);


        $oldTroll = $world->getNearestEntity(new Position(-234, 79, 3, $world), 100, TrollBoss::class);
        if ($oldTroll instanceof TrollBoss) {
            if (!$oldTroll->isFlaggedForDespawn()) {
                $oldTroll->flagForDespawn();
            }
        }

        $boss = new TrollBoss(new Location(-234, 79, 3, $world, 0, 0));
        $boss->spawnToAll();
        Server::getInstance()->broadcastMessage("§6[§fALERT BOSS§6]\n§r§fLe troll dans la \nWarZone vient de faire sont apparition ! §6[x-234 | z3]");
                        break;
                    case "20:00":
                         if (!KothScheduler::$hasKoth) {
                            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new KothScheduler(), 20);
                        }
                        break;
                    case "21:00":
                        Main::getInstance()->getChestRefillManager()->refill();
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f Les coffres en Warzone §6sont pleins de stuff !\n§fCoordonnées : §6x§f(§6192§f) §6z§f(§6-286§f)");
                        break;
                    case "22:00":
                        $world = Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld();
                        $x = mt_rand(3000, 4000);
                        $z = mt_rand(3000, 4000);
                        $y = $world->getHighestBlockAt($x, $z);
                        $pos = new Position($x, $y, $z, $world);

                        $chest = VanillaBlocks::CHEST();
                        $world->setBlock($pos, $chest);
                        $tile = $world->getTile($pos);
                        if ($tile instanceof Chest) {
                            $lots = [
                                CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_FORCE, 2),
                                CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT, 32),
                                CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SWORD, 2),
                                CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE, 1),
                                CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE, 2),
                                CustomiesItemFactory::getInstance()->get(Ids::KEY_COMMON, 4),
                                CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_HEAL, 4),
                                CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_SPEED, 2),
                                CustomiesItemFactory::getInstance()->get(Ids::UNCLAIM_FINDER_AMETHYST, 1),
                                CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_CHESTPLATE, 1),
                                CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE, 64),
                                CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_NUGGET, 64),
                            ];
                            for ($i = 0; $i < 6; $i++) {
                                $tile->getInventory()->addItem($lots[array_rand($lots)]);
                            }
                            Server::getInstance()->broadcastMessage("§6[§fCHASSE AU TRÉSOR]\n§6Objectif§f: Allez chercher le trésor\navant tout le monde !\n§6Coordonnées§f: (§6x§f)$x (§6z§f)$z");

                        }
                        break;
                }
                break;
            case self::JEUDI:
                switch ($heureActuelle->format("H:i")) {
                    case "00:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::MINOR);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Mineur§f est désormais doublée pendant une heure !");
                        break;
                    case "01:00":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "01:30":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "01:45":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "02:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::HUNTER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Chasseur§f est désormais doublée pendant une heure !");
                        break;
                    case "09:00":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "09:30":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "09:45":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "10:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::FARMER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6farmeur§f est désormais doublée pendant une heure !");
                        break;
                    case "11:00":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "11:30":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "11:45":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "12:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::MINOR);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6mineur§f est désormais doublée pendant une heure !");
                        break;
                    case "13:00":
                        Main::getInstance()->getChestRefillManager()->refill();
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f Les coffres en Warzone §6sont pleins de stuff !\n§fCoordonnées : §6x§f(§6192§f) §6z§f(§6-286§f)");
                        break;
                    case "13:30":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "13:45":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "14:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::HUNTER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Chasseur§f est désormais doublée pendant une heure !");
                        break;
                    case "15:00":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "15:30":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "15:45":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "16:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::LUMBERJACK);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Bucheron§f est désormais doublée pendant une heure !");
                        break;
                    case "18:00":
                        $airdrop = new AirDrops(AirDrops::getRandomPos());
                        $airdrop->spawnToAll();
                        break;
                    case "19:00":
                        $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
        $world->loadChunk(-234 >> 4, -3 >>4);


        $oldTroll = $world->getNearestEntity(new Position(-234, 79, 3, $world), 100, TrollBoss::class);
        if ($oldTroll instanceof TrollBoss) {
            if (!$oldTroll->isFlaggedForDespawn()) {
                $oldTroll->flagForDespawn();
            }
        }

        $boss = new TrollBoss(new Location(-234, 79, 3, $world, 0, 0));
        $boss->spawnToAll();
        Server::getInstance()->broadcastMessage("§6[§fALERT BOSS§6]\n§r§fLe troll dans la \nWarZone vient de faire sont apparition ! §6[x-234 | z3]");
                        break;
                    case "20:00":
                        Main::getInstance()->getChestRefillManager()->refill();
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f Les coffres en Warzone §6sont pleins de stuff !\n§fCoordonnées : §6x§f(§6192§f) §6z§f(§6-286§f)");
                        break;
                    case "21:00":
                        $pos = Nexus::getSpawnPosition();
                        $pos->getWorld()->loadChunk($pos->getX() >> 4, $pos->getZ() >> 4);
                        $Nexus = new Nexus($pos);
                        $Nexus->spawnToAll();
                        break;
                }
                break;
            case self::VENDREDI:
                switch ($heureActuelle->format("H:i")) {
                    case "00:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::MINOR);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Mineur§f est désormais doublée pendant une heure !");
                        break;
                    case "01:00":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "01:30":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "01:45":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "02:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::FARMER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Farmeur§f est désormais doublée pendant une heure !");
                        break;
                    case "09:00":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "09:30":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "09:45":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "10:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::FARMER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6farmeur§f est désormais doublée pendant une heure !");
                        break;
                    case "11:00":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "11:30":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "11:45":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "12:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::LUMBERJACK);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6bucheron§f est désormais doublée pendant une heure !");
                        break;
                    case "13:00":
                        Main::getInstance()->getChestRefillManager()->refill();
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f Les coffres en Warzone §6sont pleins de stuff !\n§fCoordonnées : §6x§f(§6192§f) §6z§f(§6-286§f)");
                        break;
                    case "13:30":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "13:45":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "14:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::MINOR);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Mineur§f est désormais doublée pendant une heure !");
                        break;
                    case "15:00":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "15:30":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "15:45":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "16:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::HUNTER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Chasseur§f est désormais doublée pendant une heure !");
                        break;
                    case "18:00":
                         if (!KothScheduler::$hasKoth) {
                            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new KothScheduler(), 20);
                        }
                        break;
                    case "19:00":
                        $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
        $world->loadChunk(-234 >> 4, -3 >>4);


        $oldTroll = $world->getNearestEntity(new Position(-234, 79, 3, $world), 100, TrollBoss::class);
        if ($oldTroll instanceof TrollBoss) {
            if (!$oldTroll->isFlaggedForDespawn()) {
                $oldTroll->flagForDespawn();
            }
        }

        $boss = new TrollBoss(new Location(-234, 79, 3, $world, 0, 0));
        $boss->spawnToAll();
        Server::getInstance()->broadcastMessage("§6[§fALERT BOSS§6]\n§r§fLe troll dans la \nWarZone vient de faire sont apparition ! §6[x-234 | z3]");
                        break;
                    case "20:00":
                        Main::getInstance()->getChestRefillManager()->refill();
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f Les coffres en Warzone §6sont pleins de stuff !\n§fCoordonnées : §6x§f(§6192§f) §6z§f(§6-286§f)");
                        break;
                    case "22:00":
                        $airdrop = new AirDrops(AirDrops::getRandomPos());
                        $airdrop->spawnToAll();
                        break;
                }
                break;
            case self::SAMEDI:
                switch ($heureActuelle->format("H:i")) {
                    case "00:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::HUNTER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6chasseur§f est désormais doublée pendant une heure !");
                        break;
                    case "01:00":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "01:30":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "01:45":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "02:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::LUMBERJACK);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6bucheron§f est désormais doublée pendant une heure !");
                        break;
                    case "07:00":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "07:30":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "07:45":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "08:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::FARMER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Farmeur§f est désormais doublée pendant une heure !");
                        break;
                    case "09:00":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "09:30":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "09:45":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "10:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::LUMBERJACK);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6bucheron§f est désormais doublée pendant une heure !");
                        break;
                    case "11:00":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "11:30":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "11:45":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "12:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::MINOR);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Mineur§f est désormais doublée pendant une heure !");
                        break;
                    case "13:00":
                        Main::getInstance()->getChestRefillManager()->refill();
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f Les coffres en Warzone §6sont pleins de stuff !\n§fCoordonnées : §6x§f(§6192§f) §6z§f(§6-286§f)");
                        break;
                    case "13:30":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "13:45":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "14:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::HUNTER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Chasseur§f est désormais doublée pendant une heure !");
                        break;
                    case "14:30":
                        $airdrop = new AirDrops(AirDrops::getRandomPos());
                        $airdrop->spawnToAll();
                        break;
                    case "16:00":
                        if (!KothScheduler::$hasKoth) {
                            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new KothScheduler(), 20);
                        }
                        break;
                    case "18:00":
                        $pos = Nexus::getSpawnPosition();
                        $pos->getWorld()->loadChunk($pos->getX() >> 4, $pos->getZ() >> 4);
                        $Nexus = new Nexus($pos);
                        $Nexus->spawnToAll();
                        break;
                    case "19:00":
                        $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
        $world->loadChunk(-234 >> 4, -3 >>4);


        $oldTroll = $world->getNearestEntity(new Position(-234, 79, 3, $world), 100, TrollBoss::class);
        if ($oldTroll instanceof TrollBoss) {
            if (!$oldTroll->isFlaggedForDespawn()) {
                $oldTroll->flagForDespawn();
            }
        }

        $boss = new TrollBoss(new Location(-234, 79, 3, $world, 0, 0));
        $boss->spawnToAll();
        Server::getInstance()->broadcastMessage("§6[§fALERT BOSS§6]\n§r§fLe troll dans la \nWarZone vient de faire sont apparition ! §6[x-234 | z3]");
                        break;
                    case "20:00":
                        $world = Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld();
                        $x = mt_rand(2000, 3000);
                        $z = mt_rand(2000, 3000);
                        $y = $world->getHighestBlockAt($x, $z);
                        $pos = new Position($x, $y, $z, $world);

                        $chest = VanillaBlocks::CHEST();
                        $world->setBlock($pos, $chest);
                        $tile = $world->getTile($pos);
                        if ($tile instanceof Chest) {
                            $lots = [
                                CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_FORCE, 2),
                                CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT, 32),
                                CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SWORD, 2),
                                CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE, 1),
                                CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE, 2),
                                CustomiesItemFactory::getInstance()->get(Ids::KEY_COMMON, 4),
                                CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_HEAL, 4),
                                CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_SPEED, 2),
                                CustomiesItemFactory::getInstance()->get(Ids::UNCLAIM_FINDER_AMETHYST, 1),
                                CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_CHESTPLATE, 1),
                                CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE, 64),
                                CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_NUGGET, 64),
                            ];
                            for ($i = 0; $i < 6; $i++) {
                                $tile->getInventory()->addItem($lots[array_rand($lots)]);
                            }
                            Server::getInstance()->broadcastMessage("§6[§fCHASSE AU TRÉSOR]\n§6Objectif§f: Allez chercher le trésor\navant tout le monde !\n§6Coordonnées§f: (§6x§f)$x (§6z§f)$z");

                        }
                        break;
                    case "21:00":
                        Main::getInstance()->getChestRefillManager()->refill();
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f Les coffres en Warzone §6sont pleins de stuff !\n§fCoordonnées : §6x§f(§6192§f) §6z§f(§6-286§f)");
                        break;
                }
                break;
            case self::DIMANCHE:
                switch ($heureActuelle->format("H:i")) {
                    case "00:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::MINOR);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6mineur§f est désormais doublée pendant une heure !");
                        break;
                    case "01:00":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "01:30":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "01:45":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "02:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::LUMBERJACK);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6bucheron§f est désormais doublée pendant une heure !");
                        break;
                    case "03:00":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "03:30":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "03:45":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "04:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::FARMER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6farmeur§f est désormais doublée pendant une heure !");
                        break;
                    case "07:00":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "07:30":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "07:45":
                        $jobs = "Bucheron";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "08:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::LUMBERJACK);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6bucheron§f est désormais doublée pendant une heure !");
                        break;
                    case "09:00":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "09:30":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "09:45":
                        $jobs = "Mineur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "10:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::MINOR);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6mineur§f est désormais doublée pendant une heure !");
                        break;
                    case "11:00":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une heure !");
                        break;
                    case "11:30":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "11:45":
                        $jobs = "Farmeur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "12:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::FARMER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Mineur§f est désormais doublée pendant une heure !");
                        break;
                    case "13:00":
                        Main::getInstance()->getChestRefillManager()->refill();
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f Les coffres en Warzone §6sont pleins de stuff !\n§fCoordonnées : §6x§f(§6192§f) §6z§f(§6-286§f)");
                        break;
                    case "13:30":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans une 30 minutes !");
                        break;
                    case "13:45":
                        $jobs = "Chasseur";
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6$jobs"."§f sera doublé dans 15 minutes");
                        break;
                    case "14:00":
                        Main::getInstance()->getJobsManager()->setXpJobX2(JobsManager::HUNTER);
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f L'expérience dans le métier de §6Chasseur§f est désormais doublée pendant une heure !");
                        break;
                    case "14:30":
                        $airdrop = new AirDrops(AirDrops::getRandomPos());
                        $airdrop->spawnToAll();
                        break;
                    case "16:00":
                        if (!KothScheduler::$hasKoth) {
                            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new KothScheduler(), 20);
                        }
                        break;
                    case "18:00":
                        $pos = Nexus::getSpawnPosition();
                        $pos->getWorld()->loadChunk($pos->getX() >> 4, $pos->getZ() >> 4);
                        $Nexus = new Nexus($pos);
                        $Nexus->spawnToAll();
                        break;
                    case "19:00":
                        $world = Server::getInstance()->getWorldManager()->getDefaultWorld();
        $world->loadChunk(-234 >> 4, -3 >>4);


        $oldTroll = $world->getNearestEntity(new Position(-234, 79, 3, $world), 100, TrollBoss::class);
        if ($oldTroll instanceof TrollBoss) {
            if (!$oldTroll->isFlaggedForDespawn()) {
                $oldTroll->flagForDespawn();
            }
        }

        $boss = new TrollBoss(new Location(-234, 79, 3, $world, 0, 0));
        $boss->spawnToAll();
        Server::getInstance()->broadcastMessage("§6[§fALERT BOSS§6]\n§r§fLe troll dans la \nWarZone vient de faire sont apparition ! §6[x-234 | z3]");
                        break;
                    case "20:00":
                        $world = Main::getInstance()->getServer()->getWorldManager()->getDefaultWorld();
                        $x = mt_rand(2000, 3000);
                        $z = mt_rand(2000, 3000);
                        $y = $world->getHighestBlockAt($x, $z);
                        $pos = new Position($x, $y, $z, $world);

                        $chest = VanillaBlocks::CHEST();
                        $world->setBlock($pos, $chest);
                        $tile = $world->getTile($pos);
                        if ($tile instanceof Chest) {
                            $lots = [
                                CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_FORCE, 2),
                                CustomiesItemFactory::getInstance()->get(Ids::EMERALD_INGOT, 32),
                                CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_SWORD, 2),
                                CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE, 1),
                                CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE, 2),
                                CustomiesItemFactory::getInstance()->get(Ids::KEY_COMMON, 4),
                                CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_HEAL, 4),
                                CustomiesItemFactory::getInstance()->get(Ids::ALCOOL_PUISSANT_SPEED, 2),
                                CustomiesItemFactory::getInstance()->get(Ids::UNCLAIM_FINDER_AMETHYST, 1),
                                CustomiesItemFactory::getInstance()->get(Ids::AMETHYST_CHESTPLATE, 1),
                                CustomiesItemFactory::getInstance()->get(Ids::EMERALD_DYNAMITE, 64),
                                CustomiesItemFactory::getInstance()->get(Ids::PLATINUM_NUGGET, 64),
                            ];
                            for ($i = 0; $i < 6; $i++) {
                                $tile->getInventory()->addItem($lots[array_rand($lots)]);
                            }
                            Server::getInstance()->broadcastMessage("§6[§fCHASSE AU TRÉSOR]\n§6Objectif§f: Allez chercher le trésor\navant tout le monde !\n§6Coordonnées§f: (§6x§f)$x (§6z§f)$z");

                        }
                        break;
                    case "21:00":
                        Main::getInstance()->getChestRefillManager()->refill();
                        Server::getInstance()->broadcastMessage("§6§l[§r§fEVENT§6§l]§r§f Les coffres en Warzone §6sont pleins de stuff !\n§fCoordonnées : §6x§f(§6192§f) §6z§f(§6-286§f)");
                        break;
                    case "23:59":
                        Main::getInstance()->getFactionManager()->sendRecompenseFactionQuest();
                        Main::getInstance()->getFactionManager()->setNewQuestHebdo();
                        Server::getInstance()->broadcastMessage("§6[§fALERT FACTION§6]\n§fUne nouvelle quête hebdomadaire est\ndisponible pour les factions jusqu'au\n§6dimanche prochain à 23h59 ! §fEffectuez la commande §6/f quest §f!");
                        break;

                }
                break;
        }



        if ($heureActuelle->format('H:i') === '04:00') {

            $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
            $sell = $config->get('shop');


            $ids = [
                'minecraft:melon_slice' => mt_rand(2, 4),
                'minecraft:potatoes' => mt_rand(3, 8),
                'minecraft:carrots' => mt_rand(3, 8),
                'minecraft:wheat' => mt_rand(6, 12),
                'minecraft:beetroots' => mt_rand(6,12),
                'minecraft:pumpkin' => mt_rand(4, 6),
                'minecraft:sugarcane' => mt_rand(4, 6),
                'minecraft:cactus' => mt_rand(3, 5),
                Ids::RAISIN => mt_rand(6, 12),
                Ids::BERRY_BLUE => mt_rand(6, 12),
                Ids::BERRY_PINK => mt_rand(6, 12),
                Ids::BERRY_YELLOW => mt_rand(6, 12),
                Ids::BERRY_BLACK => mt_rand(6, 12),
            ];


            foreach ($sell['Farming']['items'] as $index => $values) {
                $id = $values['idMeta'];
                if (in_array($id, array_keys($ids))) {
                    if (isset($values['sell'])) {
                        $sell['Farming']['items'][$index]['sell'] = $ids[$id];
                    }
                }
            }

            $config->set('shop', $sell);
            $config->save();

            Main::getInstance()->getBourseManager()->setBourse($ids);

            Server::getInstance()->broadcastMessage("§l§6[§r§fALERTE BOURSE§l§6] §r§fLa bourse des cultures a été changée !");
        }


        if ($heureActuelle->format("H:i") === "00:00") {
            Main::getInstance()->getJobsManager()->resetXpQuoti();
        }

    }




    public function syncListeConnected(): void {
        $queryMinage = Query::query("goldrushmc.fun", 19133);
        $players = $queryMinage['Players'];
        $count = 0;
        if (!is_null($players)) {
            $count =$players;
        }

    }
}