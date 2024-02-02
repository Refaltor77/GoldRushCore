<?php

namespace core\commands\executors;

use core\api\form\elements\Button;
use core\api\form\elements\Image;
use core\api\form\MenuForm;
use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\tasks\Teleport;
use core\utils\SkinUtils;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;

class Cosmetics extends Executor
{
    public function __construct(string $name = 'cosmetics', string $description = "Voir vos cosmetiques en jeu", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $this->sendMenuCosmetic($sender);
    }


    public function sendMenuCosmetic(Player $sender): void {
        $sender->sendForm(new MenuForm("COSMETIC_CATEGORY", "§6Une envie de se dinstinguer des autres joueurs ? §fHésite pas à passer sur notre boutique §6goldrushmc.fun §fpour découvrir toute notre gamme de cosmétique trop cooooool !", [
            new Button("Hats"),
            new Button("Bags"),
            new Button("Capes"),
            new Button("Costumes"),
            new Button("Others"),
            new Button("Pets")
        ], function (Player $player, Button $button): void {
            $type = match ($button->getText()) {
                "Hats" => "HEAD",
                "Bags" => "BACK",
                "Costumes" => "COST",
                "Capes" => "CAPE",
                "Others" => "OTHE",
                "Pets" => "PETS"
            };

            if ($type === 'PETS') {
                $player->sendMessage(Messages::message("§cCe type de cosmétique n'est pas encore disponible sur §4GoldRush§c."));
                return;
            }

            if ($type === 'CAPE' || $type === 'PETS') {
                $player->sendMessage(Messages::message("§cEn raison d'un soucie technique, ce type de cosmétique n'est pas encore disponible, il arrivera en cours de semaine."));
                return;
            }

            $this->sendCosmeticChoice($player, $type);
        }));
    }



    public function sendOtherPage(Player $sender, string $type, int $pageChoiceRay, array $cosmetics): void {
        $allCosmetics = $cosmetics;
        Main::getInstance()->getCosmeticManager()->loadCosmetics($sender,
            function (Player $player, array $cosmetics) use ($type, $pageChoiceRay, $allCosmetics) : void {
                $typeChoice = match ($type) {
                    "HEAD" => "head",
                    "BACK" => "back",
                    "CAPE" => "cape",
                    "COST" => "costumes",
                    "OTHE" => "other",
                    "PETS" => "pets"
                };

                $buttons = [];
                $i = 0;
                $pagination = 0;
                $allPage = 1;
                $page = [0 => []];
                foreach ($cosmetics[$typeChoice] as $cosmeticName) {
                    if ($i >= 18) {
                        $pagination++;
                        $allPage++;
                        $i = 0;
                    }
                    $button = new Button("cosmet_" . $cosmeticName, new Image("textures/renders/" . $cosmeticName));
                    $page[$pagination][] = $button;
                    $i++;
                }

                $pageCount = count(array_keys($page));
                $buttons = $page[$pageChoiceRay - 1];
                $cosmetics = $page;

                $buttonsUtils = [
                    new Button("pseudo_" . $player->getName()),
                    new Button("page_$pageChoiceRay / $allPage"),
                    new Button("right"),
                    new Button("left"),
                    new Button("interog"),
                    new Button("retour"),
                ];

                $btn = [];
                foreach ($buttons as $button) {
                    $btn[] = $button;
                }
                foreach ($buttonsUtils as $button) {
                    $btn[] = $button;
                }
                $player->sendForm(new MenuForm("COSMETIC_CHOICE_" . $type, "§6Une envie de se dinstinguer des autres joueurs ? §fHésite pas à passer sur notre boutique §6goldrushmc.fun §fpour découvrir toute notre gamme de cosmétique trop cooooool !", $btn,
                    function (Player $player, Button $button) use ($buttons, $page, $cosmetics, $allPage, $type, $pageChoiceRay, $typeChoice) : void {
                        # handle buttons utils
                        switch ($button->getText()) {
                            case 'right':
                                if (($pageChoiceRay + 1) <= $allPage) {
                                    $this->sendOtherPage($player, $type, $pageChoiceRay + 1, $cosmetics);
                                    return;
                                } else {
                                    $player->sendMessage(Messages::message("§cVous avez atteint la limite de votre inventaire de cosmétique."));
                                    $player->sendErrorSound();
                                    return;
                                }
                            case 'left':
                                if ($pageChoiceRay === 1) {
                                    $player->sendMessage(Messages::message("§cVous êtes déjà à la première page."));
                                    $player->sendErrorSound();
                                } else {
                                    if (($pageChoiceRay - 1) <= $allPage) {
                                        $this->sendOtherPage($player, $type, $pageChoiceRay - 1, $cosmetics);
                                        return;
                                    } else {
                                        $player->sendMessage(Messages::message("§cVous êtes déjà à la première page."));
                                        $player->sendErrorSound();
                                        return;
                                    }
                                }
                                return;
                            case 'interog':
                                $player->sendMessage(Messages::message("§fPréparation à la téléportation."));
                                Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Teleport($player,
                                    new Position(55, 85, -29, Server::getInstance()->getWorldManager()->getDefaultWorld())), 20);
                                return;
                        }


                        if ($typeChoice === 'cape') {
                            if (substr($button->getText(),0, 7) === 'cosmet_') {
                                $cosmeticName = substr($button->getText(), 7);
                                $this->sendOtherPage($player, $type, 1, $cosmetics);
                                $cape = $this->createCape($cosmeticName);
                                if (is_null($cape)) {
                                    return;
                                }
                                $player->setSkin(new \pocketmine\entity\Skin(
                                    $player->getSkin()->getSkinId(),
                                    $player->getSkin()->getSkinData(),
                                    $cape,
                                    $player->getSkin()->getGeometryName(),
                                    $player->getSkin()->getGeometryData()
                                ));
                            }
                        } else {
                            if (substr($button->getText(),0, 7) === 'cosmet_') {
                                $cosmeticName = substr($button->getText(), 7);
                                Main::getInstance()->getCosmeticManager()->setCosmeticInPlayer($player, $cosmeticName);
                                $this->sendOtherPage($player, $type, 1, $cosmetics);
                            }
                        }
                    }
                ));
            });
    }


    public function createCape(string $capeName): ?string {
        $path = $this->getPlugin()->getDataFolder() . "cosmetic/$capeName.png";
        $img = @imagecreatefrompng($path);
        if (!$img) return null;
        $bytes = '';
        $l = (int) @getimagesize($path)[1];

        for($y = 0; $y < $l; $y++) {
            for($x = 0; $x < 64; $x++) {
                $rgba = @imagecolorat($img, $x, $y);
                $a = ((~((int)($rgba >> 24))) << 1) & 0xff;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }

        @imagedestroy($img);

        return $bytes;
    }

    public function sendCosmeticChoice(Player $sender, string $type): void {


        /*
        'head' => [],
        'back' => [],
        'cape' => [],
        'costumes' => [],
        'other' => [],
        'pets' => []
         */
        Main::getInstance()->getCosmeticManager()->loadCosmetics($sender,
            function (Player $player, array $cosmetics) use ($type) : void {
            $typeChoice = match ($type) {
                "HEAD" => "head",
                "BACK" => "back",
                "CAPE" => "cape",
                "COST" => "costumes",
                "OTHE" => "other",
                "PETS" => "pets"
            };


            $buttons = [];
            $i = 0;
            $pagination = 0;
            $page = [0 => []];
            $allPage = 1;
            foreach ($cosmetics[$typeChoice] as $cosmeticName) {
                if ($i >= 18) {
                    $pagination++;
                    $allPage++;
                    $i = 0;
                }
                $button = new Button("cosmet_" . $cosmeticName, new Image("textures/renders/" . $cosmeticName));
                $page[$pagination][] = $button;
                $i++;
            }

            $pageCount = count(array_keys($page));
            $buttons = $page[0];
            $cosmetics = $page;

            $buttonsUtils = [
                new Button("pseudo_" . $player->getName()),
                new Button("page_1 / $allPage"),
                new Button("right"),
                new Button("left"),
                new Button("interog"),
                new Button("retour"),
            ];

            $btn = [];
            foreach ($page[0] as $i => $button) {
                $btn[] = $button;
            }
            foreach ($buttonsUtils as $button) {
                $btn[] = $button;
            }

            $player->sendForm(new MenuForm("COSMETIC_CHOICE_" . $type, "§6Une envie de se dinstinguer des autres joueurs ? §fHésite pas à passer sur notre boutique §6goldrushmc.fun §fpour découvrir toute notre gamme de cosmétique trop cooooool !", $btn,
                function (Player $player, Button $button) use ($buttons, $page, $cosmetics, $allPage, $type, $typeChoice) : void {
                    # handle buttons utils
                    switch ($button->getText()) {
                        case 'right':
                            if ($allPage >= 2) {
                                $this->sendOtherPage($player, $type, 2, $cosmetics);
                                return;
                            } else {
                                $player->sendMessage(Messages::message("§cVous avez atteint la limite de votre inventaire de cosmétique."));
                                $player->sendErrorSound();
                                return;
                            }
                        case 'left':
                            $player->sendMessage(Messages::message("§cVous êtes déjà à la première page."));
                            $player->sendErrorSound();
                            return;
                        case 'interog':
                            $player->sendMessage(Messages::message("§fPréparation à la téléportation."));
                            Main::getInstance()->getScheduler()->scheduleRepeatingTask(new Teleport($player,
                            new Position(55, 85, -29, Server::getInstance()->getWorldManager()->getDefaultWorld())), 20);
                            return;
                        case 'retour':
                            $this->sendMenuCosmetic($player);
                            return;
                    }

                    if ($typeChoice === 'cape') {
                        if (substr($button->getText(),0, 7) === 'cosmet_') {
                            $cosmeticName = substr($button->getText(), 7);
                            $this->sendOtherPage($player, $type, 1, $cosmetics);
                            $cape = $this->createCape($cosmeticName);
                            if (is_null($cape)) {
                                return;
                            }
                            $player->setSkin(new \pocketmine\entity\Skin(
                                $player->getSkin()->getSkinId(),
                                $player->getSkin()->getSkinData(),
                                $cape,
                                $player->getSkin()->getGeometryName(),
                                $player->getSkin()->getGeometryData()
                            ));
                        }
                    } else {
                        if (substr($button->getText(),0, 7) === 'cosmet_') {
                            $cosmeticName = substr($button->getText(), 7);
                            Main::getInstance()->getCosmeticManager()->setCosmeticInPlayer($player, $cosmeticName);
                            $this->sendOtherPage($player, $type, 1, $cosmetics);
                        }
                    }

            }
            ));
        });
    }
}