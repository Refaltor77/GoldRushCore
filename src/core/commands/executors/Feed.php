<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\sound\XpCollectSound;

class Feed extends Executor
{
    public static array $feed = [];

    public function __construct(string $name = 'feed', string $description = "Se nourrir", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("feed.use");
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {

        $rank = Main::getInstance()->getRankManager()->getSupremeRankPriority($sender->getXuid());

        $cooldown = 0;
        switch ($rank) {
            case "COWBOY":
                $cooldown = 60 * 5;
                break;
            case "MARSHALL":
                $cooldown = 60 * 2;
                break;
        }


        if (!isset($args[0])) {
            if (isset(self::$feed[$sender->getXuid()])) {
                if (self::$feed[$sender->getXuid()] <= time()) {
                    $sender->getHungerManager()->setFood(20);
                    $sender->getHungerManager()->setSaturation(20);
                    $sender->getWorld()->addSound($sender->getLocation(), new XpCollectSound(), [$sender]);
                    $sender->sendMessage(Messages::message("§aVous avez été nourris !"));
                    self::$feed[$sender->getXuid()] = $cooldown;
                } else {
                    $sender->sendMessage(Messages::message("§cIl vous reste §4" . self::$feed[$sender->getXuid()] - time()) . "§c seconde(s) pour vous nourrir.");
                }
            } else {
                $sender->getHungerManager()->setFood(20);
                $sender->getHungerManager()->setSaturation(20);
                $sender->getWorld()->addSound($sender->getLocation(), new XpCollectSound(), [$sender]);
                $sender->sendMessage(Messages::message("§aVous avez été nourris !"));
                self::$feed[$sender->getXuid()] = $cooldown;
            }
        } else {
            $player = Server::getInstance()->getPlayerByPrefix(strval($args[0]));
            if ($player instanceof Player) {
                if ($player->hasTagged()) {
                    $sender->sendMessage(Messages::message("§cLe joueur est en combat !"));
                    return;
                }
                if (isset(self::$feed[$sender->getXuid()])) {
                    if (self::$feed[$sender->getXuid()] <= time()) {
                        $player->getHungerManager()->setFood(20);
                        $player->getHungerManager()->setSaturation(20);
                        $player->getWorld()->addSound($sender->getLocation(), new XpCollectSound(), [$sender]);
                        $sender->sendMessage(Messages::message("§aVous avez nourris le joueur §f" . $player->getName() . '§a !'));
                        $player->sendMessage(Messages::message("§aVous avez été nourris par le joueur §f" . $player->getName() . '§a !'));
                        self::$feed[$sender->getXuid()] = $cooldown;
                    } else {
                        $sender->sendMessage(Messages::message("§cIl vous reste §4" . self::$feed[$sender->getXuid()] - time()) . "§c seconde(s) pour nourrir un joueur.");
                    }
                } else {
                    $player->getHungerManager()->setFood(20);
                    $player->getHungerManager()->setSaturation(20);
                    $player->getWorld()->addSound($sender->getLocation(), new XpCollectSound(), [$sender]);
                    $sender->sendMessage(Messages::message("§aVous avez nourris le joueur §f" . $player->getName() . '§a !'));
                    $player->sendMessage(Messages::message("§aVous avez été nourris par le joueur §f" . $player->getName() . '§a !'));
                    self::$feed[$sender->getXuid()] = $cooldown;
                }
            } else {
                if (Main::getInstance()->getDataManager()->getXuidByName($args[0]) !== null) {
                    $sender->sendMessage(Messages::message("§cLe joueur n'est pas en ligne."));
                } else  $sender->sendMessage(Messages::message("§cLe joueur n'existe pas."));
            }
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        //$this->addOptionEnum(0, 'joueurs', false, 'name', $this->getAllOnlinePlayers());
        return parent::loadOptions($player);
    }
}