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
use core\messages\Messages;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class ShopForms
{
    use SoundTrait;

    const CATEGORY_FARM = "farms";
    const CATEGORY_DECO = "deco";
    const CATEGORY_ORES = "ores";
    const CATEGORY_BLOCKS = "blocks";
    const CATEGORY_OTHERS = "others";
    const CATEGORY_UTILS = "utils";
    const CATEGORY_MOBS = "mobs";

    public static function validateButton(Button $button, Player $player): bool
    {
        $arrayBlacklist = [
            "category_blocks",
            "category_farms",
            "category_deco",
            "category_ores",
            "category_others",
            "category_utils",
            "category_mobs",
        ];

        if (in_array($button->getText(), $arrayBlacklist)) {
            $category = explode("_", $button->getText());
            self::sendMainMenuShop($player, $category[1]);
            return false;
        }

        return true;
    }

    public static function sendMainMenuShop(Player $player, string $category = "farms"): void
    {
        switch ($category) {
            case self::CATEGORY_FARM:
                self::sendShopFarm($player);
                break;
            case self::CATEGORY_UTILS:
                self::sendShopUtils($player);
                break;
            case self::CATEGORY_DECO:
                self::sendShopDeco($player);
                break;
            case self::CATEGORY_ORES:
                self::sendShopOres($player);
                break;
            case self::CATEGORY_BLOCKS:
                self::sendShopBlocks($player);
                break;
            case self::CATEGORY_OTHERS:
                self::sendShopOthers($player);
                break;
            case self::CATEGORY_MOBS:
                self::sendShopMobs($player);
                break;
        }
    }

    public static function sendShopFarm(Player $player, int $pageChoice = 1): void
    {
        $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $shop = $config->get('shop');


        $itemsBtn = [0 => []];
        $pagination = [0 => []];
        $i = 0;
        $page = 0;
        foreach ($shop['Farming']['items'] as $index => $values) {
            $name = $values['name'];
            $idString = $values['idMeta'];
            $buy = $values['buy'] ?? 404;
            $sell = $values['sell'] ?? 404;
            $image = $values['image'];


            if ($buy !== 404 && $sell !== 404) {
                $pagination[$page][] = new Button("$name\n§a$buy$ | §c" . $sell . "$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            } elseif ($buy !== 404 && $sell === 404) {
                $pagination[$page][] = new Button("$name\n§a" . $buy . "$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            } elseif ($buy === 404 && $sell !== 404) {
                $pagination[$page][] = new Button("$name\n§c$sell$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            }

            if ($i >= 15) {
                $i = 0;
                $page++;
            }


            $i++;
        }


        if ($pageChoice === 1) {
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
        } elseif ($pageChoice - 1 >= $page) {
            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
        } else {
            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
        }


        $btn[] = new Button("category_blocks", new Image("textures/goldrush/shop/shop_item_blocks"));
        $btn[] = new Button("category_farms", new Image("textures/goldrush/shop/shop_item_farms"));
        $btn[] = new Button("category_deco", new Image("textures/goldrush/shop/shop_item_deco"));
        $btn[] = new Button("category_ores", new Image("textures/goldrush/shop/shop_item_ores"));
        $btn[] = new Button("category_others", new Image("textures/goldrush/shop/shop_item_others"));
        $btn[] = new Button("category_utils", new Image("textures/goldrush/shop/shop_item_utils"));
        $btn[] = new Button("category_mobs", new Image("textures/goldrush/shop/shop_item_mobs"));


        $player->sendForm(new MenuForm("SHOP_farms", "", array_merge($pagination[$pageChoice - 1], $btn), function (Player $player, Button $button) use ($itemsBtn, $pageChoice, $page): void {
            $isItemChoice = self::validateButton($button, $player);


            if ($isItemChoice) {
                if ($button->getText() === 'left_arrow') {
                    if ($pageChoice - 1 <= 1) {
                        self::sendShopFarm($player);
                    } else {
                        self::sendShopFarm($player, $pageChoice - 1);
                    }
                    return;
                } elseif ($button->getText() === 'right_arrow') {
                    if ((($pageChoice + 1) - 1) > $page) {
                        self::sendShopFarm($player, $page + 1);
                    } else  self::sendShopFarm($player, $pageChoice + 1);
                    return;
                }
            }

            if ($isItemChoice) {
                $itemArray = $itemsBtn[$pageChoice - 1][$button->getValue()];
                $idString = $itemArray['id_string'];

                if (str_contains($idString, "goldrush:")) {
                    $item = CustomiesItemFactory::getInstance()->get($idString);
                } else $item = StringToItemParser::getInstance()->parse($idString);

                $sell = $itemArray['sell'];
                $buy = $itemArray['buy'];
                $name = $itemArray['name'];

                if ($sell === 404 && $buy !== 404) {
                    self::shopItem($player, $item, $buy, $name);
                } elseif ($sell !== 404 && $buy === 404) {
                    self::sellItem($player, $item, $sell, $name);
                } elseif ($sell !== 404 && $buy !== 404) {
                    self::sellAndBuy($player, $item, $name, $buy, $sell);
                }
            }
        }));
    }

    public static function shopItem(Player $player, Item $item, int $buy, string $name): void
    {
        $form = new CustomForm("Achat : " . $item->getName(), [
            new Label("§6Acheter §e:§f " . $buy . "\$"),
            new Input("§6Quantité", 0)
        ], function (Player $player, CustomFormResponse $response) use ($item, $buy, $name): void {
            $target = $response->getValues();

            $number = $target[0];

            if (!is_numeric($number) || $number <= 0) {
                $player->sendMessage(Messages::message("§eLe nombre doit être numérique"));
                $player->sendErrorSound();
                return;
            }

            if (!(int)$number || $number <= 0) {
                $player->sendMessage(Messages::message("§eLe nombre doit être numérique"));
                $player->sendErrorSound();
                return;
            }

            if (is_float($number)) {
                $player->sendMessage(Messages::message("§eLe nombre doit être numérique"));
                $player->sendErrorSound();
                return;
            }


            Main::getInstance()->getEconomyManager()->getMoneySQL($player,
                function (Player $player, int $money) use ($target, $buy, $item, $name, $number): void {
                    if ($money < $buy * $target[0]) {
                        $player->sendMessage(Messages::message("§cTu n'a pas assez d'argent."));
                        $player->sendErrorSound();
                        return;
                    }

                    $item->setCount((int)$number);


                    if (!$player->getInventory()->canAddItem($item)) {
                        $player->sendMessage(Messages::message("§cTu n'a pas assez de place dans ton inventaire."));
                        $player->sendErrorSound();
                        return;
                    }

                    $player->getInventory()->addItem($item);
                    $player->sendMessage(self::replace(Messages::message("§aTu à acheté §f{item} §apour §f{price}$ §a!"), [
                        'item' => $name,
                        'price' => (int)$number * $buy
                    ]));
                    Main::getInstance()->getEconomyManager()->removeMoney($player, (int)$number * $buy);
                    $player->sendSuccessSound();
                });
        });

        $player->sendForm($form);
    }

    private static function replace(string $str, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $str = str_replace("{" . $key . "}", $value, $str);
        }
        return $str;
    }

    public static function sellItem(Player $player, Item $item, int $sell, string $name): void
    {

        $player->sendForm(new MenuForm("§6- §fVendre §6-", "", [
            new Button("Vendre à l'unité"),
            new Button("Vendre tout")
        ], function (Player $player, Button $button) use ($name, $sell, $item): void {

            switch ($button->getValue()) {
                case 0:
                    $form = new CustomForm($name, [
                        new Label("§cVendre : " . $sell . "\$"),
                        new Input("§6Quantité", 0)
                    ], function (Player $player, CustomFormResponse $response) use ($item, $sell, $name): void {
                        $target = $response->getValues();

                        $number = $target[0];

                        if (!is_numeric($number) || $number <= 0) {
                            $player->sendMessage(Messages::message("§eLe nombre doit être numérique"));
                            $player->sendErrorSound();
                            return;
                        }

                        if (!(int)$number || $number <= 0) {
                            $player->sendMessage(Messages::message("§eLe nombre doit être numérique"));
                            $player->sendErrorSound();
                            return;
                        }

                        if (is_float($number)) {
                            $player->sendMessage(Messages::message("§eLe nombre doit être numérique"));
                            $player->sendErrorSound();
                            return;
                        }

                        $item->setCount((int)$number);


                        if (!$player->getInventory()->contains($item)) {
                            $player->sendMessage(Messages::message("§cVous n'avez pas assez d'item."));
                            $player->sendErrorSound();
                            return;
                        }

                        $addMoney = (int)$number * $sell;


                        Main::getInstance()->getEconomyManager()->addMoney($player, $addMoney);
                        $player->getInventory()->removeItem($item->setCount((int)$number));
                        $player->sendMessage(self::replace(Messages::message("§aVous avez vendu l'item §f{item} §apour §a{price}§a$ !"), [
                            'item' => $name,
                            'price' => (int)$number * $sell
                        ]));
                        $player->sendSuccessSound();
                    });
                    $player->sendForm($form);
                    break;
                case 1:
                    if (!$player->getInventory()->contains($item)) {
                        $player->sendMessage(Messages::message("§cVous n'avez pas assez l'item."));
                        $player->sendErrorSound();
                        return;
                    }

                    $itemCount = 0;
                    foreach ($player->getInventory()->getContents() as $slot => $itemSearch) {
                        if ($itemSearch->equals($item)) $itemCount += $itemSearch->getCount();
                    }

                    $addMoney = $itemCount * $sell;


                    Main::getInstance()->getEconomyManager()->addMoney($player, $addMoney);
                    $player->getInventory()->remove($item);
                    $player->sendMessage(self::replace(Messages::message("§aVous avez vendu l'item §f{item} §apour §a{price}§a$ !"), [
                        'item' => $name,
                        'price' => $itemCount * $sell
                    ]));
                    $player->sendSuccessSound();
                    break;
            }
        }));


    }

    public static function sellAndBuy(Player $player, Item $item, string $name, int $buy, int $sell): void
    {

        $player->sendForm(new MenuForm("Choix de l'action pour l'item " . $name, "Voulez vous acheter ou vendre cette item ?", [
            new Button("§aAcheter"),
            new Button("§cVendre"),
            new Button("§cTout vendre")
        ], function (Player $player, Button $button) use ($item, $name, $buy, $sell): void {
            switch ($button->getValue()) {
                case 0:
                    self::shopItem($player, $item, $buy, $name);
                    break;
                case 1:
                    self::sellItem($player, $item, $sell, $name);
                    break;
                case 2:
                    if (!$player->getInventory()->contains($item)) {
                        $player->sendMessage(Messages::message("§cVous n'avez pas assez l'item."));
                        $player->sendErrorSound();
                        return;
                    }

                    $itemCount = 0;
                    foreach ($player->getInventory()->getContents() as $slot => $itemSearch) {
                        if ($itemSearch->equals($item)) $itemCount += $itemSearch->getCount();
                    }

                    $addMoney = $itemCount * $sell;


                    Main::getInstance()->getEconomyManager()->addMoney($player, $addMoney);
                    $player->getInventory()->remove($item);
                    $player->sendMessage(self::replace(Messages::message("§aVous avez vendu l'item §f{item} §apour §a{price}§a$ !"), [
                        'item' => $name,
                        'price' => $itemCount * $sell
                    ]));
                    $player->sendSuccessSound();
                    break;
            }
        }));
    }

    public static function sendShopUtils(Player $player, int $pageChoice = 1): void
    {
        $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $shop = $config->get('shop');


        $itemsBtn = [0 => []];
        $pagination = [0 => []];
        $i = 0;
        $page = 0;
        foreach ($shop['Utilitaires']['items'] as $index => $values) {
            $name = $values['name'];
            $idString = $values['idMeta'];
            $buy = $values['buy'] ?? 404;
            $sell = $values['sell'] ?? 404;
            $image = $values['image'];


            if ($buy !== 404 && $sell !== 404) {
                $pagination[$page][] = new Button("$name\n§a$buy$ | §c" . $sell . "$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            } elseif ($buy !== 404 && $sell === 404) {
                $pagination[$page][] = new Button("$name\n§a" . $buy . "$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            } elseif ($buy === 404 && $sell !== 404) {
                $pagination[$page][] = new Button("$name\n§c$sell$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            }

            if ($i >= 15) {
                $i = 0;
                $page++;
            }


            $i++;
        }


        if ($pageChoice === 1 && $pageChoice - 1 < $page) {
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
            $btn[] = new Button("left_arrow", new Image(""));
        } elseif ($pageChoice - 1 >= $page && $pageChoice !== 1) {
            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image(""));
        } elseif (($pageChoice - 1 >= $page) && $pageChoice === 1) {
            $btn[] = new Button("left_arrow", new Image(""));
            $btn[] = new Button("right_arrow", new Image(""));
        } else {
            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
        }


        $btn[] = new Button("category_blocks", new Image("textures/goldrush/shop/shop_item_blocks"));
        $btn[] = new Button("category_farms", new Image("textures/goldrush/shop/shop_item_farms"));
        $btn[] = new Button("category_deco", new Image("textures/goldrush/shop/shop_item_deco"));
        $btn[] = new Button("category_ores", new Image("textures/goldrush/shop/shop_item_ores"));
        $btn[] = new Button("category_others", new Image("textures/goldrush/shop/shop_item_others"));
        $btn[] = new Button("category_utils", new Image("textures/goldrush/shop/shop_item_utils"));
        $btn[] = new Button("category_mobs", new Image("textures/goldrush/shop/shop_item_mobs"));


        $player->sendForm(new MenuForm("SHOP_utils", "", array_merge($pagination[$pageChoice - 1], $btn), function (Player $player, Button $button) use ($itemsBtn, $pageChoice, $page): void {
            $isItemChoice = self::validateButton($button, $player);


            if ($isItemChoice) {
                if ($button->getText() === 'left_arrow') {
                    if ($pageChoice - 1 <= 1) {
                        self::sendShopUtils($player);
                    } else {
                        self::sendShopUtils($player, $pageChoice - 1);
                    }
                    return;
                } elseif ($button->getText() === 'right_arrow') {
                    if ((($pageChoice + 1) - 1) > $page) {
                        self::sendShopUtils($player, $page + 1);
                    } else  self::sendShopUtils($player, $pageChoice + 1);
                    return;
                }
            }

            if ($isItemChoice) {
                $itemArray = $itemsBtn[$pageChoice - 1][$button->getValue()];
                $idString = $itemArray['id_string'];

                if (str_contains($idString, "goldrush:")) {
                    $item = CustomiesItemFactory::getInstance()->get($idString);
                } else $item = StringToItemParser::getInstance()->parse($idString);

                $sell = $itemArray['sell'];
                $buy = $itemArray['buy'];
                $name = $itemArray['name'];

                if ($sell === 404 && $buy !== 404) {
                    self::shopItem($player, $item, $buy, $name);
                } elseif ($sell !== 404 && $buy === 404) {
                    self::sellItem($player, $item, $sell, $name);
                } elseif ($sell !== 404 && $buy !== 404) {
                    self::sellAndBuy($player, $item, $name, $buy, $sell);
                }
            }
        }));
    }

    public static function sendShopDeco(Player $player, int $pageChoice = 1): void
    {
        $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $shop = $config->get('shop');


        $itemsBtn = [0 => []];
        $pagination = [0 => []];
        $i = 0;
        $page = 0;
        foreach ($shop['Deco']['items'] as $index => $values) {
            $name = $values['name'];
            $idString = $values['idMeta'];
            $buy = $values['buy'] ?? 404;
            $sell = $values['sell'] ?? 404;
            $image = $values['image'];


            if ($buy !== 404 && $sell !== 404) {
                $pagination[$page][] = new Button("$name\n§a$buy$ | §c" . $sell . "$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            } elseif ($buy !== 404 && $sell === 404) {
                $pagination[$page][] = new Button("$name\n§a" . $buy . "$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            } elseif ($buy === 404 && $sell !== 404) {
                $pagination[$page][] = new Button("$name\n§c$sell$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            }

            if ($i >= 15) {
                $i = 0;
                $page++;
            }


            $i++;
        }


        if ($pageChoice === 1 && $pageChoice - 1 < $page) {
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
            $btn[] = new Button("left_arrow", new Image(""));
        } elseif ($pageChoice - 1 >= $page && $pageChoice !== 1) {
            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image(""));
        } elseif (($pageChoice - 1 >= $page) && $pageChoice === 1) {
            $btn[] = new Button("left_arrow", new Image(""));
            $btn[] = new Button("right_arrow", new Image(""));
        } else {
            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
        }


        $btn[] = new Button("category_blocks", new Image("textures/goldrush/shop/shop_item_blocks"));
        $btn[] = new Button("category_farms", new Image("textures/goldrush/shop/shop_item_farms"));
        $btn[] = new Button("category_deco", new Image("textures/goldrush/shop/shop_item_deco"));
        $btn[] = new Button("category_ores", new Image("textures/goldrush/shop/shop_item_ores"));
        $btn[] = new Button("category_others", new Image("textures/goldrush/shop/shop_item_others"));
        $btn[] = new Button("category_utils", new Image("textures/goldrush/shop/shop_item_utils"));
        $btn[] = new Button("category_mobs", new Image("textures/goldrush/shop/shop_item_mobs"));


        $player->sendForm(new MenuForm("SHOP_deco", "", array_merge($pagination[$pageChoice - 1], $btn), function (Player $player, Button $button) use ($itemsBtn, $pageChoice, $page): void {
            $isItemChoice = self::validateButton($button, $player);


            if ($isItemChoice) {
                if ($button->getText() === 'left_arrow') {
                    if ($pageChoice - 1 <= 1) {
                        self::sendShopDeco($player);
                    } else {
                        self::sendShopDeco($player, $pageChoice - 1);
                    }
                    return;
                } elseif ($button->getText() === 'right_arrow') {
                    if ((($pageChoice + 1) - 1) > $page) {
                        self::sendShopDeco($player, $page + 1);
                    } else  self::sendShopDeco($player, $pageChoice + 1);
                    return;
                }
            }

            if ($isItemChoice) {
                $itemArray = $itemsBtn[$pageChoice - 1][$button->getValue()];
                $idString = $itemArray['id_string'];

                if (str_contains($idString, "goldrush:")) {
                    $item = CustomiesItemFactory::getInstance()->get($idString);
                } else $item = StringToItemParser::getInstance()->parse($idString);

                $sell = $itemArray['sell'];
                $buy = $itemArray['buy'];
                $name = $itemArray['name'];

                if ($sell === 404 && $buy !== 404) {
                    self::shopItem($player, $item, $buy, $name);
                } elseif ($sell !== 404 && $buy === 404) {
                    self::sellItem($player, $item, $sell, $name);
                } elseif ($sell !== 404 && $buy !== 404) {
                    self::sellAndBuy($player, $item, $name, $buy, $sell);
                }
            }
        }));
    }

    public static function sendShopOres(Player $player, int $pageChoice = 1): void
    {
        $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $shop = $config->get('shop');


        $itemsBtn = [0 => []];
        $pagination = [0 => []];
        $i = 0;
        $page = 0;
        foreach ($shop['Minage']['items'] as $index => $values) {
            $name = $values['name'];
            $idString = $values['idMeta'];
            $buy = $values['buy'] ?? 404;
            $sell = $values['sell'] ?? 404;
            $image = $values['image'];
            


            if ($buy !== 404 && $sell !== 404) {
                $pagination[$page][] = new Button("$name\n§a$buy$ | §c" . $sell . "$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            } elseif ($buy !== 404 && $sell === 404) {
                $pagination[$page][] = new Button("$name\n§a" . $buy . "$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            } elseif ($buy === 404 && $sell !== 404) {
                $pagination[$page][] = new Button("$name\n§c$sell$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            }

            if ($i >= 15) {
                $i = 0;
                $page++;
            }


            $i++;
        }


        if ($pageChoice === 1 && $pageChoice - 1 < $page) {
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
            $btn[] = new Button("left_arrow", new Image(""));
        } elseif ($pageChoice - 1 >= $page && $pageChoice !== 1) {
            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image(""));
        } elseif (($pageChoice - 1 >= $page) && $pageChoice === 1) {
            $btn[] = new Button("left_arrow", new Image(""));
            $btn[] = new Button("right_arrow", new Image(""));
        } else {
            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
        }


        $btn[] = new Button("category_blocks", new Image("textures/goldrush/shop/shop_item_blocks"));
        $btn[] = new Button("category_farms", new Image("textures/goldrush/shop/shop_item_farms"));
        $btn[] = new Button("category_deco", new Image("textures/goldrush/shop/shop_item_deco"));
        $btn[] = new Button("category_ores", new Image("textures/goldrush/shop/shop_item_ores"));
        $btn[] = new Button("category_others", new Image("textures/goldrush/shop/shop_item_others"));
        $btn[] = new Button("category_utils", new Image("textures/goldrush/shop/shop_item_utils"));
        $btn[] = new Button("category_mobs", new Image("textures/goldrush/shop/shop_item_mobs"));


        $player->sendForm(new MenuForm("SHOP_ores", "", array_merge($pagination[$pageChoice - 1], $btn), function (Player $player, Button $button) use ($itemsBtn, $pageChoice, $page): void {
            $isItemChoice = self::validateButton($button, $player);


            if ($isItemChoice) {
                if ($button->getText() === 'left_arrow') {
                    if ($pageChoice - 1 <= 1) {
                        self::sendShopOres($player);
                    } else {
                        self::sendShopOres($player, $pageChoice - 1);
                    }
                    return;
                } elseif ($button->getText() === 'right_arrow') {
                    if ((($pageChoice + 1)) > $page) {
                        self::sendShopOres($player, $page + 1);
                    } else {
                        self::sendShopOres($player, $pageChoice + 1);
                    }
                    return;
                }
            }

            if ($isItemChoice) {
                $itemArray = $itemsBtn[$pageChoice - 1][$button->getValue()];
                $idString = $itemArray['id_string'];

                if (str_contains($idString, "goldrush:")) {
                    $item = CustomiesItemFactory::getInstance()->get($idString);
                } else $item = StringToItemParser::getInstance()->parse($idString);

                $sell = $itemArray['sell'];
                $buy = $itemArray['buy'];
                $name = $itemArray['name'];

                if ($sell === 404 && $buy !== 404) {
                    self::shopItem($player, $item, $buy, $name);
                } elseif ($sell !== 404 && $buy === 404) {
                    self::sellItem($player, $item, $sell, $name);
                } elseif ($sell !== 404 && $buy !== 404) {
                    self::sellAndBuy($player, $item, $name, $buy, $sell);
                }
            }
        }));
    }

    public static function sendShopBlocks(Player $player, int $pageChoice = 1): void
    {
        $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $shop = $config->get('shop');


        $itemsBtn = [0 => []];
        $pagination = [0 => []];
        $i = 0;
        $page = 0;
        foreach ($shop['Blocs']['items'] as $index => $values) {
            $name = $values['name'];
            $idString = $values['idMeta'];
            $buy = $values['buy'] ?? 404;
            $sell = $values['sell'] ?? 404;
            $image = $values['image'];


            if ($buy !== 404 && $sell !== 404) {
                $pagination[$page][] = new Button("$name\n§a$buy$ | §c" . $sell . "$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            } elseif ($buy !== 404 && $sell === 404) {
                $pagination[$page][] = new Button("$name\n§a" . $buy . "$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            } elseif ($buy === 404 && $sell !== 404) {
                $pagination[$page][] = new Button("$name\n§c$sell$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            }

            if ($i >= 15) {
                $i = 0;
                $page++;
            }


            $i++;
        }


        if ($pageChoice === 1 && $pageChoice - 1 < $page) {
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
            $btn[] = new Button("left_arrow", new Image(""));
        } elseif ($pageChoice - 1 >= $page && $pageChoice !== 1) {
            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image(""));
        } elseif (($pageChoice - 1 >= $page) && $pageChoice === 1) {
            $btn[] = new Button("left_arrow", new Image(""));
            $btn[] = new Button("right_arrow", new Image(""));
        } else {
            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
        }


        $btn[] = new Button("category_blocks", new Image("textures/goldrush/shop/shop_item_blocks"));
        $btn[] = new Button("category_farms", new Image("textures/goldrush/shop/shop_item_farms"));
        $btn[] = new Button("category_deco", new Image("textures/goldrush/shop/shop_item_deco"));
        $btn[] = new Button("category_ores", new Image("textures/goldrush/shop/shop_item_ores"));
        $btn[] = new Button("category_others", new Image("textures/goldrush/shop/shop_item_others"));
        $btn[] = new Button("category_utils", new Image("textures/goldrush/shop/shop_item_utils"));
        $btn[] = new Button("category_mobs", new Image("textures/goldrush/shop/shop_item_mobs"));


        $player->sendForm(new MenuForm("SHOP_blocks", "", array_merge($pagination[$pageChoice - 1], $btn), function (Player $player, Button $button) use ($itemsBtn, $pageChoice, $page): void {
            $isItemChoice = self::validateButton($button, $player);


            if ($isItemChoice) {
                if ($button->getText() === 'left_arrow') {
                    if ($pageChoice - 1 <= 1) {
                        self::sendShopBlocks($player);
                    } else {
                        self::sendShopBlocks($player, $pageChoice - 1);
                    }
                    return;
                } elseif ($button->getText() === 'right_arrow') {
                    if ((($pageChoice + 1) - 1) > $page) {
                        self::sendShopBlocks($player, $page + 1);
                    } else  self::sendShopBlocks($player, $pageChoice + 1);
                    return;
                }
            }

            if ($isItemChoice) {
                $itemArray = $itemsBtn[$pageChoice - 1][$button->getValue()];
                $idString = $itemArray['id_string'];

                if (str_contains($idString, "goldrush:")) {
                    $item = CustomiesItemFactory::getInstance()->get($idString);
                } else $item = StringToItemParser::getInstance()->parse($idString);

                $sell = $itemArray['sell'];
                $buy = $itemArray['buy'];
                $name = $itemArray['name'];

                if ($sell === 404 && $buy !== 404) {
                    self::shopItem($player, $item, $buy, $name);
                } elseif ($sell !== 404 && $buy === 404) {
                    self::sellItem($player, $item, $sell, $name);
                } elseif ($sell !== 404 && $buy !== 404) {
                    self::sellAndBuy($player, $item, $name, $buy, $sell);
                }
            }
        }));
    }

    public static function sendShopOthers(Player $player, int $pageChoice = 1): void
    {
        $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $shop = $config->get('shop');


        $itemsBtn = [0 => []];
        $pagination = [0 => []];
        $i = 0;
        $page = 0;
        foreach ($shop['Food']['items'] as $index => $values) {
            $name = $values['name'];
            $idString = $values['idMeta'];
            $buy = $values['buy'] ?? 404;
            $sell = $values['sell'] ?? 404;
            $image = $values['image'];


            if ($buy !== 404 && $sell !== 404) {
                $pagination[$page][] = new Button("$name\n§a$buy$ | §c" . $sell . "$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            } elseif ($buy !== 404 && $sell === 404) {
                $pagination[$page][] = new Button("$name\n§a" . $buy . "$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            } elseif ($buy === 404 && $sell !== 404) {
                $pagination[$page][] = new Button("$name\n§c$sell$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            }

            if ($i >= 15) {
                $i = 0;
                $page++;
            }


            $i++;
        }


        if ($pageChoice === 1 && $pageChoice - 1 < $page) {
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
            $btn[] = new Button("left_arrow", new Image(""));
        } elseif ($pageChoice - 1 >= $page && $pageChoice !== 1) {
            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image(""));
        } elseif (($pageChoice - 1 >= $page) && $pageChoice === 1) {
            $btn[] = new Button("left_arrow", new Image(""));
            $btn[] = new Button("right_arrow", new Image(""));
        } else {
            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
        }


        $btn[] = new Button("category_blocks", new Image("textures/goldrush/shop/shop_item_blocks"));
        $btn[] = new Button("category_farms", new Image("textures/goldrush/shop/shop_item_farms"));
        $btn[] = new Button("category_deco", new Image("textures/goldrush/shop/shop_item_deco"));
        $btn[] = new Button("category_ores", new Image("textures/goldrush/shop/shop_item_ores"));
        $btn[] = new Button("category_others", new Image("textures/goldrush/shop/shop_item_others"));
        $btn[] = new Button("category_utils", new Image("textures/goldrush/shop/shop_item_utils"));
        $btn[] = new Button("category_mobs", new Image("textures/goldrush/shop/shop_item_mobs"));


        $player->sendForm(new MenuForm("SHOP_others", "", array_merge($pagination[$pageChoice - 1], $btn), function (Player $player, Button $button) use ($itemsBtn, $pageChoice, $page): void {
            $isItemChoice = self::validateButton($button, $player);


            if ($isItemChoice) {
                if ($button->getText() === 'left_arrow') {
                    if ($pageChoice - 1 <= 1) {
                        self::sendShopOthers($player);
                    } else {
                        self::sendShopOthers($player, $pageChoice - 1);
                    }
                    return;
                } elseif ($button->getText() === 'right_arrow') {
                    if ((($pageChoice + 1) - 1) > $page) {
                        self::sendShopOthers($player, $page + 1);
                    } else  self::sendShopOthers($player, $pageChoice + 1);
                    return;
                }
            }

            if ($isItemChoice) {
                $itemArray = $itemsBtn[$pageChoice - 1][$button->getValue()];
                $idString = $itemArray['id_string'];

                if (str_contains($idString, "goldrush:")) {
                    $item = CustomiesItemFactory::getInstance()->get($idString);
                } else $item = StringToItemParser::getInstance()->parse($idString);

                $sell = $itemArray['sell'];
                $buy = $itemArray['buy'];
                $name = $itemArray['name'];

                if ($sell === 404 && $buy !== 404) {
                    self::shopItem($player, $item, $buy, $name);
                } elseif ($sell !== 404 && $buy === 404) {
                    self::sellItem($player, $item, $sell, $name);
                } elseif ($sell !== 404 && $buy !== 404) {
                    self::sellAndBuy($player, $item, $name, $buy, $sell);
                }
            }
        }));
    }

    public static function sendShopMobs(Player $player, int $pageChoice = 1): void
    {
        $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $shop = $config->get('shop');


        $itemsBtn = [0 => []];
        $pagination = [0 => []];
        $i = 0;
        $page = 0;
        foreach ($shop['Loots']['items'] as $index => $values) {
            $name = $values['name'];
            $idString = $values['idMeta'];
            $buy = $values['buy'] ?? 404;
            $sell = $values['sell'] ?? 404;
            $image = $values['image'];


            if ($buy !== 404 && $sell !== 404) {
                $pagination[$page][] = new Button("$name\n§a$buy$ | §c" . $sell . "$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            } elseif ($buy !== 404 && $sell === 404) {
                $pagination[$page][] = new Button("$name\n§a" . $buy . "$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            } elseif ($buy === 404 && $sell !== 404) {
                $pagination[$page][] = new Button("$name\n§c$sell$", new Image($image));
                $itemsBtn[$page][] = [
                    'id_string' => $idString,
                    'buy' => $buy,
                    'sell' => $sell,
                    "name" => $name
                ];
            }

            if ($i >= 15) {
                $i = 0;
                $page++;
            }


            $i++;
        }


        if ($pageChoice === 1 && $pageChoice - 1 < $page) {
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
            $btn[] = new Button("left_arrow", new Image(""));
        } elseif ($pageChoice - 1 >= $page && $pageChoice !== 1) {
            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image(""));
        } elseif (($pageChoice - 1 >= $page) && $pageChoice === 1) {
            $btn[] = new Button("left_arrow", new Image(""));
            $btn[] = new Button("right_arrow", new Image(""));
        } else {
            $btn[] = new Button("left_arrow", new Image("textures/goldrush/left_arrow"));
            $btn[] = new Button("right_arrow", new Image("textures/goldrush/right_arrow"));
        }


        $btn[] = new Button("category_blocks", new Image("textures/goldrush/shop/shop_item_blocks"));
        $btn[] = new Button("category_farms", new Image("textures/goldrush/shop/shop_item_farms"));
        $btn[] = new Button("category_deco", new Image("textures/goldrush/shop/shop_item_deco"));
        $btn[] = new Button("category_ores", new Image("textures/goldrush/shop/shop_item_ores"));
        $btn[] = new Button("category_others", new Image("textures/goldrush/shop/shop_item_others"));
        $btn[] = new Button("category_utils", new Image("textures/goldrush/shop/shop_item_utils"));
        $btn[] = new Button("category_mobs", new Image("textures/goldrush/shop/shop_item_mobs"));


        $player->sendForm(new MenuForm("SHOP_mobs", "", array_merge($pagination[$pageChoice - 1], $btn), function (Player $player, Button $button) use ($itemsBtn, $pageChoice, $page): void {
            $isItemChoice = self::validateButton($button, $player);


            if ($isItemChoice) {
                if ($button->getText() === 'left_arrow') {
                    if ($pageChoice - 1 <= 1) {
                        self::sendShopMobs($player);
                    } else {
                        self::sendShopMobs($player, $pageChoice - 1);
                    }
                    return;
                } elseif ($button->getText() === 'right_arrow') {
                    if ((($pageChoice + 1)) > $page) {
                        self::sendShopMobs($player, $pageChoice);
                    } else self::sendShopMobs($player, $pageChoice + 1);
                    return;
                }
            }

            if ($isItemChoice) {
                $itemArray = $itemsBtn[$pageChoice - 1][$button->getValue()];
                $idString = $itemArray['id_string'];

                if (str_contains($idString, "goldrush:")) {
                    $item = CustomiesItemFactory::getInstance()->get($idString);
                } else $item = StringToItemParser::getInstance()->parse($idString);

                $sell = $itemArray['sell'];
                $buy = $itemArray['buy'];
                $name = $itemArray['name'];

                if ($sell === 404 && $buy !== 404) {
                    self::shopItem($player, $item, $buy, $name);
                } elseif ($sell !== 404 && $buy === 404) {
                    self::sellItem($player, $item, $sell, $name);
                } elseif ($sell !== 404 && $buy !== 404) {
                    self::sellAndBuy($player, $item, $name, $buy, $sell);
                }
            }
        }));
    }
}