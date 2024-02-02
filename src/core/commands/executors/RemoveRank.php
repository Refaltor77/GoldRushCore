<?php

namespace core\commands\executors;

use core\commands\CommandTrait;
use core\commands\Executor;
use core\events\LogEvent;
use core\Main;
use core\messages\Messages;
use core\traits\UtilsTrait;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class RemoveRank extends Executor
{
    const CONVERSION_RANK = [
        'PLAYER' => 'Joueur',
        'JOURNALISTE' => 'Journaliste',
        'BANDIT' => 'Bandit',
        'BRAQUEUR' => 'Braqueur',
        'COWBOY' => 'Cowboy',
        'MARSHALL' => 'Marshall',
        'SHERIF' => 'Shérif',
        'GUIDE' => 'Guide',
        'MODO' => 'Modérateur',
        'MODO+' => 'Super Modérateur',
        'spm' => 'Super Modérateur',
        'RESPONSABLE' => 'Responsable',
        'ADMINISTRATEUR' => 'Administrateur',
        "BOOST" => "Boost",
        "FARMER" => "Farmer",
        "YOUTUBER" => "Youtubeur"
    ];

    const CONVERSION_WRITE = [
        'joueur' => 'PLAYER',
        'journaliste' => 'JOURNALISTE',
        'bandit' => 'BANDIT',
        'braqueur' => 'BRAQUEUR',
        'cowboy' => 'COWBOY',
        'marshall' => 'MARSHALL',
        'sherif' => 'SHERIF',
        'esprit' => 'ESPRIT',
        'guide' => 'GUIDE',
        'modo' => 'MODO',
        'modo+' => 'MODO+',
        'responsable' => 'RESPONSABLE',
        'admin' => 'ADMINISTRATEUR',
        "boost" => "BOOST",
        "farmer" => "FARMER",
        "youtubeur" => "YOUTUBER"
    ];


    public function __construct(string $name = 'removerank', string $description = "Retirer le grade d'un joueur.", ?string $usageMessage = null, array $aliases = [])
    {
        $this->VisibilityPermission = PlayerPermissions::OPERATOR;
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission('rank.use');
    }


    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::PREFIX . '§cVous devez séléctionner un joueur.');
            return;
        }
        if (!isset($args[1]) || empty($args[1])) {
            $sender->sendMessage(Messages::PREFIX . "§cVous devez séléctionner un grade.");
            return;
        }

        $player = Server::getInstance()->getPlayerByPrefix(strval($args[0]));
        if ($player instanceof Player) {
            if (!in_array($args[1], array_keys(self::CONVERSION_WRITE))) {
                $sender->sendMessage(Messages::PREFIX . "§cLe grade n'existe pas.");
                return;
            }
            Main::getInstance()->getRankManager()->removeRank( $player->getXuid(), self::CONVERSION_WRITE[strval($args[1])]);
            Main::getInstance()->getRankManager()->refreshPerm($player);
            $player->sendMessage(Messages::PREFIX . '§fLe modérateur §6' . $sender->getName() . '§f à modifié vos grades.');
            $sender->sendMessage(Messages::PREFIX . "§fVous avez mis à jour les grades du joueur §6" . $player->getName());
            (new LogEvent($sender->getName()." a retiré un rank ({$args[1]}) à ".$player->getName(),LogEvent::RANK_TYPE))->call();
        } else {
            $xuid = Main::getInstance()->getDataManager()->getXuidByName(strval($args[0]));
            if (!is_null($xuid)) {
                if (!in_array($args[1], array_keys(self::CONVERSION_WRITE))) {
                    $sender->sendMessage(Messages::PREFIX . "§cLe grade n'existe pas.");
                    return;
                }
                Main::getInstance()->getRankManager()->removeRank($xuid, self::CONVERSION_WRITE[$args[1]]);
                $sender->sendMessage(Messages::PREFIX . "§fVous avez mis à jour les grades du joueur §6" . strval($args[0]));
                (new LogEvent($sender->getName()." a retiré un rank ({$args[1]}) à ".$args[0],LogEvent::RANK_TYPE))->call();
            } else {
                $sender->sendMessage(Messages::PREFIX . "§cLe joueur n'existe pas.");
            }
        }
    }

    public function onRunConsoleCommandSender(ConsoleCommandSender $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::PREFIX . '§cVous devez séléctionner un joueur.');
            return;
        }
        if (!isset($args[1])) {
            $sender->sendMessage(Messages::PREFIX . "§cVous devez séléctionner un grade.");
        }

        $ranksValide = [];
        foreach (self::CONVERSION_WRITE as $write => $rankNull) {
            $ranksValide[] = $write;
        }

        $player = Server::getInstance()->getPlayerByPrefix(strval($args[0]));
        if ($player instanceof Player) {
            if (!in_array($args[1], $ranksValide)) {
                $sender->sendMessage(Messages::PREFIX . "§cLe grade n'existe pas.");
                return;
            }
            Main::getInstance()->getRankManager()->removeRank($player->getXuid(), self::CONVERSION_WRITE[strval($args[1])]);
            Main::getInstance()->getRankManager()->refreshPerm($player);
            $player->sendMessage(Messages::PREFIX . '§fLe modérateur §6' . $sender->getName() . '§f à modifié votre grade.');
            $sender->sendMessage(Messages::PREFIX . "§fVous avez mis à jour le grade du joueur §6" . $player->getName());
        } else {
            $xuid = Main::getInstance()->getDataManager()->getXuidByName(strval($args[0]));
            if (!is_null($xuid)) {
                Main::getInstance()->getRankManager()->removeRank($xuid, self::CONVERSION_WRITE[strval($args[1])]);
                $sender->sendMessage(Messages::PREFIX . "§fVous avez mis à jour le grade du joueur §6" . strval($args[0]));
            } else {
                $sender->sendMessage(Messages::PREFIX . "§cLe joueur n'existe pas.");
            }
        }
    }

    use UtilsTrait;

    protected function loadOptions(?Player $player): CommandData
    {

        //$array = array_merge($this->getAllOnlinePlayers(), $this->getAllPlayerInDB());
        $this->addOptionEnum(0, 'Joueurs', false, 'Joueurs', $this->getAllPlayersArrayForArgs());
        $this->addOptionEnum(1, 'Grades', false, 'Grades', [
            'pionnier' ,
            'bandit' ,
            'braqueur' ,
            'cowboy',
            'marshall',
            'sherif',
            'amis' ,
            'moderateur',
            'supermodo',
            'spm',
            'modo' ,
            'developpeur',
            'dev' ,
            'animateur' ,
            'superdeveloppeur' ,
            'responsable' ,
            'guide' ,
            'spdev',
            "administrateur",
            "boost",
            "farmer",
            'sherif',
            'youtubeur'
        ]);

        return parent::loadOptions($player);
    }
}