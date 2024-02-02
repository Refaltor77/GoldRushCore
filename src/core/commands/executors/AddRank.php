<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\events\LogEvent;
use core\Main;
use core\messages\Messages;
use core\traits\UtilsTrait;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class AddRank extends Executor
{
    use UtilsTrait;

    const CONVERT = [
        'pionnier' => 'Pionnier',
        'bandit' => 'Bandit',
        'braqueur' => 'Braqueur',
        'cowboy' => 'Cowboy',
        'marshall' => 'Marshall',
        'sherif' => 'Shérif',
        'amis' => 'Amis',
        'moderateur' => 'Modérateur',
        'supermodo' => 'Super Modérateur',
        'spm' => 'Super Modérateur',
        'modo' => 'Modérateur',
        'developpeur' => 'Développeur',
        'dev' => 'Développeur',
        'animateur' => "Animateur",
        'superdeveloppeur' => 'Super Développeur',
        'responsable' => 'Résponsable',
        'guide' => 'Guide',
        'spdev' => 'Super Développeur',
        "administrateur" => "Administrateur",
        "boost" => "Boost",
        "farmer" => "Farmer",
        "youtubeur" => "Youtubeur"
    ];

    public function __construct(string $name = 'addrank', string $description = "Ajouter un grade à un joueur", ?string $usageMessage = null, array $aliases = [])
    {
        $this->setPermissionMessage(Messages::message("§cVous n'avez pas la permissions !"));
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission('rank.use');
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (isset($args[0])) {
            $target = $args[0];
            if (str_contains('_', $args[0])) $target = str_replace("_", " ", $args[0]);
            $player = $this->getPlugin()->getServer()->getPlayerByPrefix($target);
            if (!is_null($player)) {
                if (isset($args[1])) {
                    $RANK = $this->getPlugin()->getRankManager()->convertStringToRANK(strval(self::CONVERT[$args[1]] ?? '404'));
                    if ($RANK !== '404') {
                        if ($this->getPlugin()->getRankManager()->existGrade($RANK)) {
                            if (!$this->getPlugin()->getRankManager()->hasRank($player->getXuid(), $RANK)) {
                                $player->sendMessage(Messages::message("§eUn modérateur vous a donné le grade §6" . $args[1]));
                                $player->sendNotification("§fUn modérateur vous a donné le grade §6" . $args[1] . " ! §fAmuse toi bien avec tes avantages !");
                                $sender->sendMessage(Messages::message("§eVous avez donné le grade §6" . $args[1] . " §eau joueur §6" . $player->getName()));
                                $this->getPlugin()->getRankManager()->addRank($player->getXuid(), $RANK);
                                Main::getInstance()->getRankManager()->refreshPerm($player);
                                (new LogEvent($sender->getName()." a ajouté un rank ({$args[1]}) à ".$player->getName(),LogEvent::RANK_TYPE))->call();
                            } else $sender->sendMessage(Messages::message("§cLe joueur possède déjà le grade !"));
                        } else  $sender->sendMessage(Messages::message("§cLe grade n'existe pas !"));
                    } else $sender->sendMessage(Messages::message("§cLe grade n'existe pas !"));
                } else $sender->sendMessage(Messages::message("§cVous devez séléctionner un grade !"));
            } else {
                $xuid = $this->getPlugin()->getDataManager()->getXuidByName($target);
                if (!is_null($xuid)) {
                    if (isset($args[1])) {
                        $RANK = $this->getPlugin()->getRankManager()->convertStringToRANK(strval(self::CONVERT[$args[1]] ?? '404'));
                        if ($RANK !== '404') {
                            if ($this->getPlugin()->getRankManager()->existGrade($RANK)) {
                                if (!$this->getPlugin()->getRankManager()->hasRank($xuid, $RANK)) {
                                    $sender->sendMessage(Messages::message("§eVous avez donné le grade §6" . $args[1] . " §eau joueur §6" . $args[0]));
                                    $this->getPlugin()->getRankManager()->addRank($xuid, $RANK);
                                    (new LogEvent($sender->getName()." a ajouté un rank ({$args[1]}) à ".$args[0],LogEvent::RANK_TYPE))->call();
                                } else $sender->sendMessage(Messages::message("§cLe joueur possède déjà le grade !"));
                            } else  $sender->sendMessage(Messages::message("§cLe grade n'existe pas !"));
                        } else $sender->sendMessage(Messages::message("§cLe grade n'existe pas !"));
                    } else $sender->sendMessage(Messages::message("§cVous devez séléctionner un grade !"));
                } else $sender->sendMessage(Messages::message("§cLe joueur n'existe pas !"));
            }
        } else $sender->sendMessage(Messages::message("§cVous devez sélectionner un joueur !"));
    }


    public function onRunConsoleCommandSender(ConsoleCommandSender $sender, string $commandLabel, array $args)
    {
        if (isset($args[0])) {
            $target = $args[0];
            if (str_contains('_', $args[0])) $target = str_replace("_", " ", $args[0]);
            $player = $this->getPlugin()->getServer()->getPlayerByPrefix($target);
            if (!is_null($player)) {
                if (isset($args[1])) {
                    $RANK = $this->getPlugin()->getRankManager()->convertStringToRANK(strval(self::CONVERT[$args[1]] ?? '404'));
                    if ($RANK !== '404') {
                        if ($this->getPlugin()->getRankManager()->existGrade($RANK)) {
                            if (!$this->getPlugin()->getRankManager()->hasRank($player->getXuid(), $RANK)) {
                                $player->sendMessage(Messages::message("§eUn modérateur vous a donné le grade §6" . $args[1]));
                                $sender->sendMessage(Messages::message("§eVous avez donné le grade §6" . $args[1] . " §eau joueur §6" . $player->getName()));
                                $this->getPlugin()->getRankManager()->addRank($player->getXuid(), $RANK);
                                Main::getInstance()->getRankManager()->refreshPerm($player);
                            } else $sender->sendMessage(Messages::message("§cLe joueur possède déjà le grade !"));
                        } else  $sender->sendMessage(Messages::message("§cLe grade n'existe pas !"));
                    } else $sender->sendMessage(Messages::message("§cLe grade n'existe pas !"));
                } else $sender->sendMessage(Messages::message("§cVous devez séléctionner un grade !"));
            } else {
                $xuid = $this->getPlugin()->getDataManager()->getXuidByName($target);
                if (!is_null($xuid)) {
                    if (isset($args[1])) {
                        $RANK = $this->getPlugin()->getRankManager()->convertStringToRANK(strval(self::CONVERT[$args[1]] ?? '404'));
                        if ($RANK !== '404') {
                            if ($this->getPlugin()->getRankManager()->existGrade($RANK)) {
                                if (!$this->getPlugin()->getRankManager()->hasRank($xuid, $RANK)) {
                                    $sender->sendMessage(Messages::message("§eVous avez donné le grade §6" . $args[1] . " §eau joueur §6" . $args[0]));
                                    $this->getPlugin()->getRankManager()->addRank($xuid, $RANK);
                                } else $sender->sendMessage(Messages::message("§cLe joueur possède déjà le grade !"));
                            } else  $sender->sendMessage(Messages::message("§cLe grade n'existe pas !"));
                        } else $sender->sendMessage(Messages::message("§cLe grade n'existe pas !"));
                    } else $sender->sendMessage(Messages::message("§cVous devez séléctionner un grade !"));
                } else $sender->sendMessage(Messages::message("§cLe joueur n'existe pas !"));
            }
        } else $sender->sendMessage(Messages::message("§cVous devez sélectionner un joueur !"));
    }

    public function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'Liste des joueurs', true, 'Joueurs', $this->getAllPlayersArrayForArgs());
        $this->addOptionEnum(1, "Liste des grades", true, 'grades', [
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