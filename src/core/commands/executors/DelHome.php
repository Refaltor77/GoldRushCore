<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\events\LogEvent;
use core\managers\homes\HomeManager;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class DelHome extends Executor
{
    use UtilsTrait;

    public function __construct(string $name = 'delhome', string $description = "Supprimer un home", ?string $usageMessage = null, array $aliases = [])
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
                $this->getPlugin()->getHomeManager()->deleteHome($xuid, $homeName);
                $sender->sendMessage(Messages::message("§aVous avez supprimé le home §6" . $homeName));
                (new LogEvent($sender->getName()." a supprimé son home {$homeName}", LogEvent::HOME_TYPE))->call();
            } else $sender->sendMessage(Messages::message("§cVotre home n'existe pas !"));
        } else $sender->sendMessage(Messages::message("§cVous devez préciser le nom de votre home."));
    }

    public function loadOptions(?Player $player): CommandData
    {
        $array = [];


        if ($player instanceof Player) {
            if (isset(HomeManager::$cache[$xuid = $player->getXuid()])) {
                foreach (HomeManager::$cache[$xuid] as $homeName => $values) {
                    $array[$name = str_replace(' ', '_', strtolower($homeName))] = $name;
                }
            }
        }


        $this->addOptionEnum(0, "Vos homes", true, 'Homes', $array);
        return $this->getCommandData();
    }
}