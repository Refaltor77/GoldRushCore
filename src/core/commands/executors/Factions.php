<?php

namespace core\commands\executors;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Button;
use core\api\form\elements\Dropdown;
use core\api\form\elements\Image;
use core\api\form\elements\Input;
use core\api\form\elements\Label;
use core\api\form\MenuForm;
use core\api\form\ModalForm;
use core\api\webhook\Message;
use core\async\RequestAsync;
use core\commands\Executor;
use core\events\LogEvent;
use core\forms\FactionForms;
use core\forms\TopForms;
use core\Main;
use core\managers\factions\FactionManager;
use core\managers\factions\FactionManager as Faction;
use core\managers\factions\FactionVisibility;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\sql\SQL;
use core\tasks\Teleport;
use core\traits\FactionTrait;
use core\traits\SoundTrait;
use core\traits\UtilsTrait;
use core\utils\Utils;
use DateTime;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Factions extends Executor
{
    use UtilsTrait;
    use SoundTrait;
    use FactionTrait;

    public function __construct(string $name = 'f', string $description = "Commandes de factions", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function stripFactionName(string $name): string
    {
        $name = strtolower($name);
        return $name;
    }

    public function onRun(CommandSender $sender, string $commandLabel, array $args)
    {


        if (!$sender instanceof Player) {
            $sender->sendMessage(Messages::message("§cCommande exécutable uniquement sur le serveur."));
            return;
        }


        $xuid = $sender->getXuid();
        if (isset($args[0])) {
            switch (strtolower(strval($args[0]))) {
                case 'admin':
                    if (!$sender->hasPermission('faction.use') && !$sender->isOp()) {
                        $sender->sendMessage(Messages::message($this->getPermissionMessage()));
                        return;
                    }

                    if (!isset($args[1])) {
                        $sender->sendMessage(Messages::message("§c/f admin <faction>"));
                        return;
                    }

                    $facName = $args[1];
                    if (!$this->getManager()->existFaction($facName)) {
                        $sender->sendMessage(Messages::message("§cLa faction §4" . $facName ." §cn'existe pas !"));
                        return;
                    }

                    $sender->sendForm(new MenuForm("§c- §fFACTION: §c" . $facName . " §c-", "Un formulaire pour gérer une faction, quoi demandé de plus a refaltor a 23:01 entrain de piqué du nez ? Allez modére bien mon kéké", [
                        new Button("Gérer le home de faction"),
                        new Button("§ajouter de l'argent"),
                        new Button("§cRetirer de l'argent"),
                        new Button("§4- FORCE DELETE /!\ -"),
                    ], function (Player $player, Button $button) use ($facName) : void {

                        if (!$this->getManager()->existFaction($facName)) {
                            $player->sendErrorSound();
                            $player->sendMessage(Messages::message("§cLa faction n'existe plus.."));
                            return;
                        }

                        switch ($button->getValue()) {
                            case 0:
                                if ($this->getManager()->existFaction($facName)) {
                                    if ($this->getManager()->hasHome($facName)) {
                                        $player->sendForm(new MenuForm("§c- §fHOME DE LA FACTION §c" . $facName . " §c-", "", [
                                            new Button("§6Se téléporter"),
                                            new Button("§cSupprimer")
                                        ], function (Player $player, Button $button) use ($facName): void {
                                            if ($this->getManager()->existFaction($facName) && $this->getManager()->hasHome($facName)) {
                                                switch ($button->getValue()) {
                                                    case 0:
                                                        $home = $this->getManager()->getHome($facName);
                                                        $player->teleport($home);
                                                        $player->sendSuccessSound();
                                                        $player->sendMessage(Messages::message("§fTéléportation vers le home de la faction §6" . $facName));
                                                        break;
                                                    case 1:
                                                        $this->getManager()->delhome($facName);
                                                        $player->sendSuccessSound();
                                                        $player->sendMessage(Messages::message("§fTu vient de supprimer le home de la faction §6" . $facName));
                                                        break;
                                                }
                                            }
                                        }));
                                    } else $player->sendMessage(Messages::message("§cLa faction ne possède pas de home de faction."));
                                }
                                break;
                            case 1:
                                $player->sendForm(new CustomForm("§c- §fAjouter de l'argent a §4" . $facName . " §c-", [
                                    new Label("Oh ! Un formulaire trop génial pour ajouter de l'argent à une faction !"),
                                    new Input("Montant", "150")
                                ], function (Player $player, CustomFormResponse $response) use ($facName) : void {
                                    $value = $response->getInput()->getValue();
                                    if (!(int)$value) {
                                        $player->sendErrorSound();
                                        $player->sendMessage(Messages::message("§cTu doit renseigner un chiffre."));
                                        return;
                                    }

                                    if ($value > PHP_INT_MAX) {
                                        $player->sendErrorSound();
                                        $player->sendMessage(Messages::message("§cWTF THEO STOP PTN DE MERDE AVEC T CHIFFRE TROP GROS TAS MERE LA....... sale belge"));
                                        return;
                                    }

                                    if (!$this->getManager()->existFaction($facName)) {
                                        $player->sendErrorSound();
                                        $player->sendMessage(Messages::message("§cLa faction n'existe plus.."));
                                        return;
                                    }

                                    $this->getManager()->addMoneyFactionBank($facName, $value);
                                    $player->sendSuccessSound();
                                    $player->sendMessage(Messages::message("§fTu vient d'ajouter §6" . $value . "$ §fà la faction §6" . $facName));
                                }));
                                break;
                            case 2:
                                $player->sendForm(new CustomForm("§c- §fRetirer de l'argent a §4" . $facName . " §c-", [
                                    new Label("Oh ! Un formulaire trop génial pour retirer de l'argent à une faction !"),
                                    new Input("Montant", "150")
                                ], function (Player $player, CustomFormResponse $response) use ($facName) : void {
                                    $value = $response->getInput()->getValue();
                                    if (!(int)$value) {
                                        $player->sendErrorSound();
                                        $player->sendMessage(Messages::message("§cTu doit renseigner un chiffre."));
                                        return;
                                    }

                                    if ($value > PHP_INT_MAX) {
                                        $player->sendErrorSound();
                                        $player->sendMessage(Messages::message("§cWTF THEO STOP PTN DE MERDE AVEC T CHIFFRE TROP GROS TAS MERE LA....... sale belge"));
                                        return;
                                    }

                                    if (!$this->getManager()->existFaction($facName)) {
                                        $player->sendErrorSound();
                                        $player->sendMessage(Messages::message("§cLa faction n'existe plus.."));
                                        return;
                                    }

                                    $this->getManager()->removeMoneyFactionAll($facName, $value);
                                    $player->sendSuccessSound();
                                    $player->sendMessage(Messages::message("§fTu vient de retirer §6" . $value . "$ §fà la faction §6" . $facName));
                                }));
                                break;
                            case 3:
                                $player->sendForm(new ModalForm("§c- FORCE DELETE FACTION §4" . $facName . "§c -",
                                "§cATTENTION ! Tu est entrain de faire une action sensible, soit sur de toi avant de réaliser cette action",
                                    function (Player $player, bool $accept) use ($facName): void {
                                        if (!$this->getManager()->existFaction($facName)) {
                                            $player->sendErrorSound();
                                            $player->sendMessage(Messages::message("§cLa faction n'existe plus.."));
                                            return;
                                        }

                                        if ($accept) {
                                            $this->getManager()->deleteFaction($facName);
                                            $player->sendSuccessSound();
                                            $player->sendMessage(Messages::message("§fTu vient de supprimer la faction §6" . $facName . "§f de §force"));
                                        }
                                }));
                                break;
                        }
                    }));
                    break;
                case 'adminkick':
                    if (!$sender->hasPermission('faction.use')) {
                        $sender->sendMessage(Messages::message($this->getPermissionMessage()));
                        return;
                    }

                    if (!isset($args[1])) {
                        $sender->sendMessage(Messages::message("§c/f adminkick <faction> <playerName>"));
                        return;
                    }

                    $facName = $args[1];
                    if (!$this->getManager()->existFaction($facName)) {
                        $sender->sendMessage(Messages::message("§cLa faction §4" . $facName ." §cn'existe pas !"));
                        return;
                    }

                    $playerName = $args[2];
                    $xuid = Main::getInstance()->getDataManager()->getXuidByName($playerName);


                    $player = Server::getInstance()->getPlayerByPrefix($playerName);
                    if ($player instanceof CustomPlayer) {
                        if ($this->getManager()->isInFaction($player->getXuid())) {
                            if ($this->getManager()->getFactionName($player->getXuid()) === $facName) {
                                $this->getManager()->removeMember($player->getXuid(), $facName);
                                $player->sendNotification("§fUn modérateur vous a exclu de la faction §6" . $facName);


                                $player->sendErrorSound();

                                $sender->sendSuccessSound();
                                $sender->sendMessage(Messages::message("§fVous avez exclu le joueur §6" . $player->getName() . "§f de la faction §6" . $facName));
                            } else {
                                $sender->sendMessage(Messages::message("§cLe joueur n'est pas dans la faction"));
                                $sender->sendErrorSound();
                            }
                        } else {
                            $sender->sendMessage(Messages::message("§cLe joueur n'est pas dans une faction"));
                            $sender->sendErrorSound();
                        }
                    }

                    if ($xuid === null) {
                        $sender->sendErrorSound();
                        $sender->sendMessage(Messages::message("§cLe joueur n'existe pas, veuillez verifier le pseudo ou taper en entier le pseudo du joueur."));
                        return;
                    } else {
                        if ($this->getManager()->isInFaction($xuid)) {
                            if ($this->getManager()->getFactionName($xuid) === $facName) {
                                $sender->sendSuccessSound();
                                $sender->sendMessage(Messages::message("§fVous avez exclu le joueur §6" . $playerName . "§f de la faction §6" . $facName));
                            } else {
                                $sender->sendMessage(Messages::message("§cLe joueur n'est pas dans la faction"));
                                $sender->sendErrorSound();
                            }
                        } else {
                            $sender->sendMessage(Messages::message("§cLe joueur n'est pas dans une faction"));
                            $sender->sendErrorSound();
                        }
                    }

                    break;
                case 'quests':
                case 'quetes':
                case 'quest':
                    FactionForms::sendQuestUi($sender);
                    break;
                case "top":
                    TopForms::sendTopFac($sender);
                    break;
                case 'addpower':
                    if (!$sender->hasPermission('faction.use')) {
                        $sender->sendMessage(Messages::message($this->getPermissionMessage()));
                        return;
                    }

                    if (!isset($args[1])) {
                        $sender->sendMessage(Messages::message("§c/f addpower <player> <power>"));
                        return;
                    }

                    if (!isset($args[2])) {
                        $sender->sendMessage(Messages::message("§c/f addpower <player> <power>"));
                        return;
                    }

                    if (!(int)$args[2]) {
                        $sender->sendMessage(Messages::message("§cLe power doit être un chiffre"));
                        return;
                    }

                    $player = Server::getInstance()->getPlayerByPrefix($args[1]);
                    if ($player instanceof Player) {
                        $this->getManager()->addPower("", intval($args[2]), $player);
                        $sender->sendMessage(Messages::message("§fVous avez ajouté §6" . $args[2] . "§f powers au joueur §6" . $args[1]));
                    } else $sender->sendMessage(Messages::message("§cLe joueur n'est pas en ligne."));
                    break;
                case 'removepower':
                    if (!$sender->hasPermission('faction.use')) {
                        $sender->sendMessage(Messages::message($this->getPermissionMessage()));
                        return;
                    }

                    if (!isset($args[1])) {
                        $sender->sendMessage(Messages::message("§c/f removepower <player> <power>"));
                        return;
                    }

                    if (!isset($args[2])) {
                        $sender->sendMessage(Messages::message("§c/f removepower <player> <power>"));
                        return;
                    }

                    if (!(int)$args[2]) {
                        $sender->sendMessage(Messages::message("§cLe power doit être un chiffre"));
                        return;
                    }

                    $player = Server::getInstance()->getPlayerByPrefix($args[1]);
                    if ($player instanceof Player) {
                        $this->getManager()->reducePower("", intval($args[2]), $player);
                        $sender->sendMessage(Messages::message("§fVous avez retiré §6" . $args[2] . "§f powers au joueur §6" . $args[1]));
                    } else $sender->sendMessage(Messages::message("§cLe joueur n'est pas en ligne."));
                    break;
                case 'addmoney':
                    if (!$sender->hasPermission('faction.use')) {
                        $sender->sendMessage(Messages::message($this->getPermissionMessage()));
                        return;
                    }

                    if (!isset($args[1])) {
                        $sender->sendMessage(Messages::message("§c/f addmoney <faction> <money>"));
                        return;
                    }

                    if (!isset($args[2])) {
                        $sender->sendMessage(Messages::message("§c/f addmoney <faction> <money>"));
                        return;
                    }

                    if (!(int)$args[2]) {
                        $sender->sendMessage(Messages::message("§cL'argent doit être un chiffre"));
                        return;
                    }

                    $facName = $args[1];
                    if ($this->getManager()->existFaction($facName)) {
                        $this->getManager()->addMoneyFactionBank( $facName, intval($args[2]));
                        $sender->sendMessage(Messages::message("§fVous avez ajouté §6" . $args[2] . "§6$ §fa la faction §6" . $args[1]));

                    } else {
                        $sender->sendMessage(Messages::message("§cLa faction §4" . $facName . " §cn'existe pas !"));
                        $sender->sendErrorSound();
                    }
                    break;
                case 'removemoney':
                    if (!$sender->hasPermission('faction.use')) {
                        $sender->sendMessage(Messages::message($this->getPermissionMessage()));
                        return;
                    }

                    if (!isset($args[1])) {
                        $sender->sendMessage(Messages::message("§c/f removemoney <faction> <money>"));
                        return;
                    }

                    if (!isset($args[2])) {
                        $sender->sendMessage(Messages::message("§c/f removemoney <faction> <money>"));
                        return;
                    }

                    if (!(int)$args[2]) {
                        $sender->sendMessage(Messages::message("§cL'argent doit être un chiffre"));
                        return;
                    }

                    $facName = $args[1];
                    if ($this->getManager()->existFaction($facName)) {
                        $this->getManager()->removeMoneyFactionAll( $facName, intval($args[2]));
                        $sender->sendMessage(Messages::message("§fVous avez retiré §6" . $args[2] . "§6$ §fa la faction §6" . $args[1]));

                    } else {
                        $sender->sendMessage(Messages::message("§cLa faction §4" . $facName . " §cn'existe pas !"));
                        $sender->sendErrorSound();
                    }
                    break;
                case 'forcedelete':
                    if (!$sender->hasPermission('faction.use')) {
                        $sender->sendMessage(Messages::message($this->getPermissionMessage()));
                        return;
                    }

                    if (!isset($args[1])) {
                        $sender->sendMessage(Messages::message("§c/f forcedelete <faction_name>"));
                        return;
                    }

                    if (!$this->getManager()->existFaction($args[1])) {
                        $sender->sendMessage(Messages::message("§cLa faction n'existe pas."));
                        return;
                    }

                    $this->getManager()->deleteFaction($args[1]);
                    break;
                case 'create':
                    if (!$this->getManager()->isInFaction($sender->getXuid())) {
                        FactionForms::sendFormFactionCreate($sender);
                    } else $sender->sendMessage(Messages::message("§cVous êtes déjà dans une faction !"));
                    break;
                case 'delete':
                    if ($this->getManager()->isInFaction($xuid)) {
                        if ($this->getManager()->getXuidOwnerFaction($name = $this->getManager()->getFactionName($xuid)) === $xuid) {
                            $sender->sendForm(new ModalForm(
                                "§c/§4!§c\\ §cSuppression de votre faction §c/§4!§c\\",
                                '§cÊtes-vous sur de vouloir supprimer votre faction ? Cette action est irréversible.',
                                function (Player $sender, bool $bool) use ($name): void {
                                    if ($bool) {
                                        $sender->sendNotification("Votre faction est désormais §csupprimée !");
                                        $this->getManager()->deleteFaction($name);
                                    } else $sender->sendMessage(Messages::message("§fLa suppression de votre faction a été §6annulée."));
                                }));
                        } else $sender->sendMessage(Messages::message("§cVous devez être le chef de la faction pour pouvoir la supprimer !"));
                    } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    break;
                case 'bank':
                    if ($this->getManager()->isInFaction($xuid)) {
                        FactionForms::sendBankFaction($sender);
                    } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    break;
                case 'money':
                    if ($this->getManager()->isInFaction($xuid)) {
                        FactionForms::sendBankFaction($sender);
                    } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    break;
                case 'promote':
                    if ($this->getManager()->isInFaction($xuid)) {
                        if ($this->getManager()->getXuidOwnerFaction($name = $this->getManager()->getFactionName($xuid)) === $sender->getXuid() || $this->getManager()->getRankMember($xuid, $name) === Faction::OFFICIER) {
                            if (isset($args[1])) {
                                $player = $this->getPlugin()->getServer()->getPlayerByPrefix(strval($args[1]));
                                if (!is_null($player)) {
                                    if ($this->getManager()->isInFaction($player->getXuid())) {
                                        if ($this->getManager()->getFactionName($player->getXuid()) === $name) {
                                            $rank = $this->getManager()->getRankMember($player->getXuid(), $name);
                                            switch ($rank) {
                                                case Faction::RECRUE:
                                                    $this->getManager()->setRankMember($player->getXuid(), Faction::MEMBER);
                                                    $player->sendMessage(Messages::message("§fVous avez été promu §6membre !"));
                                                    $player->sendNotification("Le chef de votre faction vous à promu §6membre");
                                                    $sender->sendMessage(Messages::message("§fVous avez promu §6{$player->getName()}§f au grade §6membre !"));
                                                    break;
                                                case Faction::MEMBER:
                                                    if ($this->getManager()->getRankMember($xuid, $name) !== Faction::OFFICIER) {
                                                        if (!$this->getManager()->getXuidOwnerFaction($name) === $xuid) {
                                                            $sender->sendMessage(Messages::message("§cVous devez être chef de votre clan pour pouvoir promouvoir un membre officier !"));
                                                            return;
                                                        }
                                                    }
                                                    $this->getManager()->setRankMember($player->getXuid(), Faction::OFFICIER);
                                                    $player->sendNotification("Le chef de votre faction vous à promu §6officier");
                                                    $sender->sendMessage(Messages::message("§fVous avez promu §6{$player->getName()}§f au grade §6officier !"));
                                                    break;
                                                case Faction::OFFICIER:
                                                    $sender->sendMessage(Messages::message("§cVous ne pouvez pas promouvoir un officier."));
                                                    break;
                                            }
                                        } else $sender->sendMessage(Messages::message("§cLe joueur n'est pas dans votre faction !"));
                                    } else $sender->sendMessage(Messages::message("§cLe joueur n'est pas dans votre faction !"));
                                } else {
                                    $xuidP = $this->getPlugin()->getDataManager()->getXuidByName(strval($args[1]));
                                    if (!is_null($xuidP)) {
                                        if ($this->getManager()->isInFaction($xuidP)) {
                                            if ($this->getManager()->getFactionName($xuidP) === $name) {
                                                $rank = $this->getManager()->getRankMember($xuidP, $name);
                                                switch ($rank) {
                                                    case Faction::RECRUE:
                                                        $this->getManager()->setRankMember($xuidP, Faction::MEMBER);
                                                        $sender->sendMessage(Messages::message("§fVous avez promu §6{$args[1]}§f au grade §6membre !"));
                                                        break;
                                                    case Faction::MEMBER:
                                                        if ($this->getManager()->getRankMember($xuid, $name) !== Faction::OFFICIER) {
                                                            if (!$this->getManager()->getXuidOwnerFaction($name) === $xuid) {
                                                                $sender->sendMessage(Messages::message("§cVous devez être chef de votre clan pour pouvoir promouvoir un membre officier !"));
                                                                return;
                                                            }
                                                        }
                                                        $this->getManager()->setRankMember($xuidP, Faction::OFFICIER);
                                                        $sender->sendMessage(Messages::message("§fVous avez promu §6{$args[1]}§f au grade §6officier !"));
                                                        break;
                                                }
                                            } else $sender->sendMessage(Messages::message("§cLe joueur n'est pas dans votre faction !"));
                                        } else $sender->sendMessage(Messages::message("§cLe joueur n'est pas dans votre faction !"));
                                    }
                                }
                            } else $sender->sendMessage(Messages::message("§cVous devez sélectionner un joueur pour cette commande !"));
                        } else $sender->sendMessage(Messages::message("§cVous devez être chef de la faction pour faire cette action !"));
                    } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    break;
                case 'invite':
                    if ($this->getManager()->isInFaction($xuid)) {
                        if ($this->getManager()->getXuidOwnerFaction($name = $this->getManager()->getFactionName($xuid)) === $sender->getXuid() || $this->getManager()->getRankMember($sender->getXuid(), $name) === FactionManager::OFFICIER) {
                            if (isset($args[1])) {
                                $player = $this->getPlugin()->getServer()->getPlayerByPrefix(strval($args[1]));
                                if (!is_null($player)) {
                                    if ($player->getXuid() !== $sender->getXuid()) {
                                        if (!$this->getManager()->isInFaction($player->getXuid())) {
                                            if ($this->getManager()->hasInvited($player)) {
                                                if (!$this->getManager()->isTimeoutInvited($player)) {
                                                    if ($this->getManager()->getFactionInvited($player) === $name) {
                                                        $sender->sendMessage(Messages::message("§cVous avez déjà invité ce joueur dans votre faction, attendez sa réponse !"));
                                                    } else {
                                                        $sender->sendMessage(Messages::message("§cLe joueur est déjà invité par une autre faction !"));
                                                    }
                                                    return;
                                                }
                                            }
                                            $this->getManager()->invitePlayerInFaction($player, $name, $xuid);
                                            $sender->sendMessage(Messages::message("§fInvitation envoyée au joueur §6{$player->getName()}§f !"));
                                            $player->sendNotification("§fVous avez reçu une invitation pour rejoindre la faction §6{$this->getManager()->getFactionInvited($player)}§f !");
                                        } else $sender->sendMessage(Messages::message("§cLe joueur est déjà dans une faction !"));
                                    } else $sender->sendMessage(Messages::message("§cVous ne pouvez pas vous inviter vous-même !"));
                                } else $sender->sendMessage(Messages::message("§cLe joueur n'est pas en ligne !"));
                            } else $sender->sendMessage(Messages::message("§cVous devez sélectionner un joueur pour cette commande !"));
                        } else $sender->sendMessage(Messages::message("§cVous devez être chef/officier de la faction pour faire cette action !"));
                    } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    break;
                case 'accept':
                    if (!$this->getManager()->isInFaction($xuid)) {
                        if ($this->getManager()->hasInvited($sender)) {
                            if (!$this->getManager()->isTimeoutInvited($sender)) {
                                $faction = $this->getManager()->getFactionInvited($sender);
                                foreach ($this->getManager()->getMembersFaction($faction) as $xuid => $rank) {
                                    $player = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
                                    if (!is_null($player)) {
                                        $player->sendNotification("§aLe joueur §f{$sender->getName()}§a à rejoint la faction !");
                                    }
                                }
                                (new LogEvent($sender->getName()." a rejoint la faction ".$faction, LogEvent::FACTION_TYPE))->call();
                                $xuidOwner = $this->getManager()->getXuidLeaderInvite($sender);
                                $ownerInvite = $this->getPlugin()->getDataManager()->getPlayerXuid($xuidOwner);
                                if (!is_null($ownerInvite)) {
                                    $ownerInvite->sendMessage(Messages::message("§aLe joueur §f{$sender->getName()}§a a accepté votre invitation !"));
                                }
                                $this->getManager()->acceptInvitation($sender);
                                $sender->sendNotification("§fTu fais désormais partie de la faction §6" . $faction . " !");
                                $sender->sendMessage(Messages::message("§aVous avez rejoint la faction §f{$faction} §a!"));
                            } else $sender->sendMessage("§cVous n'avez aucune invitation !");
                        } else $sender->sendMessage(Messages::message("§cVous n'avez aucune invitation !"));
                    } else $sender->sendMessage(Messages::message("§cVous êtes déjà dans une faction !"));
                    break;
                case 'deny':
                    if (!$this->getManager()->isInFaction($xuid)) {
                        if ($this->getManager()->hasInvited($sender)) {
                            if (!$this->getManager()->isTimeoutInvited($sender)) {
                                $faction = $this->getManager()->getFactionInvited($sender);
                                $xuidOwner = $this->getManager()->getXuidOwnerFaction($faction);
                                $this->getManager()->denyInvitation($sender);
                                $sender->sendNotification("§fVous avez refusé l'invitation de la faction §6{$faction}§f !");
                                $target = $this->getPlugin()->getDataManager()->getPlayerXuid($xuidOwner);
                                if (!is_null($target)) {
                                    $target->sendNotification("§fLe joueur §6{$sender->getName()}§f a §crefusé §fvotre invitation");
                                }
                            } else $sender->sendMessage("§cVous n'avez aucune invitation !");
                        } else $sender->sendMessage(Messages::message("§cVous n'avez aucune invitation !"));
                    } else $sender->sendMessage(Messages::message("§cVous êtes déjà dans une faction !"));
                    break;
                case 'leave':
                    if ($this->getManager()->isInFaction($xuid)) {
                        if ($this->getManager()->getXuidOwnerFaction($name = $this->getManager()->getFactionName($xuid)) !== $sender->getXuid()) {
                            $this->getManager()->removeMember($xuid, $name);
                            $allMembers = $this->getManager()->getMembersFaction($name);
                            foreach ($allMembers as $xuid => $rank) {
                                $player = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
                                if (!is_null($player)) {
                                    $player->sendNotification("§cLe joueur §f{$sender->getName()}§c à quitté la faction !");
                                }
                            }
                            (new LogEvent($sender->getName()." a quitté la faction ".$name, LogEvent::FACTION_TYPE))->call();
                            $xuidOwner = $this->getManager()->getXuidOwnerFaction($name);
                            $target = $this->getPlugin()->getDataManager()->getPlayerXuid($xuidOwner);
                            if (!is_null($target)) {
                                $target->sendNotification("§cLe joueur §f{$sender->getName()}§c à quitté la faction !");
                            }
                            $sender->sendNotification("§fVous venez de quitter la faction §6{$name}§f !");
                        } else $sender->sendMessage(Messages::message("§cVous devez transférer le rôle de chef à un membre ou supprimer la faction pour pouvoir la quitter."));
                    } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    break;
                case 'claim':
                    if ($this->getManager()->isInFaction($xuid)) {
                        if ($this->getManager()->getXuidOwnerFaction($name = $this->getManager()->getFactionName($xuid)) === $sender->getXuid() || $this->getManager()->getRankMember($xuid, $name) === Faction::OFFICIER) {
                            if ($this->getManager()->getMoneyFaction($name) >= 150000) {
                                if (!$this->getManager()->isInClaim($sender->getPosition())) {
                                    if (!$this->getPlugin()->getAreaManager()->isInArea($sender->getPosition())) {
                                        if ($this->getPlugin()->getFactionManager()->getClaimCount($name) >= 10) {
                                            $sender->sendMessage(Messages::message("§cVous avez atteint le nombre maximum de claim !"));
                                            return;
                                        }
                                        $price = ((int)$this->getPlugin()->getFactionManager()->getClaimCount($name) === 0 ?? 1) * 150000;
                                        $sender->sendForm(new ModalForm("§cÊtes vous sur ?", "§cVous êtes sur le point de claim un chunk pour $price$ qui sera déduit de votre faction", function (Player $player, bool $success) use ($name) : void {

                                            $price = ((int)$this->getPlugin()->getFactionManager()->getClaimCount($name) === 0 ?? 1) * 150000;
                                            if (!$this->getManager()->getMoneyFaction($name) >= $price) {
                                                $player->sendMessage(Messages::message("§cVous n'avez pas assez de money dans votre faction ! Prix : " . $price . "$"));
                                                return;
                                            }

                                            if ($this->getPlugin()->getFactionManager()->getClaimCount($name) >= 10) {
                                                $player->sendMessage(Messages::message("§cVous avez atteint le nombre maximum de claim !"));
                                                return;
                                            }


                                            if ($this->getManager()->isInClaim($player->getPosition())) {
                                                $player->sendMessage(Messages::message("§cVous ne pouvez pas claim dans une zone protégée."));
                                                return;
                                            }

                                            $this->getManager()->claimChunk($name, $player);
                                            $this->getManager()->removeMoneyFactionAll($name, $price);
                                            $player->sendMessage(Messages::message("§aVous avez claim ce chunk !"));
                                        }));






                                    } else $sender->sendMessage(Messages::message("§cVous ne pouvez pas claim dans une zone protégée."));
                                } else {
                                    if ($this->getManager()->getFactionNameInClaim($sender->getPosition()) === $name) {
                                        $sender->sendMessage(Messages::message("§cCe chunk est déjà claim par votre faction !"));
                                    } else $sender->sendMessage(Messages::message("§cCe chunk est déjà claim par une autre faction !"));
                                }
                            } else $sender->sendMessage(Messages::message("§cVous n'avez pas assez de money dans votre faction !"));
                        } else $sender->sendMessage(Messages::message("§cVous devez être chef de la faction ou officier pour faire cette action !"));
                    } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    break;
                case 'unclaim':
                    if ($this->getManager()->isInFaction($xuid)) {
                        if ($this->getManager()->getXuidOwnerFaction($name = $this->getManager()->getFactionName($xuid)) === $sender->getXuid() || $this->getManager()->getRankMember($xuid, $name) === Faction::OFFICIER) {
                            if ($this->getManager()->isInClaim($sender->getPosition())) {
                                if ($this->getManager()->getFactionNameInClaim($sender->getPosition()) === $name) {
                                    $this->getManager()->unclaimChunk($name, $sender);
                                    $sender->sendMessage(Messages::message("§aVous avez unclaim ce chunk !"));
                                } else $sender->sendMessage(Messages::message("§cVous ne pouvez pas unclaim un claim ennemi !"));
                            } else $sender->sendMessage(Messages::message("§cVous devez être dans votre claim pour pouvoir l'unclaim !"));
                        } else $sender->sendMessage(Messages::message("§cVous devez être chef de la faction ou officier pour faire cette action !"));
                    } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    break;
                case 'kick':
                    if ($this->getManager()->isInFaction($xuid)) {
                        if ($this->getManager()->getXuidOwnerFaction($name = $this->getManager()->getFactionName($xuid)) === $sender->getXuid() || $this->getManager()->getRankMember($xuid, $name) === Faction::OFFICIER) {
                            if (isset($args[1])) {
                                $xuid = $this->getPlugin()->getDataManager()->getXuidByName(strval($args[1]));
                                if (!is_null($xuid)) {
                                    $rank = $this->getManager()->getRankMember($xuid, $name);
                                    switch ($rank) {
                                        case Faction::RECRUE:
                                            $this->getManager()->removeMember($xuid, $name);
                                            $allMembers = $this->getManager()->getMembersFaction($name);
                                            foreach ($allMembers as $xuid => $rank) {
                                                $player = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
                                                if (!is_null($player)) {
                                                    $player->sendNotification("§cLe joueur §f{$args[1]}§c a été kick de la faction par §f{$sender->getName()}!");
                                                }
                                            }
                                            $target = $this->getPlugin()->getDataManager()->getPlayerXuid($this->getManager()->getXuidOwnerFaction($name));
                                            if (!is_null($target)) {
                                                $target->sendNotification("§cLe joueur §f{$args[1]}§c a été kick de la faction par §f{$sender->getName()}!");
                                            }
                                            $sender->sendMessage(Messages::message("§fVous avez §cviré §fle joueur §6{$args[1]}"));
                                            break;
                                        case Faction::MEMBER:
                                            if ($this->getManager()->getRankMember($sender->getXuid(), ($name = $this->getManager()->getFactionName($sender->getXuid()))) === Faction::OFFICIER ||
                                                $this->getManager()->getXuidOwnerFaction($name) === $sender->getXuid()
                                            ) {
                                                $this->getManager()->removeMember($xuid, $name);
                                                $allMembers = $this->getManager()->getMembersFaction($name);
                                                foreach ($allMembers as $xuid => $rank) {
                                                    $player = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
                                                    if (!is_null($player)) {
                                                        $player->sendNotification("§cLe joueur §f{$args[1]}§c a été kick de la faction par §f{$sender->getName()}!");
                                                    }
                                                }
                                                $target = $this->getPlugin()->getDataManager()->getPlayerXuid($this->getManager()->getXuidOwnerFaction($name));
                                                if (!is_null($target)) {
                                                    $target->sendNotification("§cLe joueur §f{$args[1]}§c a été kick de la faction par §f{$sender->getName()}!");
                                                }
                                                $sender->sendMessage(Messages::message("§fVous avez §cviré §fle joueur §6{$args[1]}"));
                                            } else $sender->sendMessage(Messages::message("§cVous devez être officier pour virer ce joueur !"));
                                            break;
                                        case Faction::OFFICIER:
                                            if ($this->getManager()->getXuidOwnerFaction(($name = $this->getManager()->getFactionName($sender->getXuid()))) === $sender->getXuid()) {
                                                $this->getManager()->removeMember($xuid, $name);
                                                $allMembers = $this->getManager()->getMembersFaction($name);
                                                foreach ($allMembers as $xuid => $rank) {
                                                    $player = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
                                                    if (!is_null($player)) {
                                                        $player->sendNotification("§cLe joueur §f{$args[1]}§c a été kick de la faction par §f{$sender->getName()}!");
                                                    }
                                                }
                                                $sender->sendMessage(Messages::message("§fVous avez §cviré §fle joueur §6{$args[1]}"));
                                            } else $sender->sendMessage(Messages::message("§cVous devez être chef de la faction pour virer ce joueur !"));
                                            break;
                                    }
                                } else $sender->sendMessage(Messages::message("§cLe joueur ne fait pas partie de votre faction !"));
                            } else $sender->sendMessage(Messages::message("§cVous devez sélectionner un joueur pour cette commande !"));
                        } else $sender->sendMessage(Messages::message("§cVous devez être chef de la faction ou officier pour faire cette action !"));
                    } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    break;
                case 'sethome':
                    if ($this->getManager()->isInFaction($xuid)) {
                        if ($this->getManager()->getXuidOwnerFaction($name = $this->getManager()->getFactionName($xuid)) === $sender->getXuid()) {
                            if (!$this->getPlugin()->getAreaManager()->isInArea($sender->getPosition())) {
                                if ($this->getManager()->hasHome($name)) {
                                    $this->getManager()->setHome($name, $sender->getPosition());
                                    $sender->sendMessage(Messages::message("§fVous avez §6redéfini§f votre home de faction !"));
                                } else {
                                    $this->getManager()->setHome($name, $sender->getPosition());
                                    $sender->sendMessage(Messages::message("§fVous avez crée votre §6home §fde faction !"));
                                }
                            } else $sender->sendMessage(Messages::message("§cVous ne pouvez pas créer votre home de faction dans une zone protéger."));
                        } else $sender->sendMessage(Messages::message("§cVous devez être chef de la faction pour faire cette action !"));
                    } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    break;
                case 'delhome':
                    if ($this->getManager()->isInFaction($xuid)) {
                        if ($this->getManager()->getXuidOwnerFaction($name = $this->getManager()->getFactionName($xuid)) === $sender->getXuid()) {
                            if ($this->getManager()->hasHome($name)) {
                                $this->getManager()->delhome($name);
                                $sender->sendMessage(Messages::message("§fVous avez §csupprimé§f votre home de faction !"));
                            } else $sender->sendMessage(Messages::message("§cVous n'avez aucun home de faction !"));
                        } else $sender->sendMessage(Messages::message("§cVous devez être chef de la faction pour faire cette action !"));
                    } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    break;
                case 'home':
                    if ($this->getManager()->isInFaction($xuid)) {
                        if ($this->getManager()->hasHome($name = $this->getManager()->getFactionName($xuid))) {
                            if ($this->getManager()->getRankMember($xuid, $name) !== Faction::RECRUE) {
                                $this->getPlugin()->getScheduler()->scheduleRepeatingTask(new Teleport($sender, $this->getManager()->getHome($name)), 20);
                            } else $sender->sendMessage(Messages::message("§cLes recrues n'ont pas l'accès au home de faction, demandez une promotion a un officier !"));
                        } else $sender->sendMessage(Messages::message("§cVotre faction ne possède aucun home."));
                    } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    break;
                case 'demote':
                    if ($this->getManager()->isInFaction($xuid)) {
                        if ($this->getManager()->getXuidOwnerFaction($name = $this->getManager()->getFactionName($xuid)) === $sender->getXuid() || $this->getManager()->getRankMember($xuid, $name) === Faction::OFFICIER) {
                            if (isset($args[1])) {
                                $player = $this->getPlugin()->getServer()->getPlayerByPrefix(strval($args[1]));
                                if (!is_null($player)) {
                                    if ($this->getManager()->isInFaction($player->getXuid())) {
                                        if ($this->getManager()->getFactionName($player->getXuid()) === $name) {
                                            $rank = $this->getManager()->getRankMember($player->getXuid(), $name);
                                            switch ($rank) {
                                                case Faction::RECRUE:
                                                    $sender->sendMessage(Messages::message("§f{$player->getName()} §cpossède déjà le grade le plus bas !"));
                                                    break;
                                                case Faction::MEMBER:

                                                    if ($this->getManager()->getRankMember($xuid, $name) !== Faction::OFFICIER) {
                                                        if (!$this->getManager()->getXuidOwnerFaction($name) === $xuid) {
                                                            $sender->sendMessage(Messages::message("§cVous devez être chef de votre clan pour pouvoir rétrograder un membre officier !"));
                                                            return;
                                                        }
                                                    }
                                                    $player->sendNotification("§fVous venez d'être §crétrogradé §fau grade de §6recrue");
                                                    $this->getManager()->setRankMember($player->getXuid(), Faction::RECRUE);
                                                    $sender->sendNotification("§fVous avez rétogradé §6{$player->getName()}§f au grade recrue !");
                                                    break;
                                                case Faction::OFFICIER:
                                                    if ($this->getManager()->getRankMember($xuid, $name) !== Faction::OFFICIER) {
                                                        $this->getManager()->setRankMember($player->getXuid(), Faction::MEMBER);
                                                        $player->sendMessage(Messages::message("§fVous venez d'être §crétrogradé §fau grade de §6membre"));
                                                        $sender->sendNotification("§fVous avez rétogradé §6{$player->getName()}§f au grade membre !");
                                                        $player->sendNotification("§fVous venez d'être §crétrogradé §fau grade de §6membre");
                                                    } else $sender->sendMessage(Messages::message("§cVous ne pouvez pas rétrograder un officier."));
                                                    break;
                                            }
                                        } else $sender->sendMessage(Messages::message("§cLe joueur n'est pas dans votre faction !"));
                                    } else $sender->sendMessage(Messages::message("§cLe joueur n'est pas dans votre faction !"));
                                } else {
                                    $xuidP = $this->getPlugin()->getDataManager()->getXuidByName(strval($args[1]));
                                    if (!is_null($xuidP)) {
                                        if ($this->getManager()->isInFaction($xuidP)) {
                                            if ($this->getManager()->getFactionName($xuidP) === $name) {
                                                $rank = $this->getManager()->getRankMember($xuidP, $name);
                                                switch ($rank) {
                                                    case Faction::RECRUE:
                                                        $sender->sendMessage(Messages::message("§f{$args[1]} §cpossède déjà le grade le plus bas !"));
                                                        break;
                                                    case Faction::MEMBER:

                                                        if ($this->getManager()->getRankMember($xuid, $name) !== Faction::OFFICIER) {
                                                            if (!$this->getManager()->getXuidOwnerFaction($name) === $xuid) {
                                                                $sender->sendMessage(Messages::message("§cVous devez être chef de votre clan pour pouvoir rétrograder un membre officier !"));
                                                                return;
                                                            }
                                                            $this->getManager()->setRankMember($xuidP, Faction::RECRUE);
                                                            $sender->sendNotification("§fVous avez rétogradé §6{$args[1]}§f au grade recrue !");
                                                        }
                                                        break;
                                                    case Faction::OFFICIER:
                                                        if ($this->getManager()->getRankMember($xuid, $name) !== Faction::OFFICIER) {
                                                            $this->getManager()->setRankMember($xuidP, Faction::MEMBER);
                                                            $sender->sendNotification("§fVous avez §crétogradé §6{$args[1]}§f au grade membre !");
                                                        } else $sender->sendMessage(Messages::message("§cVous ne pouvez pas rétrograder un officier."));
                                                        break;
                                                }
                                            } else $sender->sendMessage(Messages::message("§cLe joueur n'est pas dans votre faction !"));
                                        } else $sender->sendMessage(Messages::message("§cLe joueur n'est pas dans votre faction !"));
                                    } else $sender->sendMessage(Messages::message("§cLe joueur n'existe pas !"));
                                }
                            } else $sender->sendMessage(Messages::message("§cVous devez sélectionner un joueur pour cette commande !"));
                        } else $sender->sendMessage(Messages::message("§cVous devez être chef de la faction pour faire cette action !"));
                    } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    break;
                case 'info':
                    if (isset($args[1])) {
                        if ($this->getManager()->existFaction(strval($args[1]))) {
                            $chef = "§c" . $this->getPlugin()->getDataManager()->getNameByXuid($xuidL = $this->getManager()->getXuidOwnerFaction(strval($args[1])));
                            if ($chef === null) $chef = '§c404';
                            $playerT = $this->getPlugin()->getDataManager()->getPlayerXuid($xuidL);
                            if ($playerT !== null) $chef = "§a" . $playerT->getName();

                            $members = [];
                            $recrues = [];
                            $officiers = [];
                            foreach ($this->getManager()->getMembersFaction(strval($args[1])) as $xuid => $rank) {
                                if ($rank === Faction::RECRUE) {
                                    $namee = "§7" . $this->getPlugin()->getDataManager()->getNameByXuid($xuid);
                                    if ($namee === null) $namee = "§c404";
                                    $playerT = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
                                    if ($playerT !== null) $namee = "§a" . $playerT->getName();
                                    $recrues[] = $namee;
                                } elseif ($rank === Faction::MEMBER) {
                                    $namee = "§7" . $this->getPlugin()->getDataManager()->getNameByXuid($xuid);
                                    if ($namee === null) $namee = "§c404";
                                    $playerT = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
                                    if ($playerT !== null) $namee = "§a" . $playerT->getName();
                                    $members[] = $namee;
                                } elseif ($rank === Faction::OFFICIER) {
                                    $namee = "§7" . $this->getPlugin()->getDataManager()->getNameByXuid($xuid);
                                    if ($namee === null) $namee = "§c404";
                                    $playerT = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
                                    if ($playerT !== null) $namee = "§a" . $playerT->getName();
                                    $officiers[] = $namee;
                                }
                            }
                            if ($members === "") $members = "§7Aucun membres";
                            if ($recrues === "") $recrues = "§7Aucune recrues";
                            if ($officiers === "") $officiers = "§7Aucun officiers";

                            $factionName = strval($args[1]);
                            $power = $this->getManager()->getPower($factionName);
                            $money = $this->getManager()->getMoneyFaction($factionName);

                            $btn = [];

                            $btn[] = new Button("Chef - $chef");


                            foreach ($officiers as $officier) {
                                $btn[] = new Button("Sous-Chef - $officier");
                            }
                            foreach ($members as $member) {
                                $btn[] = new Button("Membre - $member");
                            }
                            foreach ($recrues as $recrue) {
                                $btn[] = new Button("Recrue - $recrue");
                            }

                            $power = $this->getManager()->getPower($factionName);


                            $btn[] = new Button("tpf_10");
                            $btn[] = new Button("pwr_" . Utils::moneyFormat($power));
                            $btn[] = new Button("des_" . $this->getManager()->getBio($factionName));

                            $btn[] = new Button("postuler");

                            $btn[] = new Button("rht_Crée une faction",new Image("textures/goldrush/faction/cree"));
                            $btn[] = new Button("bot_Menu principale",new Image("textures/goldrush/faction/home"));
                            $btn[] = new Button("lft_Ma faction",new Image("textures/goldrush/faction/info"));
                            $btn[] = new Button("lft_Banque de faction",new Image("textures/goldrush/faction/bank"));
                            $btn[] = new Button("rht_Top factions",new Image("textures/goldrush/faction/top"));
                            $btn[] = new Button("rht_Vos mails",new Image("textures/goldrush/faction/mail"));
                            $btn[] = new Button("lft_Quêtes de faction",new Image("textures/goldrush/faction/quest"));
                            $btn[] = new Button("bot_Retour",new Image("textures/goldrush/faction/back"));


                            $form = new MenuForm("SEE_FAC", $factionName,$btn,function (Player $player, Button $r):void{
                                FactionForms::validateTabsButtons($player, $r);

                                if ($r->getText() === "bot_Retour") {
                                    FactionForms::sendMenuFaction($player);
                                }
                            });
                            $sender->sendForm($form);


                        } else $sender->sendMessage(Messages::message("§cCette faction n'existe pas !"));
                    } else {
                        if ($this->getManager()->isInFaction($xuid)) {
                            $chef = "§c" . $this->getPlugin()->getDataManager()->getNameByXuid($xuidL = $this->getManager()->getXuidOwnerFaction($factionName = $this->getManager()->getFactionName($xuid)));
                            if ($chef === null) $chef = '§c404';
                            $playerT = $this->getPlugin()->getDataManager()->getPlayerXuid($xuidL);
                            if ($playerT !== null) $chef = "§a" . $playerT->getName();

                            $members = [];
                            $recrues = [];
                            $officiers = [];
                            foreach ($this->getManager()->getMembersFaction(strval($factionName)) as $xuid => $rank) {
                                if ($rank === Faction::RECRUE) {
                                    $namee = "§7" . $this->getPlugin()->getDataManager()->getNameByXuid($xuid);
                                    if ($namee === null) $namee = "§c404";
                                    $playerT = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
                                    if ($playerT !== null) $namee = "§a" . $playerT->getName();
                                    $recrues[] = $namee;
                                } elseif ($rank === Faction::MEMBER) {
                                    $namee = "§7" . $this->getPlugin()->getDataManager()->getNameByXuid($xuid);
                                    if ($namee === null) $namee = "§c404";
                                    $playerT = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
                                    if ($playerT !== null) $namee = "§a" . $playerT->getName();
                                    $members[] = $namee;
                                } elseif ($rank === Faction::OFFICIER) {
                                    $namee = "§7" . $this->getPlugin()->getDataManager()->getNameByXuid($xuid);
                                    if ($namee === null) $namee = "§c404";
                                    $playerT = $this->getPlugin()->getDataManager()->getPlayerXuid($xuid);
                                    if ($playerT !== null) $namee = "§a" . $playerT->getName();
                                    $officiers[] = $namee;
                                }
                            }
                            if ($members === "") $members = "§7Aucun membres";
                            if ($recrues === "") $recrues = "§7Aucune recrues";
                            if ($officiers === "") $officiers = "§7Aucun officiers";

                            $power = $this->getManager()->getPower($factionName);
                            $money = $this->getManager()->getMoneyFaction($factionName);

                            $btn = [];

                            $btn[] = new Button("Chef - $chef");


                            foreach ($officiers as $officier) {
                                $btn[] = new Button("Sous-Chef - $officier");
                            }
                            foreach ($members as $member) {
                                $btn[] = new Button("Membre - $member");
                            }
                            foreach ($recrues as $recrue) {
                                $btn[] = new Button("Recrue - $recrue");
                            }

                            $power = $this->getManager()->getPower($factionName);


                            $btn[] = new Button("tpf_10");
                            $btn[] = new Button("pwr_" . Utils::moneyFormat($power));
                            $btn[] = new Button("des_" . $this->getManager()->getBio($factionName));

                            $btn[] = new Button("postuler");

                            $btn[] = new Button("rht_Crée une faction",new Image("textures/goldrush/faction/cree"));
                            $btn[] = new Button("bot_Menu principale",new Image("textures/goldrush/faction/home"));
                            $btn[] = new Button("lft_Ma faction",new Image("textures/goldrush/faction/info"));
                            $btn[] = new Button("lft_Banque de faction",new Image("textures/goldrush/faction/bank"));
                            $btn[] = new Button("rht_Top factions",new Image("textures/goldrush/faction/top"));
                            $btn[] = new Button("rht_Vos mails",new Image("textures/goldrush/faction/mail"));
                            $btn[] = new Button("lft_Quêtes de faction",new Image("textures/goldrush/faction/quest"));
                            $btn[] = new Button("bot_Retour",new Image("textures/goldrush/faction/back"));


                            $form = new MenuForm("SEE_FAC", $factionName,$btn,function (Player $player, Button $r):void{
                                FactionForms::validateTabsButtons($player, $r);

                                if ($r->getText() === "bot_Retour") {
                                    FactionForms::sendMenuFaction($player);
                                }
                            });
                            $sender->sendForm($form);

                        } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    }
                    break;
                case 'transfer':
                    if ($this->getManager()->isInFaction($xuid)) {
                        if ($this->getManager()->getXuidOwnerFaction($name = $this->getManager()->getFactionName($xuid)) === $sender->getXuid()) {
                            if (isset($args[1])) {
                                $player = $this->getPlugin()->getServer()->getPlayerByPrefix(strval($args[1]));
                                if (!is_null($player)) {
                                    $this->getManager()->transferFaction($sender, $player, $name);
                                    $sender->sendMessage(Messages::message("§aVous venez de transférer votre faction au joueur §f{$player->getName()}§a !"));
                                    $player->sendMessage(Messages::message("§aLe chef de votre faction vous a transféré la propriété, vous êtes désormais chef de la faction !"));
                                } else $sender->sendMessage(Messages::message("§cLe joueur doit être connecté pour que vous puissiez lui donner votre faction !"));
                            } else $sender->sendMessage(Messages::message("§cVous devez sélectionner un joueur !"));
                        } else $sender->sendMessage(Messages::message("§cVous devez être chef de la faction pour faire cette action !"));
                    } else $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
                    break;
                case "help":
                    if (isset($args[1])) {
                        switch (strtolower($args[1])) {
                            case '1':
                                $msg = "§6- §fGoldRush Faction §c-§f Help§6 -\n";
                                $msg .= "§6§l=== §r§6PAGE §l1§c/§63 §r§l§6===§r\n";
                                $msg .= "§f/f help\n/f create\n/f delete\n/f money\n/f info (faction)";
                                $sender->sendMessage($msg);
                                break;
                            case '2':
                                $msg = "§6- §fGoldRush Faction §c-§f Help§6 -\n";
                                $msg .= "§6§l=== §r§6PAGE §l2§c/§63 §r§l§6===§r\n";
                                $msg .= "§f/f invite (joueur)\n/f accept\n/f deny\n/f sethome\n/f delhome\n/f home";
                                $sender->sendMessage($msg);
                                break;
                            case '3':
                                $msg = "§6- §fGoldRush Faction §c-§f Help§6 -\n";
                                $msg .= "§6§l=== §r§6PAGE §l3§c/§63 §r§l§6===§r\n";
                                $msg .= "§f/f claim\n/f unclaim\n/f promote\n/f demote\n/f leave";
                                $sender->sendMessage($msg);
                                break;
                        }
                    } else {
                        $msg = "§6- §fGoldRush Faction §c-§f Help§6 -\n";
                        $msg .= "§6§l=== §r§6PAGE §l1§c/§63 §r§l§6===§r\n";
                        $msg .= "§f/f help\n/f create\n/f delete\n/f money\n/f info";
                        $sender->sendMessage($msg);
                    }
                    break;
                case "chat":
                    if (!$this->isFactionChat($sender->getXuid())) {
                        $this->setFactionChat($sender->getXuid(), true);
                        $sender->sendMessage(Messages::message("§aVous venez d'activer le chat faction !"));
                    } else {
                        $this->setFactionChat($sender->getXuid(), false);
                        $sender->sendMessage(Messages::message("§cVous venez de désactiver le chat faction !"));
                    }
                    break;
            }
        } else {
            FactionForms::sendMenuFaction($sender);
        }
    }

    public function getManager(): Faction
    {
        return $this->getPlugin()->getFactionManager();
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addSubCommand(0, 'create');
        $this->addSubCommandOptionEnum(0, 1, "Créer une faction", true, 'GoldRush', []);
        $this->addSubCommand(1, 'delete');
        $this->addSubCommandOptionEnum(1, 1, "Supprimer une faction", true, 'GoldRush', []);
        $this->addSubCommand(2, 'money');
        $this->addSubCommandOptionEnum(2, 1, "Gérer l'argent de votre faction", true, 'GoldRush', []);
        $this->addSubCommand(3, 'promote');
        $array = [];

        /*
        if ($this->getManager()->isInFaction($player->getXuid())) {
            foreach ($this->getManager()->getMembersFaction($this->getManager()->getFactionName($player->getXuid())) as $xuid => $rank) {
                $name = $this->getPlugin()->getDataManager()->getNameByXuid($xuid);
                if (!is_null($name)) {
                    if (str_contains(" ", strtolower($name))) {
                        $name = "\"" . strtolower($name) . "\"";
                    } else {
                        $name = strtolower($name);
                    }
                    $array[$name] = $name;
                }
            }
        }
        */
        $this->addSubCommandOptionEnum(3, 1, 'Promouvoir un membre de votre faction', true, 'joueurs factions', $array);
        $this->addSubCommand(4, 'invite');
        $array2 = [];
        foreach ($this->getPlugin()->getServer()->getOnlinePlayers() as $players) {
            if (str_contains(" ", strtolower($players->getName()))) {
                $name = "\"" . strtolower($players->getName()) . "\"";
            } else {
                $name = strtolower($players->getName());
            }
            $array2[$name] = $name;
        }
        $this->addSubCommandOptionEnum(4, 4, 'Inviter un joueur dans votre faction', true, 'joueurs', $array2);
        $this->addSubCommand(5, 'accept');
        $this->addSubCommandOptionEnum(5, 1, "Accepter une invitation d'une faction", true, 'invitations', []);
        $this->addSubCommand(6, 'deny');
        $this->addSubCommandOptionEnum(6, 1, "Refuser une invitation d'une faction", true, 'invitations', []);
        $this->addSubCommand(7, 'leave');
        $this->addSubCommandOptionEnum(7, 1, "Quitter votre faction", true, 'GoldRush', []);
        $this->addSubCommand(8, 'claim');
        $this->addSubCommandOptionEnum(8, 1, "Claim un chunk", true, 'GoldRush', []);
        $this->addSubCommand(9, 'unclaim');
        $this->addSubCommandOptionEnum(9, 1, "Unclaim un chunk", true, 'GoldRush', []);
        $this->addSubCommand(10, 'kick');
        $this->addSubCommandOptionEnum(10, 1, "Virer un joueur d'une faction", true, 'joueurs factions', $array);
        $this->addSubCommand(11, 'sethome');
        $this->addSubCommandOptionEnum(11, 1, "Créer un home de faction", true, 'GoldRush', []);
        $this->addSubCommand(12, 'delhome');
        $this->addSubCommandOptionEnum(12, 1, "Supprimer un home de faction", true, 'GoldRush', []);
        $this->addSubCommand(13, 'home');
        $this->addSubCommandOptionEnum(13, 1, "Se rendre à un home de faction", true, 'GoldRush', []);
        $this->addSubCommand(14, 'info');
        $this->addSubCommandOptionEnum(14, 1, "Informations d'une faction", true, 'factions', $this->getManager()->getAllFactionNameArgs());
        $this->addSubCommand(15, 'help');
        $this->addSubCommandOptionEnum(15, 1, "Informations du faction", true, 'GoldRush', []);
        $this->addSubCommand(16, 'demote');
        $this->addSubCommandOptionEnum(16, 1, "Rétrogradé un joueur", true, 'joueurs factions', $array);
        $this->addSubCommand(17, 'transfer');
        $this->addSubCommandOptionEnum(17, 1, "Transférer la faction", true, 'joueurs factions', $array);
        $this->addSubCommand(18, 'bank');
        $this->addSubCommandOptionEnum(18, 1, "Voir la banque de faction", true, 'joueurs factions', $array);
        $this->addSubCommand(19, 'top');
        $this->addSubCommandOptionEnum(19, 1, "Voir le top faction", true, 'joueurs factions', $array);


        if ($player->hasPermission("faction.use")) {
            $this->addSubCommand(20, 'addmoney');
            $this->addSubCommandOptionEnum(20, 1, "Ajouter de l'argent a une faction", true, 'faction', []);

            $this->addSubCommand(21, 'removemoney');
            $this->addSubCommandOptionEnum(21, 1, "Retirer de l'argent a une faction", true, 'faction', []);

            $this->addSubCommand(22, 'addpower');
            $this->addSubCommandOptionEnum(22, 1, "Ajouter du power a un joueur", true, 'joueur', []);

            $this->addSubCommand(23, 'reducepower');
            $this->addSubCommandOptionEnum(23, 1, "Retirer du power a un joueur", true, 'joueur', []);
        }

        return parent::loadOptions($player);
    }
}