<?php

namespace core\forms;

use core\api\form\elements\Button;
use core\api\form\elements\Image;
use core\api\form\MenuForm;
use core\Main;
use core\managers\jobs\JobsManager;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\player\Player;
use pocketmine\Server;

class MenuForms
{
    public static function sendMenu(Player $player): void
    {
        $btn[] = new Button("Shop", new Image("textures/goldrush/menu/shop"));
        $btn[] = new Button("Jobs", new Image("textures/goldrush/menu/jobs"));
        $btn[] = new Button("Factions", new Image("textures/goldrush/menu/factions"));
        $btn[] = new Button("Cosmetics", new Image("textures/goldrush/menu/cosmetics"));
        $btn[] = new Button("Montures", new Image("textures/goldrush/menu/montures"));


        $player->sendForm(new MenuForm("MAIN_MENU_FORMS", "", $btn, function (Player $player, Button $button): void {
            switch ($button->getText()) {
                case "Shop":
                    ShopForms::sendMainMenuShop($player);
                    break;
                case "Jobs":
                    self::sendJobsForms($player);
                    break;
                case "Factions":
                    FactionForms::sendMenuFaction($player);
                    break;
                case "Cosmetics":
                    Server::getInstance()->dispatchCommand($player, "cosmetics");
                    break;
                case "Montures":
                    self::sendMonture($player);
                    break;
            }
        }));
    }

    public static function sendJobsForms(Player $sender, int $pageChoice = 0): void
    {
        //obligation d'avoir pour chaque niveaux les 3 bouttons !!!!!!!!!!!!!!!!!!!
        /**
         * texture si y'a un item/craft give: textures/goldrush/nineslice/jobs_bg
         * texture si on gagne rien item/craft: textures/goldrush/nineslice/jobs_no_bg
         * texture pour les chiffres des niveaux: textures/goldrush/nineslice/jobs_rond_bg
         */


        $btn = [];

        $btn[] = new Button("a", new Image("textures/goldrush/jobs/mineur"));
        $btn[] = new Button("a", new Image("textures/goldrush/jobs/hunter"));
        $btn[] = new Button("a", new Image("textures/goldrush/jobs/bucheron"));
        $btn[] = new Button("a", new Image("textures/goldrush/jobs/farmer"));


        $form = new MenuForm("SELECT_JOBS", "", $btn, function (Player $player, Button $button) use ($pageChoice): void {
            $choice = 'farmer';
            switch ($button->getValue()) {
                case 0:
                    $choice = 'mineur';
                    break;
                case 1:
                    $choice = 'hunter';
                    break;
                case 2:
                    $choice = 'bucheron';
                    break;
                case 3:
                    $choice = 'farmer';
                    break;
            }
            self::sendJobsInfo($player, $choice, $pageChoice);
        });


        $sender->sendForm($form);
    }

    public static function sendJobsInfo(Player $player, string $choice, int $pageChoice = 0): void
    {


        Main::getInstance()->getJobsManager()->getAllJobsXuidSql($player->getXuid(), function ($jobs) use ($player, $choice, $pageChoice): void {
            if (!$player->isConnected()) return;
            $btn = [];

            $pagination = [0 => []];
            switch ($choice) {
                case 'farmer':
                    $title = 'farmer';
                    $data = [
                        2 => [
                            'craft' => 'aucun',
                            'item' => 'x1 seau en cuivre'
                        ],
                        3 => [
                            'craft' => 'aucun',
                            'item' => 'x64 terre compréssé'
                        ],
                        4 => [
                            'craft' => "aucun",
                            'item' => "x256 poudre d'os"
                        ],
                        5 => [
                            'craft' => "plastron + pelle émeraude",
                            'item' => "aucun"
                        ],
                        6 => [
                            'craft' => "aucun",
                            'item' => "x128 bouteille d'xp"
                        ],
                        7 => [
                            'craft' => "aucun",
                            'item' => "30 000$"
                        ],
                        8 => [
                            'craft' => "aucun",
                            'item' => "64 graines d'obsidiennes"
                        ],
                        9 => [
                            'craft' => "aucun",
                            'item' => "x1 sac du fermier/fossils"
                        ],
                        10 => [
                            'craft' => "plastron + pelle améthyste",
                            'item' => "x256 bouteille d'xp"
                        ],
                        11 => [
                            'craft' => "aucun",
                            'item' => "x256 bouteille d'xp"
                        ],
                        12 => [
                            'craft' => "aucun",
                            'item' => "60 000$"
                        ],
                        13 => [
                            'craft' => "aucun",
                            'item' => "x1 unclaim finder améthyste"
                        ],
                        14 => [
                            'craft' => "aucun",
                            'item' => "x1 hache en améthyste"
                        ],
                        15 => [
                            'craft' => "plastron + pelle platine",
                            'item' => "aucun"
                        ],
                        16 => [
                            'craft' => "aucun",
                            'item' => "120 000$"
                        ],
                        17 => [
                            'craft' => "aucun",
                            'item' => "x448 bouteille d'xp"
                        ],
                        18 => [
                            'craft' => "aucun",
                            'item' => "x1 hache en platine"
                        ],
                        19 => [
                            'craft' => "aucun",
                            'item' => "x1 seau en or"
                        ],
                        20 => [
                            'craft' => "plastron + pelle en or",
                            'item' => "aucun"
                        ],
                        21 => [
                            'craft' => "aucun",
                            'item' => "300 000$"
                        ],
                        22 => [
                            'craft' => "aucun",
                            'item' => "500 000$"
                        ],
                        23 => [
                            'craft' => "aucun",
                            'item' => "600 000$"
                        ],
                        24 => [
                            'craft' => "aucun",
                            'item' => "800 000$"
                        ],
                        25 => [
                            'craft' => "aucun",
                            'item' => "1 000 000$"
                        ],
                    ];


                    $i = 2;
                    $lvl = $jobs[JobsManager::FARMER]['lvl'];
                    $page = 0;
                    while ($i !== 26) {

                        if ($i === 10) {
                            $page++;
                        }
                        if ($i === 20) {
                            $page++;
                        }

                        $pagination[$page][] = new Button("niv_§z$i", new Image("textures/goldrush/nineslice/jobs_rond_bg"));

                        if ($lvl >= $i) {
                            $pagination[$page][] = new Button("rcp_§z" . $data[$i]['item'], new Image("textures/goldrush/nineslice/jobs_bg"));
                            $pagination[$page][] = new Button("crt_" . $data[$i]['craft'], new Image("textures/goldrush/nineslice/jobs_bg"));
                        } else {
                            $pagination[$page][] = new Button("rcp_§z" . $data[$i]['item'], new Image("textures/goldrush/nineslice/jobs_no_bg"));
                            $pagination[$page][] = new Button("crt_" . $data[$i]['craft'], new Image("textures/goldrush/nineslice/jobs_no_bg"));
                        }
                        $i++;
                    }
                    break;
                case 'mineur':
                    $title = 'minor';
                    $data = [
                        2 => [
                            'craft' => 'aucun',
                            'item' => 'x1 pioche en cuivre'
                        ],
                        3 => [
                            'craft' => 'aucun',
                            'item' => "x32 bouteilles d'xp"
                        ],
                        4 => [
                            'craft' => "aucun",
                            'item' => "128 émeraudes"
                        ],
                        5 => [
                            'craft' => "hammer, casque, pioche émeraude",
                            'item' => "aucun"
                        ],
                        6 => [
                            'craft' => "aucun",
                            'item' => "x1 VoidStone"
                        ],
                        7 => [
                            'craft' => "aucun",
                            'item' => "30 000$"
                        ],
                        8 => [
                            'craft' => "aucun",
                            'item' => "x64 bouteilles d'xp"
                        ],
                        9 => [
                            'craft' => "aucun",
                            'item' => "x1 pioche en émeraude"
                        ],
                        10 => [
                            'craft' => "hammer, casque, pioche améthyste",
                            'item' => "aucun"
                        ],
                        11 => [
                            'craft' => "aucun",
                            'item' => "x1 Sac du mineur"
                        ],
                        12 => [
                            'craft' => "aucun",
                            'item' => "60 000$"
                        ],
                        13 => [
                            'craft' => "aucun",
                            'item' => "x64 dynamites en émeraude"
                        ],
                        14 => [
                            'craft' => "aucun",
                            'item' => "x1 fleur de camouflage"
                        ],
                        15 => [
                            'craft' => "hammer, casque, pioche platine",
                            'item' => "aucun"
                        ],
                        16 => [
                            'craft' => "aucun",
                            'item' => "x128 obsidiennes en platine"
                        ],
                        17 => [
                            'craft' => "aucun",
                            'item' => "120 000 $"
                        ],
                        18 => [
                            'craft' => "aucun",
                            'item' => "x256 bouteilles d'xp"
                        ],
                        19 => [
                            'craft' => "aucun",
                            'item' => "x1 unclaim finder en or"
                        ],
                        20 => [
                            'craft' => "hammer, casque, pioche or",
                            'item' => "aucun"
                        ],
                        21 => [
                            'craft' => "aucun",
                            'item' => "300 000$"
                        ],
                        22 => [
                            'craft' => "aucun",
                            'item' => "500 000$"
                        ],
                        23 => [
                            'craft' => "aucun",
                            'item' => "600 000$"
                        ],
                        24 => [
                            'craft' => "aucun",
                            'item' => "800 000$"
                        ],
                        25 => [
                            'craft' => "aucun",
                            'item' => "1 000 000$"
                        ],
                    ];

                    $i = 2;
                    $lvl = $jobs[JobsManager::MINOR]['lvl'];
                    $pagination = [0 => []];
                    $page = 0;
                    while ($i !== 26) {

                        if ($i === 10) {
                            $page++;
                        }
                        if ($i === 20) {
                            $page++;
                        }

                        $pagination[$page][] = new Button("niv_§z$i", new Image("textures/goldrush/nineslice/jobs_rond_bg"));

                        if ($lvl >= $i) {
                            $pagination[$page][] = new Button("rcp_§z" . $data[$i]['item'], new Image("textures/goldrush/nineslice/jobs_bg"));
                            $pagination[$page][] = new Button("crt_" . $data[$i]['craft'], new Image("textures/goldrush/nineslice/jobs_bg"));
                        } else {
                            $pagination[$page][] = new Button("rcp_§z" . $data[$i]['item'], new Image("textures/goldrush/nineslice/jobs_no_bg"));
                            $pagination[$page][] = new Button("crt_" . $data[$i]['craft'], new Image("textures/goldrush/nineslice/jobs_no_bg"));
                        }
                        $i++;
                    }
                    break;
                case 'hunter':
                    $title = 'hunter';
                    $data = [
                        2 => [
                            'craft' => 'aucun',
                            'item' => 'x32 alcool de soin'
                        ],
                        3 => [
                            'craft' => 'aucun',
                            'item' => "x1 épée en diamant"
                        ],
                        4 => [
                            'craft' => "aucun",
                            'item' => "x16 raisins"
                        ],
                        5 => [
                            'craft' => "bottes, épée émeraude",
                            'item' => "aucun"
                        ],
                        6 => [
                            'craft' => "aucun",
                            'item' => "50 000$"
                        ],
                        7 => [
                            'craft' => "aucun",
                            'item' => "x256 bouteilles vides"
                        ],
                        8 => [
                            'craft' => "aucun",
                            'item' => "x128 bouteilles d'xp"
                        ],
                        9 => [
                            'craft' => "aucun",
                            'item' => "x5 alcool soin puissant"
                        ],
                        10 => [
                            'craft' => "bottes, épée améthyste",
                            'item' => "aucun"
                        ],
                        11 => [
                            'craft' => "aucun",
                            'item' => "50 000$"
                        ],
                        12 => [
                            'craft' => "aucun",
                            'item' => "x16 clés rares"
                        ],
                        13 => [
                            'craft' => "aucun",
                            'item' => "x64 graines en obsidiennes"
                        ],
                        14 => [
                            'craft' => "aucun",
                            'item' => "x3 alcool de force++"
                        ],
                        15 => [
                            'craft' => "bottes, épée platine",
                            'item' => "aucun"
                        ],
                        16 => [
                            'craft' => "aucun",
                            'item' => "x8 alcool de soin++"
                        ],
                        17 => [
                            'craft' => "aucun",
                            'item' => "x1 clé fortune"
                        ],
                        18 => [
                            'craft' => "aucun",
                            'item' => "50 000$"
                        ],
                        19 => [
                            'craft' => "aucun",
                            'item' => "x64 dynamites en platine"
                        ],
                        20 => [
                            'craft' => "bottes, épée, or",
                            'item' => "aucun"
                        ],
                        21 => [
                            'craft' => "aucun",
                            'item' => "300 000$"
                        ],
                        22 => [
                            'craft' => "aucun",
                            'item' => "500 000$"
                        ],
                        23 => [
                            'craft' => "aucun",
                            'item' => "600 000$"
                        ],
                        24 => [
                            'craft' => "aucun",
                            'item' => "800 000$"
                        ],
                        25 => [
                            'craft' => "aucun",
                            'item' => "1 000 000$"
                        ],
                    ];

                    $i = 2;
                    $lvl = $jobs[JobsManager::HUNTER]['lvl'];
                    $pagination = [0 => []];
                    $page = 0;
                    while ($i !== 26) {

                        if ($i === 10) {
                            $page++;
                        }
                        if ($i === 20) {
                            $page++;
                        }

                        $pagination[$page][] = new Button("niv_§z$i", new Image("textures/goldrush/nineslice/jobs_rond_bg"));

                        if ($lvl >= $i) {
                            $pagination[$page][] = new Button("rcp_§z" . $data[$i]['item'], new Image("textures/goldrush/nineslice/jobs_bg"));
                            $pagination[$page][] = new Button("crt_" . $data[$i]['craft'], new Image("textures/goldrush/nineslice/jobs_bg"));
                        } else {
                            $pagination[$page][] = new Button("rcp_§z" . $data[$i]['item'], new Image("textures/goldrush/nineslice/jobs_no_bg"));
                            $pagination[$page][] = new Button("crt_" . $data[$i]['craft'], new Image("textures/goldrush/nineslice/jobs_no_bg"));
                        }
                        $i++;
                    }
                    break;
                case 'bucheron':
                    $title = 'bucheron';
                    $data = [
                        2 => [
                            'craft' => 'aucun',
                            'item' => 'x1 hache du bucheron'
                        ],
                        3 => [
                            'craft' => 'aucun',
                            'item' => "x64 bouteilles d'xp"
                        ],
                        4 => [
                            'craft' => "aucun",
                            'item' => "30 000 $"
                        ],
                        5 => [
                            'craft' => "hache, jambières émeraude",
                            'item' => "aucun"
                        ],
                        6 => [
                            'craft' => "aucun",
                            'item' => "x1 clé rare"
                        ],
                        7 => [
                            'craft' => "aucun",
                            'item' => "x1 coffre en améthyste"
                        ],
                        8 => [
                            'craft' => "aucun",
                            'item' => "x16 dynamites anti-liquide"
                        ],
                        9 => [
                            'craft' => "aucun",
                            'item' => "60 000$"
                        ],
                        10 => [
                            'craft' => "hache, jambières améthyste",
                            'item' => "aucun"
                        ],
                        11 => [
                            'craft' => "aucun",
                            'item' => "x256 bouteilles d'xp"
                        ],
                        12 => [
                            'craft' => "aucun",
                            'item' => "120 000$"
                        ],
                        13 => [
                            'craft' => "aucun",
                            'item' => "x320 bouteilles d'xp"
                        ],
                        14 => [
                            'craft' => "aucun",
                            'item' => "x128 graines d'obsidiennes"
                        ],
                        15 => [
                            'craft' => "hache, jambières platine",
                            'item' => "aucun"
                        ],
                        16 => [
                            'craft' => "aucun",
                            'item' => "x16 alcool du mineur++"
                        ],
                        17 => [
                            'craft' => "aucun",
                            'item' => "x1 clé fortune"
                        ],
                        18 => [
                            'craft' => "aucun",
                            'item' => "x2 fleur de camouflage"
                        ],
                        19 => [
                            'craft' => "bottes, épée or",
                            'item' => "x1 clé mythique"
                        ],
                        20 => [
                            'craft' => "hache, jambières or",
                            'item' => "aucun"
                        ],
                        21 => [
                            'craft' => "aucun",
                            'item' => "300 000$"
                        ],
                        22 => [
                            'craft' => "aucun",
                            'item' => "500 000$"
                        ],
                        23 => [
                            'craft' => "aucun",
                            'item' => "600 000$"
                        ],
                        24 => [
                            'craft' => "aucun",
                            'item' => "800 000$"
                        ],
                        25 => [
                            'craft' => "aucun",
                            'item' => "1 000 000$"
                        ],
                    ];

                    $i = 2;
                    $lvl = $jobs[JobsManager::LUMBERJACK]['lvl'];
                    $pagination = [0 => []];
                    $page = 0;
                    while ($i !== 26) {

                        if ($i === 10) {
                            $page++;
                        }
                        if ($i === 20) {
                            $page++;
                        }

                        $pagination[$page][] = new Button("niv_§z$i", new Image("textures/goldrush/nineslice/jobs_rond_bg"));

                        if ($lvl >= $i) {
                            $pagination[$page][] = new Button("rcp_§z" . $data[$i]['item'], new Image("textures/goldrush/nineslice/jobs_bg"));
                            $pagination[$page][] = new Button("crt_" . $data[$i]['craft'], new Image("textures/goldrush/nineslice/jobs_bg"));
                        } else {
                            $pagination[$page][] = new Button("rcp_§z" . $data[$i]['item'], new Image("textures/goldrush/nineslice/jobs_no_bg"));
                            $pagination[$page][] = new Button("crt_" . $data[$i]['craft'], new Image("textures/goldrush/nineslice/jobs_no_bg"));
                        }
                        $lvl--;
                        $i++;
                    }
                    break;
            }


            $btn[] = new Button("see_lvl", new Image("textures/goldrush/nineslice/jobs_rond_bg"));
            $btn[] = new Button("see_récompense", new Image("textures/goldrush/nineslice/jobs_rond_bg"));
            $btn[] = new Button("see_craft débloqué", new Image("textures/goldrush/nineslice/jobs_rond_bg"));


            if ($pageChoice === 2) {
                $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            } elseif ($pageChoice === 0) {
                $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
            } else {
                $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
                $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
            }


            //mineur,farmer,bucheron,hunter

            $form = new MenuForm("SEE_JOB_$choice", "", array_merge($pagination[$pageChoice], $btn), function (Player $player, Button $btn) use ($pageChoice, $choice): void {
                if ($btn->getText() === "left_arrow") {
                    if ($pageChoice === 0) {
                        self::sendJobsInfo($player, $choice, 0);
                    } else self::sendJobsInfo($player, $choice, $pageChoice - 1);
                } elseif ($btn->getText() === "right_arrow") {
                    if ($pageChoice === 2) {
                        self::sendJobsInfo($player, $choice, 2);
                    } else self::sendJobsInfo($player, $choice, $pageChoice + 1);
                }
            });
            $player->sendForm($form);
        });
    }

    public static function sendMonture(Player $player): void
    {
        $btn = [];

        $btn[] = new Button("a_i", new Image("textures/items/monture_cuivre"));
        $btn[] = new Button("a_l_§z50k");
        $btn[] = new Button("a_l_§zspeed 1");
        $btn[] = new Button("a_s");

        $btn[] = new Button("b_i", new Image("textures/items/monture_emerald"));
        $btn[] = new Button("b_l_§z200k");
        $btn[] = new Button("b_l_§zspeed 2");
        $btn[] = new Button("b_s");

        $btn[] = new Button("c_i", new Image("textures/items/monture_amethyst"));
        $btn[] = new Button("c_l_§z400k");
        $btn[] = new Button("c_l_§zspeed 3");
        $btn[] = new Button("c_s");

        $btn[] = new Button("d_i", new Image("textures/items/monture_platine"));
        $btn[] = new Button("d_l_§z2M");
        $btn[] = new Button("d_l_§zspeed 4");
        $btn[] = new Button("d_s");

        $btn[] = new Button("e_i", new Image("textures/items/monture_gold"));
        $btn[] = new Button("e_l_§z5M");
        $btn[] = new Button("e_l_§zspeed 5");
        $btn[] = new Button("e_s");

        $form = new MenuForm("MONTURES", $args[1] ?? "", $btn, function (Player $player, Button $button): void {


            $choice = [3, 7, 11, 15, 19];
            if (!in_array($button->getValue(), $choice)) return;


            $arrayPrice = [
                3 => 50000,
                7 => 200000,
                11 => 400000,
                15 => 2000000,
                19 => 5000000,
            ];


            $arrayName = [
                3 => "monture en cuivre",
                7 => "monture en émeraude",
                11 => "monture en améthyste",
                15 => "monture en platine",
                19 => "monture en or",
            ];


            $arrayIds = [
                3 => Ids::HORSE_ARMOR_COPPER,
                7 => Ids::HORSE_ARMOR_EMERALD,
                11 => Ids::HORSE_ARMOR_AMETHYST,
                15 => Ids::HORSE_ARMOR_PLATINUM,
                19 => Ids::HORSE_ARMOR_GOLD,
            ];


            $buttonValues = $button->getValue();
            $name = $arrayName[$buttonValues];
            $price = $arrayPrice[$buttonValues];
            $id = $arrayIds[$buttonValues];


            Main::getInstance()->getEconomyManager()->getMoneySQL($player, function (CustomPlayer $player, int $money) use ($price, $name, $id): void {
                if ($money < $price) {
                    $player->sendMessage(Messages::message("§cVous n'avez pas assez d'argent pour acheter cette monture."));
                    $player->sendErrorSound();
                    return;
                }

                $item = CustomiesItemFactory::getInstance()->get($id);
                if (!$player->getInventory()->canAddItem($item)) {
                    $player->sendMessage(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                    $player->sendErrorSound();
                    return;
                }


                $item->getNamedTag()->setString("xuid", $player->getXuid());
                $player->getInventory()->addItem($item->setCustomName("Monture de : " . $player->getName()));
                $player->sendSuccessSound();
                Main::getInstance()->getEconomyManager()->removeMoney($player, $price);

                $player->sendMessage(Messages::message("§fVoici la §6" . $name));
            });
        });
        $player->sendForm($form);
    }
}