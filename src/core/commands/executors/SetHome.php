<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\events\LogEvent;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class SetHome extends Executor
{
    use UtilsTrait;

    public function __construct(string $name = 'sethome', string $description = "Créer un home", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(Messages::message("§cCommande exécutable uniquement sur le serveur."));
            return;
        }

        if (isset($args[0])) {
            $homeName = strval($args[0]);
            $pos = $sender->getPosition();
            if (!$this->getPlugin()->getHomeManager()->hasHome($xuid = $sender->getXuid(), $homeName)) {
                if (!$this->getPlugin()->getAreaManager()->isInArea($pos)) {
                    $countHome = Main::getInstance()->getHomeManager()->getHomeCount($sender);

                    $rank = Main::getInstance()->getRankManager()->getSupremeRankPriority($sender->getXuid());

                    switch ($rank) {
                        default:
                        case 'PLAYER':
                            if ($countHome >= 3) {
                                $sender->sendMessage(Messages::message("§cVous avez atteint la limite de homes."));
                                return;
                            }

                            $this->getPlugin()->getHomeManager()->setHome($xuid, $homeName, $pos);
                            $sender->sendMessage(Messages::message("§aVous avez crée le home §6" . $homeName));
                            break;
                        case 'FARMER':
                            if ($countHome >= 5) {
                                $sender->sendMessage(Messages::message("§cVous avez atteint la limite de homes."));
                                return;
                            }

                            $this->getPlugin()->getHomeManager()->setHome($xuid, $homeName, $pos);
                            $sender->sendMessage(Messages::message("§aVous avez crée le home §6" . $homeName));
                            break;
                        case 'BANDIT':
                            if ($countHome >= 5) {
                                $sender->sendMessage(Messages::message("§cVous avez atteint la limite de homes."));
                                return;
                            }

                            $this->getPlugin()->getHomeManager()->setHome($xuid, $homeName, $pos);
                            $sender->sendMessage(Messages::message("§aVous avez crée le home §6" . $homeName));
                            break;
                        case 'BRAQUEUR':
                            if ($countHome >= 6) {
                                $sender->sendMessage(Messages::message("§cVous avez atteint la limite de homes."));
                                return;
                            }

                            $this->getPlugin()->getHomeManager()->setHome($xuid, $homeName, $pos);
                            $sender->sendMessage(Messages::message("§aVous avez crée le home §6" . $homeName));
                            break;
                        case 'COWBOY':
                            if ($countHome >= 8) {
                                $sender->sendMessage(Messages::message("§cVous avez atteint la limite de homes."));
                                return;
                            }

                            $this->getPlugin()->getHomeManager()->setHome($xuid, $homeName, $pos);
                            $sender->sendMessage(Messages::message("§aVous avez crée le home §6" . $homeName));
                            break;
                        case 'MARSHALL':
                            if ($countHome >= 12) {
                                $sender->sendMessage(Messages::message("§cVous avez atteint la limite de homes."));
                                return;
                            }

                            $this->getPlugin()->getHomeManager()->setHome($xuid, $homeName, $pos);
                            $sender->sendMessage(Messages::message("§aVous avez crée le home §6" . $homeName));
                            break;
                        case 'SHERIF':
                            if ($countHome >= 16) {
                                $sender->sendMessage(Messages::message("§cVous avez atteint la limite de homes."));
                                return;
                            }

                            $this->getPlugin()->getHomeManager()->setHome($xuid, $homeName, $pos);
                            $sender->sendMessage(Messages::message("§aVous avez crée le home §6" . $homeName));
                            break;
                    }
                    (new LogEvent($sender->getName()." a crée un home {$homeName}({$pos->getFloorX()},{$pos->getFloorY()},{$pos->getFloorZ()})", LogEvent::HOME_TYPE))->call();
                } else $sender->sendMessage(Messages::message("§cVous êtes dans une zone protéger, les homes sont interdits."));
            } else $sender->sendMessage(Messages::message("§cVotre home existe déjà !"));
        } else $sender->sendMessage(Messages::message("§cVous devez préciser le nom de votre home."));
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}