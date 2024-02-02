<?php

namespace core\forms;

use core\api\form\elements\Button;
use core\api\form\elements\Image;
use core\api\form\MenuForm;
use core\Main;
use core\managers\jobs\JobsManager;
use core\managers\stats\StatsManager;
use core\utils\Utils;
use pocketmine\player\Player;

class TopForms
{
    const TOP_FAC = "TOP_FAC";
    const TOP_KIL = "TOP_KIL";
    const TOP_GLD = "TOP_GLD";
    const TOP_MNY = "TOP_MNY";
    const TOP_JOB_BCH = "TOP_JOB_BCH";
    const TOP_JOB_FRM = "TOP_JOB_FRM";
    const TOP_JOB_MNR = "TOP_JOB_MNR";
    const TOP_JOB_HTR = "TOP_JOB_HTR";


    private $name_converteur = [
        "TOP_FAC" => 'tpfc_',
        "TOP_KIL" => 'tpkl_',
        "TOP_GLD" => 'tpgl_',
        "TOP_MNY" => 'tpmy_',
        "TOP_JOB_BCH" => 'tpjb_',
        "TOP_JOB_FRM" => 'tpjf_',
        "TOP_JOB_MNR" => 'tpjm_',
        "TOP_JOB_HTR" => 'tpjh_',
        "TEST" => 'tpfc_',
    ];

    private $icon_converteur = [
        "TOP_FAC" => 'top_fac_',
        "TOP_KIL" => 'top_kil_',
        "TOP_GLD" => 'top_gld_',
        "TOP_MNY" => 'top_mny_',
        "TOP_JOB_BCH" => 'tpjb_',
        "TOP_JOB_FRM" => 'tpjf_',
        "TOP_JOB_MNR" => 'tpjm_',
        "TOP_JOB_HTR" => 'tpjh_',
        "TEST" => 'top_fac_',
    ];


    public static function sendTopForm(Player $player, string $category): void
    {
        switch ($category) {
            case self::TOP_FAC:
                self::sendTopFac($player);
                break;
            case self::TOP_KIL:
                self::sendTopKill($player);
                break;
        }
    }

    public static function sendTopFac(Player $player, int $pageChoice = 0): void
    {
        $btn2[] = new Button("category_FAC", new Image("textures/goldrush/scoreboard/top_fac_btn"));
        $btn2[] = new Button("category_KIL", new Image("textures/goldrush/scoreboard/top_kil_btn"));
        $btn2[] = new Button("category_GLD", new Image("textures/goldrush/scoreboard/top_gld_btn"));
        $btn2[] = new Button("category_MNY", new Image("textures/goldrush/scoreboard/top_mny_btn"));
        $btn2[] = new Button("category_JOB", new Image("textures/goldrush/scoreboard/top_job_btn"));


        $topFactions = Main::getInstance()->getFactionManager()->generateTopFaction();

        $i = 0;
        $paginations = [0 => []];
        $page = 0;
        foreach ($topFactions as $xuid => $lvl) {
            switch ($i) {
                case 0:
                    $buttonName = "frst_" . $xuid . " - " . $lvl;
                    $image = "textures/goldrush/scoreboard/top_fac_1";
                    break;
                case 1:
                    $buttonName = "scnd_" . $xuid . " - " . $lvl;
                    $image = "textures/goldrush/scoreboard/top_fac_2";
                    break;
                case 2:
                    $buttonName = "tird_" . $xuid . " - " . $lvl;
                    $image = "textures/goldrush/scoreboard/top_fac_3";
                    break;
                default:
                    $buttonName = "tpfc_" . $xuid . " - " . $lvl;
                    $image = "textures/goldrush/scoreboard/top_fac_o";
                    break;
            }

            if ($i === 12) {
                $page++;
            }
            if ($i === 12 * 2) {
                $page++;
            }
            if ($i === 12 * 3) {
                break;
            }
            $paginations[$page][] = new Button($buttonName, new Image($image));
            $i++;
        }


        $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
        $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));

        $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
        $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
        $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
        $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
        $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
        $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/mail"));
        $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
        $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));

        $player->sendForm(new MenuForm(self::TOP_FAC, "", array_merge($btn2, (isset($paginations[$pageChoice]) ? $paginations[$pageChoice] : $paginations[0]), $btn), function (Player $player, Button $button) use ($paginations, $page, $pageChoice): void {
            FactionForms::validateTabsButtons($player, $button);
            self::validateJobsTabs($player, $button);
            if ($button->getText() === "bot_Retour") {
                FactionForms::sendMenuFaction($player);
            }

            if ($button->getText() == 'left_arrow') {
                self::sendTopFac($player, $pageChoice - 1);
            } elseif ($button->getText() == 'right_arrow') {
                self::sendTopFac($player, $pageChoice + 1);
            }

            self::validateCategory($player, $button);
        }));
    }

    public static function validateJobsTabs(Player $player, Button $button): void
    {
        $btn[] = new Button("job_mnr", new Image("textures/goldrush/scoreboard/job_mnr"));
        $btn[] = new Button("job_frm", new Image("textures/goldrush/scoreboard/job_frm"));
        $btn[] = new Button("job_bch", new Image("textures/goldrush/scoreboard/job_bch"));
        $btn[] = new Button("job_htr", new Image("textures/goldrush/scoreboard/job_htr"));


        switch ($button->getText()) {
            case "job_mnr":
                self::sendTopMineur($player);
                break;
            case "job_frm":
                self::sendTopFarmer($player);
                break;
            case "job_bch":
                self::sendTopBucheron($player);
                break;
            case "job_htr":
                self::sendTopHunter($player);
                break;
        }
    }

    public static function sendTopMineur(Player $player, int $pageChoice = 0): void
    {
        Main::getInstance()->getJobsManager()->generateTopJobs(JobsManager::MINOR, function (array $array) use ($player, $pageChoice): void {
            if (!$player->isConnected()) return;


            $btn2[] = new Button("category_FAC", new Image("textures/goldrush/scoreboard/top_fac_btn"));
            $btn2[] = new Button("category_KIL", new Image("textures/goldrush/scoreboard/top_kil_btn"));
            $btn2[] = new Button("category_GLD", new Image("textures/goldrush/scoreboard/top_gld_btn"));
            $btn2[] = new Button("category_MNY", new Image("textures/goldrush/scoreboard/top_mny_btn"));
            $btn2[] = new Button("category_JOB", new Image("textures/goldrush/scoreboard/top_job_btn"));


            $i = 0;
            $paginations = [0 => []];
            $page = 0;
            foreach ($array as $xuid => $lvl) {
                switch ($i) {
                    case 0:
                        $buttonName = "frst_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                    case 1:
                        $buttonName = "scnd_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                    case 2:
                        $buttonName = "tird_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                    default:
                        $buttonName = "tpjm_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                }

                if ($i === 12) {
                    $page++;
                }
                if ($i === 12 * 2) {
                    $page++;
                }
                if ($i === 12 * 3) {
                    break;
                }
                $paginations[$page][] = new Button($buttonName, new Image($image));
                $i++;
            }

            $btn[] = new Button("job_mnr", new Image("textures/goldrush/scoreboard/job_mnr"));
            $btn[] = new Button("job_frm", new Image("textures/goldrush/scoreboard/job_frm"));
            $btn[] = new Button("job_bch", new Image("textures/goldrush/scoreboard/job_bch"));
            $btn[] = new Button("job_htr", new Image("textures/goldrush/scoreboard/job_htr"));

            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));

            $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
            $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
            $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
            $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
            $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
            $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/mail"));
            $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
            $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));

            $player->sendForm(new MenuForm(self::TOP_JOB_MNR, "", array_merge($btn2, (isset($paginations[$pageChoice]) ? $paginations[$pageChoice] : $paginations[0]), $btn), function (Player $player, Button $button) use ($paginations, $page, $pageChoice): void {
                FactionForms::validateTabsButtons($player, $button);
                self::validateJobsTabs($player, $button);
                if ($button->getText() === "bot_Retour") {
                    FactionForms::sendMenuFaction($player);
                }

                if ($button->getText() == 'left_arrow') {
                    self::sendTopMineur($player, $pageChoice - 1);
                } elseif ($button->getText() == 'right_arrow') {
                    self::sendTopMineur($player, $pageChoice + 1);
                }

                self::validateCategory($player, $button);
            }));
        });
    }

    public static function validateCategory(Player $player, Button $button): void
    {
        switch ($button->getText()) {
            case "category_FAC":
                self::sendTopFac($player);
                break;
            case "category_KIL":
                self::sendTopKill($player);
                break;
            case "category_GLD":
                self::sendTopGold($player);
                break;
            case "category_MNY":
                self::sendTopMoney($player);
                break;
            case "category_JOB":
                self::sendTopJobs($player);
                break;
        }
    }

    public static function sendTopKill(Player $player, int $pageChoice = 0): void
    {
        $btn2[] = new Button("category_FAC", new Image("textures/goldrush/scoreboard/top_fac_btn"));
        $btn2[] = new Button("category_KIL", new Image("textures/goldrush/scoreboard/top_kil_btn"));
        $btn2[] = new Button("category_GLD", new Image("textures/goldrush/scoreboard/top_gld_btn"));
        $btn2[] = new Button("category_MNY", new Image("textures/goldrush/scoreboard/top_mny_btn"));
        $btn2[] = new Button("category_JOB", new Image("textures/goldrush/scoreboard/top_job_btn"));


        $array = Main::getInstance()->getStatsManager()->getAllCache();

        foreach ($array as $xuid => $values) {
            $array[$xuid] = $values[StatsManager::KILL];
        }
        arsort($array);

        $i = 0;
        $paginations = [0 => []];
        $page = 0;
        foreach ($array as $xuid => $lvl) {
            switch ($i) {
                case 0:
                    $buttonName = "frst_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                    $image = "textures/goldrush/scoreboard/top_kil_1";
                    break;
                case 1:
                    $buttonName = "scnd_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                    $image = "textures/goldrush/scoreboard/top_kil_2";
                    break;
                case 2:
                    $buttonName = "tird_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                    $image = "textures/goldrush/scoreboard/top_kil_3";
                    break;
                default:
                    $buttonName = "tpkl_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                    $image = "textures/goldrush/scoreboard/top_kil_o";
                    break;
            }

            if ($i === 12) {
                $page++;
            }
            if ($i === 12 * 2) {
                $page++;
            }
            if ($i === 12 * 3) {
                break;
            }
            $paginations[$page][] = new Button($buttonName, new Image($image));
            $i++;
        }


        $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
        $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));

        $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
        $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
        $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
        $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
        $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
        $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/mail"));
        $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
        $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));

        $player->sendForm(new MenuForm(self::TOP_KIL, "", array_merge($btn2, (isset($paginations[$pageChoice]) ? $paginations[$pageChoice] : $paginations[0]), $btn), function (Player $player, Button $button) use ($paginations, $page, $pageChoice): void {
            FactionForms::validateTabsButtons($player, $button);
            self::validateJobsTabs($player, $button);
            if ($button->getText() === "bot_Retour") {
                FactionForms::sendMenuFaction($player);
            }

            if ($button->getText() == 'left_arrow') {
                self::sendTopKill($player, $pageChoice - 1);
            } elseif ($button->getText() == 'right_arrow') {
                self::sendTopKill($player, $pageChoice + 1);
            }

            self::validateCategory($player, $button);
        }));
    }

    public static function sendTopGold(Player $player, int $pageChoice = 0): void
    {
        $btn2[] = new Button("category_FAC", new Image("textures/goldrush/scoreboard/top_fac_btn"));
        $btn2[] = new Button("category_KIL", new Image("textures/goldrush/scoreboard/top_kil_btn"));
        $btn2[] = new Button("category_GLD", new Image("textures/goldrush/scoreboard/top_gld_btn"));
        $btn2[] = new Button("category_MNY", new Image("textures/goldrush/scoreboard/top_mny_btn"));
        $btn2[] = new Button("category_JOB", new Image("textures/goldrush/scoreboard/top_job_btn"));


        $array = Main::getInstance()->getStatsManager()->getAllCache();

        foreach ($array as $xuid => $values) {
            $array[$xuid] = $values[StatsManager::GOLD_MINED];
        }
        arsort($array);

        $i = 0;
        $paginations = [0 => []];
        $page = 0;
        foreach ($array as $xuid => $lvl) {
            switch ($i) {
                case 0:
                    $buttonName = "frst_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                    $image = "textures/goldrush/scoreboard/top_gld_1";
                    break;
                case 1:
                    $buttonName = "scnd_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                    $image = "textures/goldrush/scoreboard/top_gld_2";
                    break;
                case 2:
                    $buttonName = "tird_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                    $image = "textures/goldrush/scoreboard/top_gld_3";
                    break;
                default:
                    $buttonName = "tpgl_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                    $image = "textures/goldrush/scoreboard/top_gld_o";
                    break;
            }

            if ($i === 12) {
                $page++;
            }
            if ($i === 12 * 2) {
                $page++;
            }
            if ($i === 12 * 3) {
                break;
            }
            $paginations[$page][] = new Button($buttonName, new Image($image));
            $i++;
        }


        $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
        $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));

        $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
        $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
        $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
        $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
        $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
        $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/mail"));
        $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
        $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));

        $player->sendForm(new MenuForm(self::TOP_GLD, "", array_merge($btn2, (isset($paginations[$pageChoice]) ? $paginations[$pageChoice] : $paginations[0]), $btn), function (Player $player, Button $button) use ($paginations, $page, $pageChoice): void {
            FactionForms::validateTabsButtons($player, $button);
            self::validateJobsTabs($player, $button);
            if ($button->getText() === "bot_Retour") {
                FactionForms::sendMenuFaction($player);
            }

            if ($button->getText() == 'left_arrow') {
                self::sendTopGold($player, $pageChoice - 1);
            } elseif ($button->getText() == 'right_arrow') {
                self::sendTopGold($player, $pageChoice + 1);
            }

            self::validateCategory($player, $button);
        }));
    }

    public static function sendTopMoney(Player $player, int $pageChoice = 0): void
    {
        $btn2[] = new Button("category_FAC", new Image("textures/goldrush/scoreboard/top_fac_btn"));
        $btn2[] = new Button("category_KIL", new Image("textures/goldrush/scoreboard/top_kil_btn"));
        $btn2[] = new Button("category_GLD", new Image("textures/goldrush/scoreboard/top_gld_btn"));
        $btn2[] = new Button("category_MNY", new Image("textures/goldrush/scoreboard/top_mny_btn"));
        $btn2[] = new Button("category_JOB", new Image("textures/goldrush/scoreboard/top_job_btn"));


        $array = Main::getInstance()->getEconomyManager()->globalCache;
        foreach ($array as $xuid => $values) {
            $array[$xuid] = $values;
        }
        arsort($array);

        $i = 0;
        $paginations = [0 => []];
        $page = 0;
        foreach ($array as $xuid => $lvl) {
            switch ($i) {
                case 0:
                    $buttonName = "frst_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . Utils::moneyFormat($lvl);
                    $image = "textures/goldrush/scoreboard/top_mny_1";
                    break;
                case 1:
                    $buttonName = "scnd_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . Utils::moneyFormat($lvl);
                    $image = "textures/goldrush/scoreboard/top_mny_2";
                    break;
                case 2:
                    $buttonName = "tird_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . Utils::moneyFormat($lvl);
                    $image = "textures/goldrush/scoreboard/top_mny_3";
                    break;
                default:
                    $buttonName = "tpmy_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . Utils::moneyFormat($lvl);
                    $image = "textures/goldrush/scoreboard/top_mny_o";
                    break;
            }

            if ($i === 12) {
                $page++;
            }
            if ($i === 12 * 2) {
                $page++;
            }
            if ($i === 12 * 3) {
                break;
            }
            $paginations[$page][] = new Button($buttonName, new Image($image));
            $i++;
        }


        $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
        $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));

        $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
        $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
        $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
        $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
        $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
        $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/mail"));
        $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
        $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));

        $player->sendForm(new MenuForm(self::TOP_MNY, "", array_merge($btn2, (isset($paginations[$pageChoice]) ? $paginations[$pageChoice] : $paginations[0]), $btn), function (Player $player, Button $button) use ($paginations, $page, $pageChoice): void {
            FactionForms::validateTabsButtons($player, $button);
            self::validateJobsTabs($player, $button);
            if ($button->getText() === "bot_Retour") {
                FactionForms::sendMenuFaction($player);
            }

            if ($button->getText() == 'left_arrow') {
                self::sendTopMoney($player, $pageChoice - 1);
            } elseif ($button->getText() == 'right_arrow') {
                self::sendTopMoney($player, $pageChoice + 1);
            }

            self::validateCategory($player, $button);
        }));
    }

    public static function sendTopJobs(Player $player): void
    {
        self::sendTopFarmer($player);
    }

    public static function sendTopFarmer(Player $player, int $pageChoice = 0): void
    {
        Main::getInstance()->getJobsManager()->generateTopJobs(JobsManager::FARMER, function (array $array) use ($player, $pageChoice): void {
            if (!$player->isConnected()) return;


            $btn2[] = new Button("category_FAC", new Image("textures/goldrush/scoreboard/top_fac_btn"));
            $btn2[] = new Button("category_KIL", new Image("textures/goldrush/scoreboard/top_kil_btn"));
            $btn2[] = new Button("category_GLD", new Image("textures/goldrush/scoreboard/top_gld_btn"));
            $btn2[] = new Button("category_MNY", new Image("textures/goldrush/scoreboard/top_mny_btn"));
            $btn2[] = new Button("category_JOB", new Image("textures/goldrush/scoreboard/top_job_btn"));


            $i = 0;
            $paginations = [0 => []];
            $page = 0;
            foreach ($array as $xuid => $lvl) {
                switch ($i) {
                    case 0:
                        $buttonName = "frst_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                    case 1:
                        $buttonName = "scnd_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                    case 2:
                        $buttonName = "tird_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                    default:
                        $buttonName = "tpjf_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                }

                if ($i === 12) {
                    $page++;
                }
                if ($i === 12 * 2) {
                    $page++;
                }
                if ($i === 12 * 3) {
                    break;
                }
                $paginations[$page][] = new Button($buttonName, new Image($image));
                $i++;
            }

            $btn[] = new Button("job_mnr", new Image("textures/goldrush/scoreboard/job_mnr"));
            $btn[] = new Button("job_frm", new Image("textures/goldrush/scoreboard/job_frm"));
            $btn[] = new Button("job_bch", new Image("textures/goldrush/scoreboard/job_bch"));
            $btn[] = new Button("job_htr", new Image("textures/goldrush/scoreboard/job_htr"));

            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));

            $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
            $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
            $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
            $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
            $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
            $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/mail"));
            $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
            $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));

            $player->sendForm(new MenuForm(self::TOP_JOB_FRM, "", array_merge($btn2, (isset($paginations[$pageChoice]) ? $paginations[$pageChoice] : $paginations[0]), $btn), function (Player $player, Button $button) use ($paginations, $page, $pageChoice): void {
                FactionForms::validateTabsButtons($player, $button);
                self::validateJobsTabs($player, $button);
                if ($button->getText() === "bot_Retour") {
                    FactionForms::sendMenuFaction($player);
                }

                if ($button->getText() == 'left_arrow') {
                    self::sendTopFarmer($player, $pageChoice - 1);
                } elseif ($button->getText() == 'right_arrow') {
                    self::sendTopFarmer($player, $pageChoice + 1);
                }

                self::validateCategory($player, $button);
            }));
        });
    }

    public static function sendTopBucheron(Player $player, int $pageChoice = 0): void
    {
        Main::getInstance()->getJobsManager()->generateTopJobs(JobsManager::LUMBERJACK, function (array $array) use ($player, $pageChoice): void {
            if (!$player->isConnected()) return;


            $btn2[] = new Button("category_FAC", new Image("textures/goldrush/scoreboard/top_fac_btn"));
            $btn2[] = new Button("category_KIL", new Image("textures/goldrush/scoreboard/top_kil_btn"));
            $btn2[] = new Button("category_GLD", new Image("textures/goldrush/scoreboard/top_gld_btn"));
            $btn2[] = new Button("category_MNY", new Image("textures/goldrush/scoreboard/top_mny_btn"));
            $btn2[] = new Button("category_JOB", new Image("textures/goldrush/scoreboard/top_job_btn"));


            $i = 0;
            $paginations = [0 => []];
            $page = 0;
            foreach ($array as $xuid => $lvl) {
                switch ($i) {
                    case 0:
                        $buttonName = "frst_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                    case 1:
                        $buttonName = "scnd_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                    case 2:
                        $buttonName = "tird_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                    default:
                        $buttonName = "tpjb_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                }

                if ($i === 12) {
                    $page++;
                }
                if ($i === 12 * 2) {
                    $page++;
                }
                if ($i === 12 * 3) {
                    break;
                }
                $paginations[$page][] = new Button($buttonName, new Image($image));
                $i++;
            }

            $btn[] = new Button("job_mnr", new Image("textures/goldrush/scoreboard/job_mnr"));
            $btn[] = new Button("job_frm", new Image("textures/goldrush/scoreboard/job_frm"));
            $btn[] = new Button("job_bch", new Image("textures/goldrush/scoreboard/job_bch"));
            $btn[] = new Button("job_htr", new Image("textures/goldrush/scoreboard/job_htr"));

            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));

            $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
            $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
            $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
            $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
            $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
            $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/mail"));
            $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
            $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));

            $player->sendForm(new MenuForm(self::TOP_JOB_BCH, "", array_merge($btn2, (isset($paginations[$pageChoice]) ? $paginations[$pageChoice] : $paginations[0]), $btn), function (Player $player, Button $button) use ($paginations, $page, $pageChoice): void {
                FactionForms::validateTabsButtons($player, $button);
                self::validateJobsTabs($player, $button);
                if ($button->getText() === "bot_Retour") {
                    FactionForms::sendMenuFaction($player);
                }

                if ($button->getText() == 'left_arrow') {
                    self::sendTopBucheron($player, $pageChoice - 1);
                } elseif ($button->getText() == 'right_arrow') {
                    self::sendTopBucheron($player, $pageChoice + 1);
                }

                self::validateCategory($player, $button);
            }));
        });
    }

    public static function sendTopHunter(Player $player, int $pageChoice = 0): void
    {
        Main::getInstance()->getJobsManager()->generateTopJobs(JobsManager::HUNTER, function (array $array) use ($player, $pageChoice): void {
            if (!$player->isConnected()) return;


            $btn2[] = new Button("category_FAC", new Image("textures/goldrush/scoreboard/top_fac_btn"));
            $btn2[] = new Button("category_KIL", new Image("textures/goldrush/scoreboard/top_kil_btn"));
            $btn2[] = new Button("category_GLD", new Image("textures/goldrush/scoreboard/top_gld_btn"));
            $btn2[] = new Button("category_MNY", new Image("textures/goldrush/scoreboard/top_mny_btn"));
            $btn2[] = new Button("category_JOB", new Image("textures/goldrush/scoreboard/top_job_btn"));


            $paginations = [0 => []];
            $page = 0;
            $i = 0;
            foreach ($array as $xuid => $lvl) {
                switch ($i) {
                    case 0:
                        $buttonName = "frst_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                    case 1:
                        $buttonName = "scnd_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                    case 2:
                        $buttonName = "tird_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                    default:
                        $buttonName = "tpjh_" . (Main::getInstance()->getDataManager()->getNameByXuid($xuid) ?? 404) . " - " . $lvl;
                        $image = "";
                        break;
                }

                if ($i === 12) {
                    $page++;
                }
                if ($i === 12 * 2) {
                    $page++;
                }
                if ($i === 12 * 3) {
                    break;
                }
                $paginations[$page][] = new Button($buttonName, new Image($image));
                $i++;
            }

            $btn[] = new Button("job_mnr", new Image("textures/goldrush/scoreboard/job_mnr"));
            $btn[] = new Button("job_frm", new Image("textures/goldrush/scoreboard/job_frm"));
            $btn[] = new Button("job_bch", new Image("textures/goldrush/scoreboard/job_bch"));
            $btn[] = new Button("job_htr", new Image("textures/goldrush/scoreboard/job_htr"));

            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));

            $btn[] = new Button("rht_Crée une faction", new Image("textures/goldrush/faction/cree"));
            $btn[] = new Button("bot_Menu principale", new Image("textures/goldrush/faction/home"));
            $btn[] = new Button("lft_Ma faction", new Image("textures/goldrush/faction/info"));
            $btn[] = new Button("lft_Banque de faction", new Image("textures/goldrush/faction/bank"));
            $btn[] = new Button("rht_Top factions", new Image("textures/goldrush/faction/top"));
            $btn[] = new Button("rht_Vos mails", new Image("textures/goldrush/faction/mail"));
            $btn[] = new Button("lft_Quêtes de faction", new Image("textures/goldrush/faction/quest"));
            $btn[] = new Button("bot_Retour", new Image("textures/goldrush/faction/back"));

            $player->sendForm(new MenuForm(self::TOP_JOB_HTR, "", array_merge($btn2, (isset($paginations[$pageChoice]) ? $paginations[$pageChoice] : $paginations[0]), $btn), function (Player $player, Button $button) use ($paginations, $page, $pageChoice): void {
                FactionForms::validateTabsButtons($player, $button);
                self::validateJobsTabs($player, $button);
                if ($button->getText() === "bot_Retour") {
                    FactionForms::sendMenuFaction($player);
                }

                if ($button->getText() == 'left_arrow') {
                    self::sendTopHunter($player, $pageChoice - 1);
                } elseif ($button->getText() == 'right_arrow') {
                    self::sendTopHunter($player, $pageChoice + 1);
                }

                self::validateCategory($player, $button);
            }));
        });
    }
}