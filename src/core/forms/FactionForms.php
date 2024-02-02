<?php

namespace core\forms;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Button;
use core\api\form\elements\Image;
use core\api\form\elements\Input;
use core\api\form\elements\Label;
use core\api\form\MenuForm;
use core\Main;
use core\managers\factions\FactionManager;
use core\managers\factions\FactionManager as Faction;
use core\managers\factions\FactionVisibility;
use core\messages\Messages;
use core\utils\Utils;
use DateTime;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class FactionForms
{
    public static function sendMenuFaction(Player $player): void
    {

        Main::getInstance()->getFactionManager()->hasNotifFaction($player, function (bool $hasNotif) use ($player): void {
            if (!$player->isConnected()) return;


            $btn = [];


            $btn[] = new Button("list_faction", new Image("textures/goldrush/faction/list_faction"));
            $btn[] = new Button("find_faction", new Image("textures/goldrush/faction/find_faction"));
            $btn[] = new Button("menu_mail", new Image("textures/goldrush/faction/menu_mail"));

            $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
            $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
            $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
            $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
            $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));


            $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/" . ($hasNotif ? "mail_notif" : "mail")));
            $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
            $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));


            $player->sendForm(new MenuForm("FACTION_MENU", "", $btn, function (Player $player, Button $button): void {
                self::validateTabsButtons($player, $button);
                switch ($button->getText()) {
                    case "bot_Retour":
                        MenuForms::sendMenu($player);
                        break;
                    case "menu_mail":
                        self::sendMailsMenu($player);
                        break;
                    case 'find_faction':
                        self::sendBrowseFaction($player);
                        break;
                    case 'list_faction':
                        self::sendListFaction($player);
                        break;
                }
            }));
        });
    }

    public static function validateTabsButtons(Player $player, Button $button): bool
    {
        $return = false;
        switch ($button->getText()) {
            case "lft_Ma faction":
                $return = true;
                self::sendMaFaction($player);
                break;
            case "list_faction":
                $return = true;
                break;
            case "find_faction":
                $return = true;
                break;
            case "menu_mail":
                $return = true;
                self::sendMailsMenu($player);
                break;
            case "rht_Crée une faction":
                $return = true;
                self::sendFormFactionCreate($player);
                break;
            case "lft_Banque de faction":
                $return = true;
                self::sendBankFaction($player);
                break;
            case "rht_Top factions":
                $return = true;
                TopForms::sendTopForm($player, TopForms::TOP_FAC);
                break;
            case "rht_Vos mails":
                $return = true;
                self::sendMailsMenu($player);
                break;
            case "lft_Quêtes de faction":
                $return = true;
                self::sendQuestUi($player);
                break;
            case "bot_Menu principale":
                $return = true;
                MenuForms::sendMenu($player);
                break;
        }

        return $return;
    }

    public static function sendMaFaction(Player $player): void
    {

        Main::getInstance()->getFactionManager()->hasNotifFaction($player, function (bool $hasNotif) use ($player): void {
            if (!$player->isConnected()) return;


            if (!Main::getInstance()->getFactionManager()->isInFaction($player->getXuid())) {
                $player->sendMessage(Messages::message("§cVous etes pas dans une faction"));
                return;
            }

            $factionName = Main::getInstance()->getFactionManager()->getFactionName($player->getXuid());

            $chef = "§c" . Main::getInstance()->getDataManager()->getNameByXuid($xuidL = Main::getInstance()->getFactionManager()->getXuidOwnerFaction(strval($factionName)));
            if ($chef === null) $chef = '§c404';
            $playerT = Main::getInstance()->getDataManager()->getPlayerXuid($xuidL);
            if ($playerT !== null) $chef = "§a" . $playerT->getName();

            $members = [];
            $recrues = [];
            $officiers = [];
            foreach (Main::getInstance()->getFactionManager()->getMembersFaction(strval($factionName)) as $xuid => $rank) {
                if ($rank === Faction::RECRUE) {
                    $namee = "§7" . Main::getInstance()->getDataManager()->getNameByXuid($xuid);
                    if ($namee === null) $namee = "§c404";
                    $playerT = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                    if ($playerT !== null) $namee = "§a" . $playerT->getName();
                    $recrues[] = $namee;
                } elseif ($rank === Faction::MEMBER) {
                    $namee = "§7" . Main::getInstance()->getDataManager()->getNameByXuid($xuid);
                    if ($namee === null) $namee = "§c404";
                    $playerT = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                    if ($playerT !== null) $namee = "§a" . $playerT->getName();
                    $members[] = $namee;
                } elseif ($rank === Faction::OFFICIER) {
                    $namee = "§7" . Main::getInstance()->getDataManager()->getNameByXuid($xuid);
                    if ($namee === null) $namee = "§c404";
                    $playerT = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                    if ($playerT !== null) $namee = "§a" . $playerT->getName();
                    $officiers[] = $namee;
                }
            }
            if ($members === "") $members = "§7Aucun membres";
            if ($recrues === "") $recrues = "§7Aucune recrues";
            if ($officiers === "") $officiers = "§7Aucun officiers";

            $factionName = strval($factionName);
            $power = Main::getInstance()->getFactionManager()->getPower($factionName);
            $money = Main::getInstance()->getFactionManager()->getMoneyFaction($factionName);

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


            $btn[] = new Button("tpf_10");
            $btn[] = new Button("pwr_" . Utils::moneyFormat($power));
            $btn[] = new Button("des_" . Main::getInstance()->getFactionManager()->getBio($factionName));

            $btn[] = new Button("postuler");

            $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
            $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
            $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
            $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
            $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
            $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/" . ($hasNotif ? "mail_notif" : "mail")));
            $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
            $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));


            $form = new MenuForm("SEE_FAC", $factionName, $btn, function (Player $player, Button $r): void {
                FactionForms::validateTabsButtons($player, $r);

                if ($r->getText() === "bot_Retour") {
                    FactionForms::sendMenuFaction($player);
                }
            });
            $player->sendForm($form);
        });

    }

    public static function sendMailsMenu(Player $player): void
    {

        if (!Main::getInstance()->getFactionManager()->isInFaction($player->getXuid())) {
            $player->sendErrorSound();
            $player->sendMessage(Messages::message("§cVous devez être dans une faction !"));
            return;
        }

        Main::getInstance()->getFactionManager()->hasNotifFaction($player, function (bool $hasNotif) use ($player): void {
            if (!$player->isConnected()) return;


            $btn[] = new Button("send_mail", new Image("textures/goldrush/mails/send_mail"));
            $btn[] = new Button("open", new Image("textures/goldrush/mails/open"));
            $btn[] = new Button("send_gift", new Image("textures/goldrush/mails/send_gift"));


            $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
            $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
            $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
            $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
            $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
            $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/mail"));
            $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
            $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));


            $player->sendForm(new MenuForm("MAILS_MENU", "", $btn, function (Player $player, Button $button): void {
                self::validateTabsButtons($player, $button);
                switch ($button->getText()) {
                    case "bot_Retour":
                        self::sendMenuFaction($player);
                        break;
                    case 'send_mail':
                        if (!in_array(Main::getInstance()->getFactionManager()->getRankMember(
                            $player->getXuid(),
                            $factionName = Main::getInstance()->getFactionManager()->getFactionName($player->getXuid())
                        ), [FactionManager::OWNER, FactionManager::OFFICIER])) {
                            $player->sendErrorSound();
                            $player->sendMessage(Messages::message("§cVous n'avez pas la permission d'envoyer des mails dans votre faction."));
                            return;
                        }

                        $form = new CustomForm("WRITE_MAIL", [
                            new Input("content", "Voici un text"),
                            new Input("target", "nom de la faction")
                        ], function (Player $player, CustomFormResponse $form) use ($factionName): void {
                            $message = $form->getValues()[0];
                            $factionNameTarget = $form->getValues()[1];
                            $message = str_replace("<br>", "\n", $message);

                            if (!Main::getInstance()->getFactionManager()->existFaction($factionNameTarget)) {
                                $player->sendErrorSound();
                                $player->sendMessage(Messages::message("§cLa faction §4" . $factionNameTarget . " §cn'existe pas !"));
                                return;
                            }

                            if ($factionNameTarget === $factionName) {
                                $player->sendErrorSound();
                                $player->sendMessage(Messages::message("§cVous ne pouvez pas envoyer d'email à votre propre faction."));
                                return;
                            }

                            Main::getInstance()->getFactionManager()->sendEmails($factionNameTarget, $message, $factionName);
                            $player->sendSuccessSound();
                            $player->sendMessage(Messages::message("§fVotre email vient d'être envoyé à la faction §6" . $factionNameTarget));
                        });
                        $player->sendForm($form);
                        break;
                    case 'open':
                        self::sendOpenEmail($player);
                        break;
                }
            }));
        });
    }

    public static function sendOpenEmail(Player $player): void
    {
        $factionName = Main::getInstance()->getFactionManager()->getFactionName($player->getXuid());

        Main::getInstance()->getFactionManager()->getMailsFactions($factionName, function (array $emails) use ($player): void {
            if (!$player->isConnected()) return;

            $btn = [];
            $ids = [];
            foreach ($emails as $values) {
                $factionNameSender = $values['faction_name_sender'];
                $msg = substr("De $factionNameSender : " . $values['msg'], 0, 30);
                if (strlen($msg) >= 30) $msg .= "...";
                $lue = $values['lue'];
                $btn[] = new Button($msg, new Image("textures/goldrush/nineslice/list_mails_" . ($lue === true ? "v" : "nv")));
                $ids[] = [
                    "id" => $values['id'],
                    "msg" => $values['msg'],
                    "faction_name_sender" => $values['faction_name_sender']
                ];
            }

            $btn = array_reverse($btn);

            $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
            $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
            $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
            $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
            $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));


            if (count($emails) > 0) {
                $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/mail_notif"));
            } else {
                $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/mail"));
            }


            $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
            $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));


            $player->sendForm(new MenuForm("LIST_MAILS", "", $btn, function (Player $player, Button $button) use ($ids): void {
                self::validateTabsButtons($player, $button);

                switch ($button->getText()) {
                    case "bot_Retour":
                        self::sendMailsMenu($player);
                        break;
                }

                $id = $ids[$button->getValue()]['id'] ?? "ERROR";

                if ($id === "ERROR") {
                    return;
                }

                $msg = $ids[$button->getValue()]['msg'];
                $factionNameSender = $ids[$button->getValue()]['faction_name_sender'];

                self::sendSeeEmail($player, $id, $msg, $factionNameSender);
            }));
        });
    }

    public static function sendSeeEmail(Player $player, int $idEmail, string $msg, string $factionNameSender): void
    {

        Main::getInstance()->getFactionManager()->hasNotifFaction($player, function (bool $hasNotif) use ($player, $idEmail, $msg, $factionNameSender): void {
            if (!$player->isConnected()) return;

            $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
            $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
            $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
            $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
            $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
            $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/" . ($hasNotif ? "mail_notif" : "mail")));
            $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
            $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));


            Main::getInstance()->getFactionManager()->setEmailLue($idEmail);

            $player->sendForm(new MenuForm("SEE_MAIL", $msg, $btn, function (Player $player, Button $button): void {
                self::validateTabsButtons($player, $button);

                if ($button->getText() === "bot_Retour") {
                    self::sendOpenEmail($player);
                }
            }));
        });
    }

    public static function sendFormFactionCreate(Player $sender): void
    {

        if (Main::getInstance()->getFactionManager()->isInFaction($sender->getXuid())) {
            $sender->sendMessage(Messages::message("§cVous êtes déjà dans une faction !"));
            $sender->sendErrorSound();
            return;
        }


        $form = new CustomForm("CREATE_FAC", [
            new Input("content", ""),
            new Input("target", "")
        ], function (Player $player, CustomFormResponse $r): void {
            list($bio, $name) = $r->getValues();
            $name = TextFormat::clean(strtolower($name));

            if (preg_match('/[^\w]/', $name) === 1) {
                $player->sendMessage(Messages::message("§cVotre nom de faction est incorrect"));
                return;
            }



            if (!Main::getInstance()->getFactionManager()->existFaction($name)) {
                if (strlen(strval($name)) >= $min = 3) {
                    if (strlen(strval($name)) <= $max = 10) {
                        $name = str_replace(" ", "_", $name);
                        Main::getInstance()->getFactionManager()->createFaction($player, $name, $bio, FactionVisibility::CLOSE);
                        $player->sendMessage(Messages::message("§aVotre faction a été crée avec succès !"));
                    } else $player->sendMessage(Messages::message("§cVotre nom de faction ne doit pas dépasser §4$max §clettres !"));
                } else $player->sendMessage(Messages::message("§cVotre nom de faction doit contenir au moins §4$min §clettres !"));
            } else $player->sendMessage(Messages::message("§cLe nom de votre faction est déjà enregistré."));
        });
        $sender->sendForm($form);
    }

    public static function sendBankFaction(Player $player): void
    {

        if (!Main::getInstance()->getFactionManager()->isInFaction($player->getXuid())) {
            $player->sendErrorSound();
            $player->sendMessage(Messages::message("§cTu n'est pas dans une faction !"));
            return;
        }


        Main::getInstance()->getFactionManager()->hasNotifFaction($player, function (bool $hasNotif) use ($player): void {
            if (!$player->isConnected()) return;

            $factionName = Main::getInstance()->getFactionManager()->getFactionName($player->getXuid());
            $money = Main::getInstance()->getFactionManager()->getMoneyFaction($factionName);
            $moneyPlayer = Main::getInstance()->getFactionManager()->getMoneyPlayer($player);

            $btn[] = new Button("sp_top_" . $moneyPlayer);
            $btn[] = new Button("sp_top_" . $money);
            $btn[] = new Button("sp_bot_add", new Image("textures/goldrush/faction/bank_add"));
            $btn[] = new Button("sp_bot_remove", new Image("textures/goldrush/faction/bank_remove"));

            $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
            $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
            $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
            $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
            $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
            $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/" . ($hasNotif ? "mail_notif" : "mail")));
            $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
            $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));


            $player->sendForm(new MenuForm("BANK_FACTION", "", $btn, function (Player $player, Button $button) use ($factionName): void {
                self::validateTabsButtons($player, $button);
                if ($button->getText() === "bot_Retour") {
                    self::sendMenuFaction($player);
                    return;
                }

                if ($button->getText() === "sp_bot_add") {
                    $player->sendForm(new CustomForm("BANK_INPUT", [
                        new Input("amount", "")
                    ], function (Player $player, CustomFormResponse $response) use ($factionName): void {
                        $money = $response->getInput()->getValue();
                        if (!is_numeric($money) || !(int)$money) {
                            $player->sendErrorSound();
                            $player->sendMessage(Messages::message("§cTu doit renseigner un chiffre entier !"));
                            return;
                        }
                        Main::getInstance()->getEconomyManager()->getMoneySQL($player, function (Player $player, int $moneyPlayer) use ($money, $factionName): void {
                            if ($moneyPlayer < $money) {
                                $player->sendErrorSound();
                                $player->sendMessage(Messages::message("§cTu n'as pas assez d'argent !"));
                                return;
                            }

                            Main::getInstance()->getEconomyManager()->removeMoney($player, $money);
                            Main::getInstance()->getFactionManager()->addMoneyFaction($factionName, $money, $player);

                            $player->sendSuccessSound();
                            $player->sendMessage(Messages::message("§fVous avez ajouté §6" . $money . "$ §fà votre faction !"));
                        });
                    }));
                    return;
                } elseif ($button->getText() === "sp_bot_remove") {


                    if (Main::getInstance()->getFactionManager()->getRankMember($player->getXuid(), $factionName) !== FactionManager::OWNER &&
                        Main::getInstance()->getFactionManager()->getRankMember($player->getXuid(), $factionName) !== FactionManager::OFFICIER
                    ) {
                        $player->sendMessage(Messages::message("§cSeul le chef de la faction et les officiers peuivent retirer l'argent !"));
                        return;
                    }

                    $moneyContribute = Main::getInstance()->getFactionManager()->getMoneyFaction($factionName);
                    if ($moneyContribute <= 0) {
                        $player->sendMessage(Messages::message("§cVous n'avez pas d'argent dans la banque de votre faction."));
                        return;
                    }

                    $player->sendForm(new CustomForm("BANK_INPUT", [
                        new Input("amount", "")
                    ], function (Player $player, CustomFormResponse $response) use ($factionName): void {
                        $money = $response->getInput()->getValue();
                        if (!is_numeric($money) || !(int)$money) {
                            $player->sendErrorSound();
                            $player->sendMessage(Messages::message("§cTu doit renseigner un chiffre entier !"));
                            return;
                        }

                        $moneyContribute = Main::getInstance()->getFactionManager()->getMoneyFaction($factionName);
                        if ($moneyContribute <= 0) {
                            $player->sendMessage(Messages::message("§cVous n'avez pas d'argent dans la banque de votre faction."));
                            return;
                        }

                        if ($money > $moneyContribute) {
                            $money = $moneyContribute;
                        }

                        Main::getInstance()->getEconomyManager()->getMoneySQL($player, function (Player $player, int $moneyPlayer) use ($moneyContribute, $money, $factionName): void {

                            Main::getInstance()->getEconomyManager()->addMoney($player, $money);
                            Main::getInstance()->getFactionManager()->removeMoneyFaction($factionName, $money, $player);

                            $player->sendSuccessSound();
                            $player->sendMessage(Messages::message("§fVous avez retiré §6" . $money . "$ §fde votre faction !"));
                        });
                    }));
                }
            }));
        });
    }

    public static function sendQuestUi(Player $sender): void
    {
        if (!Main::getInstance()->getFactionManager()->isInFaction($sender->getXuid())) {
            $sender->sendMessage(Messages::message("§cVous n'êtes pas dans une faction !"));
            $sender->sendErrorSound();
            return;
        }


        Main::getInstance()->getFactionManager()->hasNotifFaction($sender, function (bool $hasNotif) use ($sender): void {
            if (!$sender->isConnected()) return;
            $elements = [];

            $item = Main::getInstance()->getFactionManager()->getItemQuest();
            $itemNumber = Main::getInstance()->getFactionManager()->getItemNumberFactionQuest($factionName = Main::getInstance()->getFactionManager()->getFactionName($sender->getXuid()));


            $textureItem = "";
            foreach (FactionManager::QUEST_TEXTURE as $itemStringLegacy => $texture) {
                $itemTarget = StringToItemParser::getInstance()->parse($itemStringLegacy);
                if ($itemTarget instanceof Item) {
                    if ($itemTarget->getTypeId() == $item->getTypeId()) {
                        $textureItem = $texture;
                        break;
                    }
                }
            }

            $limit = 200000;

            $countProgress = $itemNumber / $limit;
            if ($countProgress >= 0.25) {
                $elements[] = new Label("progress_1");
                $elements[] = new Label("progress_empty");
                $elements[] = new Label("progress_empty");
                $elements[] = new Label("progress_empty");
            } elseif ($countProgress >= 0.50) {
                $elements[] = new Label("progress_1");
                $elements[] = new Label("progress_2_3");
                $elements[] = new Label("progress_empty");
                $elements[] = new Label("progress_empty");
            } elseif ($countProgress >= 0.75) {
                $elements[] = new Label("progress_1");
                $elements[] = new Label("progress_2_3");
                $elements[] = new Label("progress_2_3");
                $elements[] = new Label("progress_empty");
            } elseif ($countProgress >= 0.99) {
                $elements[] = new Label("progress_1");
                $elements[] = new Label("progress_2_3");
                $elements[] = new Label("progress_2_3");
                $elements[] = new Label("progress_4");
            } else {
                $elements[] = new Label("progress_empty");
                $elements[] = new Label("progress_empty");
                $elements[] = new Label("progress_empty");
                $elements[] = new Label("progress_empty");
            }


            $numberPlayer = Main::getInstance()->getFactionManager()->getItemNumberFactionQuestPlayer($sender);


            date_default_timezone_set('Europe/Paris');


            $now = new DateTime();
            $nextSunday = clone $now;
            $nextSunday->modify('next Sunday');
            $nextSunday->setTime(23, 59, 0);
            $diff = $nextSunday->getTimestamp() - $now->getTimestamp();
            $hoursRemaining = $diff / 3600;


            /*
             * const QUEST = [
            'minecraft:wheat',
            'minecraft:carrot',
            'minecraft:potatoes',
            'minecraft:beetroot',
            'minecraft:melon',
            'minecraft:cactus',
            'minecraft:sugarcane',
            Ids::RAISIN,
            Ids::BERRY_BLUE,
            Ids::BERRY_BLACK,
            Ids::BERRY_YELLOW,
            Ids::BERRY_PINK,
            Ids::COPPER_RAW,
            Ids::PLATINUM_RAW,
            Ids::GOLD_RAW,
            Ids::AMETHYST_INGOT
        ];
             */


            if (method_exists(get_class($item), "getTextureString")) {
                $textureName = "itm_textures/items/" . $item->getTextureString();
            } else {
                $textureName = "itm_textures" . $textureItem;
            }


            $item = new Label($textureName);
            $amount = new Label("itm_cnt_x" . Main::getInstance()->getFactionManager()->getItemNumberFactionQuest($factionName));
            $total = new Label("abcd_Joueur: " . $numberPlayer);
            $temp_restant = new Label("abcd_§z" . intval($hoursRemaining) . 'h00');


            $elements[] = $item;
            $elements[] = $amount;
            $elements[] = $total;
            $elements[] = $temp_restant;


            $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
            $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
            $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
            $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
            $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
            $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/" . ($hasNotif ? "mail_notif" : "mail")));
            $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
            $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));

            $form = new CustomForm("QUEST_FACTION", $elements, function (Player $player, CustomFormResponse $form): void {
            });
            $sender->sendForm($form);
        });
    }

    public static function sendBrowseFaction(Player $player): void
    {
        $player->sendForm(new CustomForm("FIND_FACTION", [
            new Input("Browse", "faction")
        ], function (Player $player, CustomFormResponse $response): void {


            Main::getInstance()->getFactionManager()->hasNotifFaction($player, function (bool $hasNotif) use ($response, $player): void {
                if (!$player->isConnected()) return;


                $inputValue = $response->getInput()->getValue();


                $array = Main::getInstance()->getFactionManager()->getAllFactionByNamePrefix($inputValue);
                if (empty($array)) {
                    $player->sendErrorSound();
                    $player->sendMessage(Messages::message("§cAucune faction trouvé :("));
                    return;
                }

                if (count($array) === 1) {
                    self::sendFactionInfo($player, $array[0]);
                    return;
                }

                $btn = [];
                foreach ($array as $facName) $btn[] = new Button($facName);


                $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
                $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
                $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
                $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
                $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
                $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/" . ($hasNotif ? "mail_notif" : "mail")));
                $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
                $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));

                $player->sendForm(new MenuForm("BROWSE_FACTIONS", "", $btn, function (Player $player, Button $button): void {
                    $return = self::validateTabsButtons($player, $button);
                    if ($return) return;

                    if ($button->getText() === "bot_Retour") {
                        self::sendMenuFaction($player);
                        return;
                    }

                    $facName = $button->getText();
                    self::sendFactionInfo($player, $facName);
                }));
            });
        }));
    }

    public static function sendFactionInfo(Player $player, string $factionName): void
    {

        if (!Main::getInstance()->getFactionManager()->existFaction($factionName)) return;


        Main::getInstance()->getFactionManager()->hasNotifFaction($player, function (bool $hasNotif) use ($player, $factionName): void {
            if (!$player->isConnected()) return;
            $chef = "§c" . Main::getInstance()->getDataManager()->getNameByXuid($xuidL = Main::getInstance()->getFactionManager()->getXuidOwnerFaction(strval($factionName)));
            if ($chef === null) $chef = '§c404';
            $playerT = Main::getInstance()->getDataManager()->getPlayerXuid($xuidL);
            if ($playerT !== null) $chef = "§a" . $playerT->getName();

            $members = [];
            $recrues = [];
            $officiers = [];
            foreach (Main::getInstance()->getFactionManager()->getMembersFaction(strval($factionName)) as $xuid => $rank) {
                if ($rank === Faction::RECRUE) {
                    $namee = "§7" . Main::getInstance()->getDataManager()->getNameByXuid($xuid);
                    if ($namee === null) $namee = "§c404";
                    $playerT = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                    if ($playerT !== null) $namee = "§a" . $playerT->getName();
                    $recrues[] = $namee;
                } elseif ($rank === Faction::MEMBER) {
                    $namee = "§7" . Main::getInstance()->getDataManager()->getNameByXuid($xuid);
                    if ($namee === null) $namee = "§c404";
                    $playerT = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                    if ($playerT !== null) $namee = "§a" . $playerT->getName();
                    $members[] = $namee;
                } elseif ($rank === Faction::OFFICIER) {
                    $namee = "§7" . Main::getInstance()->getDataManager()->getNameByXuid($xuid);
                    if ($namee === null) $namee = "§c404";
                    $playerT = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                    if ($playerT !== null) $namee = "§a" . $playerT->getName();
                    $officiers[] = $namee;
                }
            }
            if ($members === "") $members = "§7Aucun membres";
            if ($recrues === "") $recrues = "§7Aucune recrues";
            if ($officiers === "") $officiers = "§7Aucun officiers";

            $factionName = strval($factionName);
            $power = Main::getInstance()->getFactionManager()->getPower($factionName);
            $money = Main::getInstance()->getFactionManager()->getMoneyFaction($factionName);

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


            $btn[] = new Button("tpf_10");
            $btn[] = new Button("pwr_" . Utils::moneyFormat($power));
            $btn[] = new Button("des_" . Main::getInstance()->getFactionManager()->getBio($factionName));

            $btn[] = new Button("postuler");

            $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
            $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
            $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
            $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
            $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
            $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/" . ($hasNotif ? "mail_notif" : "mail")));
            $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
            $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));


            $form = new MenuForm("SEE_FAC", $factionName, $btn, function (Player $player, Button $r): void {
                FactionForms::validateTabsButtons($player, $r);

                if ($r->getText() === "bot_Retour") {
                    FactionForms::sendMenuFaction($player);
                }
            });
            $player->sendForm($form);
        });

    }

    public static function sendListFaction(Player $player, int $pageChoice = 1): void
    {
        $array = Main::getInstance()->getFactionManager()->getAllFactionNameArgs();
        if (empty($array)) {
            $player->sendErrorSound();
            $player->sendMessage(Messages::message("§cAucune faction trouvé :("));
            return;
        }


        $btn = [];
        foreach ($array as $facName) $btn[] = new Button($facName);


        Main::getInstance()->getFactionManager()->hasNotifFaction($player, function (bool $hasNotif) use ($player, $pageChoice): void {
            if (!$player->isConnected()) return;

            $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
            $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
            $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
            $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
            $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));


            if (!$hasNotif) {
                $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/mail"));
            } else $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/mail_notif"));


            $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
            $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));

            $player->sendForm(new MenuForm("BROWSE_FACTIONS", "", $btn, function (Player $player, Button $button): void {
                $return = self::validateTabsButtons($player, $button);
                if ($return) return;

                if ($button->getText() === "bot_Retour") {
                    self::sendMenuFaction($player);
                    return;
                }

                $facName = $button->getText();
                self::sendFactionInfo($player, $facName);
            }));
        });
    }
}