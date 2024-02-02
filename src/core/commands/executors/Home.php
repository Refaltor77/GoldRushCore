<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\events\LogEvent;
use core\Main;
use core\managers\homes\HomeManager;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\tasks\Teleport;
use core\tasks\TeleportInterServer;
use core\traits\HomeTrait;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Home extends Executor
{
    use UtilsTrait;
    use HomeTrait;

    public function __construct(string $name = 'home', string $description = "Accéder à un home existant.", ?string $usageMessage = null, array $aliases = [
        'h', 'homes'
    ])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(Messages::message("§cCommande exécutable uniquement sur le serveur."));
            return;
        }

        if ($sender->hasTagged()) {
            $sender->sendMessage(Messages::message("§cVous êtes en combat !"));
            return;
        }

        if (isset($args[0])) {
            $homeName = strval($args[0]);
            if ($this->getPlugin()->getHomeManager()->hasHome($xuid = $sender->getXuid(), $homeName)) {
                $xuidServerInterServer = $this->getPlugin()->getHomeManager()->getServerXuidHome($xuid, $homeName);
                if ($xuidServerInterServer !== Main::XUID_SERVER) {
                    $address = $this->getIpFromID($xuidServerInterServer);
                    $tpInfo = $this->getPlugin()->getHomeManager()->getHomeInfo($xuid, $homeName);
                    $this->getPlugin()->getScheduler()->scheduleRepeatingTask(new TeleportInterServer($sender, $tpInfo, $address), 20);
                    (new LogEvent($sender->getName()." s'est téléporté a sont home {$homeName}({$tpInfo[0]},{$tpInfo[1]},{$tpInfo[2]},{$tpInfo[3]})", LogEvent::BOX_TYPE))->call();
                } else {
                    $pos = $this->getPlugin()->getHomeManager()->getPosHome($xuid, $homeName);
                    $this->getPlugin()->getScheduler()->scheduleRepeatingTask(new Teleport($sender, $pos), 20);
                    (new LogEvent($sender->getName()." s'est téléporté a sont home {$homeName}({$pos->getX()},{$pos->getY()},{$pos->getZ()})", LogEvent::BOX_TYPE))->call();
                }
            } else $sender->sendMessage(Messages::message("§cVotre home n'existe pas !"));
        } else $sender->sendMessage(Messages::message("§cVous devez préciser le nom de votre home."));
    }

    public function loadOptions(?Player $player): CommandData
    {
        $array = [];


        if ($player instanceof Player) {
            if (isset(HomeManager::$cache[$xuid = $player->getXuid()])) {
                foreach (HomeManager::$cache[$xuid] as $homeName => $values) {
                    $array[] = str_replace(' ', '_', strtolower($homeName));
                }
            }
        }

        $this->addOptionEnum(0, "Vos homes", true, 'Homes', $array);

        return parent::loadOptions($player);
    }
}