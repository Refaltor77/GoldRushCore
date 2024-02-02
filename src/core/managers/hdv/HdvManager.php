<?php

namespace core\managers\hdv;

use core\api\form\elements\Button;
use core\api\form\elements\Image;
use core\api\form\MenuForm;
use core\api\form\ModalForm;
use core\api\gui\DoubleChestInventory;
use core\async\RequestAsync;
use core\Main;
use core\managers\Manager;
use core\messages\Messages;
use core\settings\Ids;
use core\sql\SQL;
use core\utils\Utils;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\sound\ClickSound;
use pocketmine\world\sound\XpLevelUpSound;

class HdvManager extends Manager
{
    public array $cache;

    public function __construct(Main $main)
    {
        SQL::query("CREATE TABLE IF NOT EXISTS `hdv_products` (`uuid` VARCHAR(255), `serialized` TEXT);");

        parent::__construct($main);
    }

    public function displayProducts(Player $player): void
    {
        SQL::async(static function(RequestAsync $async, \mysqli $db): void {
            $query = $db->query("SELECT * FROM `hdv_products`;");
            $result = $query->fetch_all(MYSQLI_ASSOC);
            $arrayQueried = [];

            foreach ($result as $index => $value) {
                $arrayQueried[$value['uuid']] = unserialize(base64_decode($value['serialized']));
            }

            $async->setResult($arrayQueried);
        }, static function(RequestAsync $async) use ($player): void {
            $items = $async->getResult();
            if (!$player->isConnected()) return;

            $xuid = $player->getXuid();
            $content = ['0' => []];
            $pagination = 0;
            $i = 1;
            $arrayQueried = [];
            foreach ($items as $uuid => $array) {
                if ($xuid === $array["xuid"]) $arrayQueried[$uuid] = $array;
            }

            foreach ($arrayQueried as $uuid => $array) {
                $item = Utils::unserializeItem($array['item']);

                if ($i === 25) {
                    $i = 1;
                    $pagination++;
                }

                $item->setLore(["", "§7§oPrix: §r§6" . $array["price"] . "$"]);
                $nbt = $item->getNamedTag();
                $nbt->setInt('price', (int)$array["price"]);
                $nbt->setString('xuidSeller', (string)$array["xuid"]);
                $nbt->setString('uniqueId', (string)$uuid);
                $nbt->setString('basic_lore', base64_encode(serialize($array['lore_basic'])));
                $content[strval($pagination)][] = $item;
                $i++;
            }

            $inv = new DoubleChestInventory();
            $inv->setItem(24, CustomiesItemFactory::getInstance()->get(Ids::ARROW_LEFT));
            $inv->setItem(27, CustomiesItemFactory::getInstance()->get(Ids::INTEROG));
            $inv->setItem(28, CustomiesItemFactory::getInstance()->get(Ids::CHEST)->setCustomName("Retour HDV"));
            $inv->setItem(31, CustomiesItemFactory::getInstance()->get(Ids::ARROW_RIGHT));


            $inv->setViewOnly(true);

            $inv->setClickCallback(function (Player $player, Inventory $inv, Item $source, Item $target, int $slot) use ($content) {
                $player->getWorld()->addSound($player->getEyePos(), new ClickSound(), [$player]);
                if ($slot === 24) {
                    if (($player->hdv - 1) < 0) {
                    } else {
                        $player->hdv -= 1;
                        $inv->clearAll();
                        $inv->setItem(24, CustomiesItemFactory::getInstance()->get(Ids::ARROW_LEFT));
                        $inv->setItem(27, CustomiesItemFactory::getInstance()->get(Ids::INTEROG));
                        $inv->setItem(28, CustomiesItemFactory::getInstance()->get(Ids::CHEST)->setCustomName("Retour HDV"));
                        $inv->setItem(31, CustomiesItemFactory::getInstance()->get(Ids::ARROW_RIGHT));
                        $content = array_reverse($content[strval($player->hdv)]);
                        foreach ($content as $item) {
                            $inv->addItem($item);
                        }
                    }
                } elseif ($slot === 31) {
                    $hdv = $player->hdv;
                    if (isset($content[strval($hdv + 1)])) {
                        $player->hdv += 1;
                        $inv->clearAll();
                        $inv->setItem(24, CustomiesItemFactory::getInstance()->get(Ids::ARROW_LEFT));
                        $inv->setItem(27, CustomiesItemFactory::getInstance()->get(Ids::INTEROG));
                        $inv->setItem(28, CustomiesItemFactory::getInstance()->get(Ids::CHEST)->setCustomName("Retour HDV"));
                        $inv->setItem(31, CustomiesItemFactory::getInstance()->get(Ids::ARROW_RIGHT));
                        $content = array_reverse($content[strval($player->hdv)]);
                        foreach ($content as $item) $inv->addItem($item);
                    }
                } elseif ($slot === 28) {
                    $player->removeCurrentWindow();
                    Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                        Main::getInstance()->getHdvManager()->display($player);
                    }), 10);
                }elseif ($slot === 27) {
                    $player->removeCurrentWindow();
                    Main::getInstance()->getHdvManager()->showWhatHdv($player);
                } else {
                    $item = $source;
                    $seller = $item->getNamedTag()->getString('xuidSeller', 'null');
                    if ($seller !== 'null') {
                        $player->removeCurrentWindow();
                        Main::getInstance()->getHdvManager()->deleteProduct($player, $item, $inv);
                    }
                }
            });
            $inv->setCloseCallback(function (Player $player, Inventory $inventory) {
                $player->hdv = 0;
            });
            foreach ($content['0'] as $item) $inv->addItem($item);
            $inv->setName("HDV");
            $inv->send($player);
        });
    }

    public function display(Player $player, bool $isAdmin = false): void
    {
        if (!$player->hasSendHdv) {
            $player->hasSendHdv = true;


            SQL::async(static function (RequestAsync $thread, \mysqli $db) : void {
                $query = $db->query("SELECT * FROM `hdv_products`;");
                $result = $query->fetch_all(MYSQLI_ASSOC);
                $arrayQueried = [];

                foreach ($result as $index => $value) {
                    $arrayQueried[$value['uuid']] = unserialize(base64_decode($value['serialized']));
                }

                $items = array_reverse($arrayQueried, true);

                $thread->setResult($items);
            }, static function (RequestAsync $thread) use ($player, $isAdmin): void {
                if (!$player->isConnected()) return;
                $items = $thread->getResult();
                $content = ['0' => []];
                $pagination = 0;
                $i = 1;
                $slotsProducts = [10, 11, 12, 13, 14, 15, 16, 19, 20, 21, 22, 23, 24, 25, 29, 30, 31, 32, 33, 34];
                foreach ($items as $id => $array) {
                    $item = Utils::unserializeItem($array['item']);
                    if ($item instanceof Item) {
                        if ($i === 25) {
                            $i = 1;
                            $pagination++;
                        }

                        $item->setLore(["", "§7§oVendeur: §r§6" . Main::getInstance()->getDataManager()->getNameByXuid($array["xuid"]) ?? '404',
                            "§7§oPrix: §r§6" . $array["price"] . "$"]);
                        $nbt = $item->getNamedTag();
                        $nbt->setInt('price', (int)$array["price"]);
                        $nbt->setString('xuidSeller', (string)$array["xuid"]);
                        $nbt->setString('uniqueId', (string)$id);
                        $nbt->setString('basic_lore', base64_encode(serialize($array['lore_basic'])));
                        $content[strval($pagination)][] = $item;
                        $i++;
                    }
                }
                $inv = new DoubleChestInventory();
                $inv->setViewOnly(true);
                $inv->setItem(24, CustomiesItemFactory::getInstance()->get(Ids::ARROW_LEFT));
                $inv->setItem(27, CustomiesItemFactory::getInstance()->get(Ids::INTEROG));
                $inv->setItem(28, CustomiesItemFactory::getInstance()->get(Ids::CHEST)->setCustomName("Retour HDV"));
                $inv->setItem(31, CustomiesItemFactory::getInstance()->get(Ids::ARROW_RIGHT));
                $inv->setClickCallback(function (Player $player, Inventory $inv, Item $source, Item $target, int $slot) use ($isAdmin, $content) {
                    $player->getWorld()->addSound($player->getEyePos(), new ClickSound(), [$player]);
                    if ($slot === 24) {
                        if (($player->hdv - 1) < 0) {
                        } else {
                            $player->hdv -= 1;
                            $inv->clearAll();
                            $inv->setItem(24, CustomiesItemFactory::getInstance()->get(Ids::ARROW_LEFT));
                            $inv->setItem(27, CustomiesItemFactory::getInstance()->get(Ids::INTEROG));
                            $inv->setItem(28, CustomiesItemFactory::getInstance()->get(Ids::CHEST)->setCustomName("Retour HDV"));
                            $inv->setItem(31, CustomiesItemFactory::getInstance()->get(Ids::ARROW_RIGHT));
                            foreach ($content[strval($player->hdv)] as $item) {
                                $inv->addItem($item);
                            }
                        }
                    } elseif ($slot === 31) {
                        $hdv = $player->hdv;
                        if (isset($content[strval($hdv + 1)])) {
                            $player->hdv += 1;
                            $inv->clearAll();
                            $inv->setItem(24, CustomiesItemFactory::getInstance()->get(Ids::ARROW_LEFT));
                            $inv->setItem(27, CustomiesItemFactory::getInstance()->get(Ids::INTEROG));
                            $inv->setItem(28, CustomiesItemFactory::getInstance()->get(Ids::CHEST)->setCustomName("Retour HDV"));
                            $inv->setItem(31, CustomiesItemFactory::getInstance()->get(Ids::ARROW_RIGHT));
                            $content = array_reverse($content[strval($player->hdv)]);
                            foreach ($content as $item) $inv->addItem($item);
                        }
                    } elseif ($slot === 28) {
                        $player->removeCurrentWindow();
                        Main::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                            Main::getInstance()->getHdvManager()->displayProducts($player);
                        }), 10);
                    } elseif ($slot === 27) {
                        $player->removeCurrentWindow();
                        Main::getInstance()->getHdvManager()->showWhatHdv($player);
                    } else {
                        $item = $source;
                        $xuidSeller = $item->getNamedTag()->getString('xuidSeller', 'null');
                        if ($xuidSeller !== 'null') {
                            $player->removeCurrentWindow();
                            if (!$isAdmin) {
                                $player->sendForm(new ModalForm('- §6Hôtel des ventes §r-', "§7Êtes-vous sur de vouloir acheter l'item ?\n§6Item: §e" . $item->getName() . "\n§6Montant: §e" . $item->getCount() . "\n§6Prix: §e" . $item->getNamedTag()->getInt('price', 404) . "$",
                                    function (Player $player, bool $value) use ($item, $inv, $isAdmin): void {
                                        if ($value) {
                                            Main::getInstance()->getHdvManager()->purchase($player, $item, $inv);
                                        }
                                    }
                                ));
                            } else {
                                $form = new ModalForm("- §6Hôtel des ventes §r-", "§4êtes vous sûre de vouloir supprimé l'item de l'hdv ?", function (Player $player, bool $value) use ($item): void {
                                    if ($value) {
                                        Main::getInstance()->getHdvManager()->removeProduct($item);
                                        $player->sendMessage(Messages::message("vous avez bien supprimé l'item"));
                                    }
                                });
                                $player->sendForm($form);
                            }
                        }
                    }
                });
                $inv->setCloseCallback(function (Player $player, Inventory $inventory) {
                    $player->hdv = 0;
                    $player->hasSendHdv = false;
                });
                foreach ($content[strval(0)] as $item) $inv->addItem($item);
                $inv->setName("HDV");
                $inv->send($player);
            });
        }
    }


    public function showWhatHdv(Player $player): void
    {
        $player->sendForm(new MenuForm("- §6Hôtel des ventes §r-", "§7Bienvenue sur l'interface des explications de l'hôtel des ventes. Je vous explique comment vendre votre item en toute facilité !", [
            new Button("§6» §eComment vendre", new Image("textures/hdv/interog", "path"))

        ], function (Player $player, Button $button): void {
            if ($button->getValue() === 0) {
                $player->sendForm(new MenuForm(
                    '- §6Hôtel des ventes §r-',
                    "§7Bienvenue sur l'interface pour apprendre à mettre en vente un item dans l'hôtel des ventes.\nVous devez prendre un item dans votre main avec la quantité que vous souhaiter, vous faites §6/hdv sell§7  à ce moment-là un ui apparaîtra, la mise en vente sera guidée."
                ));
            }
        }));
    }

    public function purchase(Player $player, Item $item, Inventory $inv): void
    {
        $api = Main::getInstance()->getEconomyManager();
        $uniqueId = $item->getNamedTag()->getString('uniqueId', 'null');
        if ($uniqueId !== 'null') {
            $xuidSeller = $item->getNamedTag()->getString("xuidSeller");
            if ($xuidSeller !== $player->getXuid()) {
                $price = $item->getNamedTag()->getInt('price');


                Main::getInstance()->getEconomyManager()->getMoneySQL($player,
                    function (Player $player, int $money) use ($price, $api, $uniqueId, $item, $xuidSeller, $inv) : void {
                        if ($money >= $price) {
                            if ($player->getInventory()->canAddItem($item)) {
                                SQL::async(static function (RequestAsync $thread, \mysqli $db) use ($uniqueId): void {
                                    $prepare = $db->prepare("SELECT * FROM `hdv_products` WHERE uuid = ?;");
                                    $prepare->bind_param('s', $uniqueId);
                                    $prepare->execute();
                                    $found = false;
                                    if ($prepare->get_result()->num_rows > 0) $found = true;
                                    $thread->setResult($found);
                                }, static function (RequestAsync $thread) use ($player, $api, $price, $xuidSeller, $item, $inv): void {
                                    $found = $thread->getResult();
                                    if ($player->isConnected()) {
                                        if ($found) {
                                            $api->removeMoney($player, $price);
                                            $player->sendPopup('§c-' . $price . '$');
                                            $api->addMoneyNotName($xuidSeller, $price);
                                            $playerT = Main::getInstance()->getDataManager()->getPlayerXuid($xuidSeller);
                                            if ($playerT instanceof Player) {
                                                $playerT->sendMessage(Messages::message("§aUn joueur vous à acheter un item dans l'hôtel des ventes."));
                                            }
                                            /* TODO: discord notif pour les users
                                            if (Main::getInstance()->social->hasDiscordVerifid($xuidSeller)) {
                                                $pseudoDiscord = Main::getInstance()->social->getDiscordPseudo($xuidSeller);
                                                $json = json_encode([
                                                    'event' => 'hdv',
                                                    'content' => "Un joueur vous à acheter un item dans l'hôtel des ventes.",
                                                    'pseudoDiscord' => $pseudoDiscord
                                                ]);
                                                $message = new Message();
                                                $message->setContent($json);
                                                Main::getInstance()->social->sendWebhookEvent($message);
                                            }
                                            */
                                            Main::getInstance()->getHdvManager()->removeProduct($item);
                                            $inv->removeItem($item);
                                            $item2 = clone $item;

                                            $item2->getNamedTag()->removeTag('uniqueId');
                                            $item2->getNamedTag()->removeTag('xuidSeller');
                                            $item2->getNamedTag()->removeTag('price');
                                            $loreBasic = unserialize(base64_decode($item2->getNamedTag()->getString('basic_lore')));
                                            $item2->getNamedTag()->removeTag('basic_lore');
                                            $item2->setLore($loreBasic);

                                            $player->getInventory()->addItem($item2);
                                            $player->getWorld()->addSound($player->getEyePos(), new XpLevelUpSound(5), [$player]);
                                        } else  $player->sendMessage(Messages::message("§cLe produit n'est plus disponible."));
                                    }
                                });

                            } else {
                                $player->sendPopup(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                                $pk = new PlaySoundPacket();
                                $pk->x = $player->getEyePos()->getX();
                                $pk->y = $player->getEyePos()->getY();
                                $pk->z = $player->getEyePos()->getZ();
                                $pk->pitch = 0.80;
                                $pk->volume = 5;
                                $pk->soundName = 'note.bass';
                                $player->getNetworkSession()->sendDataPacket($pk);
                            }
                        } else {
                            $player->sendPopup(Messages::message("§cVous n'avez pas l'argent nécessaire."));
                            $pk = new PlaySoundPacket();
                            $pk->x = $player->getEyePos()->getX();
                            $pk->y = $player->getEyePos()->getY();
                            $pk->z = $player->getEyePos()->getZ();
                            $pk->pitch = 0.80;
                            $pk->volume = 5;
                            $pk->soundName = 'note.bass';
                            $player->getNetworkSession()->sendDataPacket($pk);
                        }
                    });


            } else {
                $player->sendPopup(Messages::message("§cVous ne pouvez pas acheter vos items."));
                $pk = new PlaySoundPacket();
                $pk->x = $player->getEyePos()->getX();
                $pk->y = $player->getEyePos()->getY();
                $pk->z = $player->getEyePos()->getZ();
                $pk->pitch = 0.80;
                $pk->volume = 5;
                $pk->soundName = 'note.bass';
                $player->getNetworkSession()->sendDataPacket($pk);
            }
        } else $player->sendMessage(Messages::message("§cUne erreur est survenue."));
    }


    public function removeProduct(Item $item): void
    {
        $uniqueId = $item->getNamedTag()->getString('uniqueId', 'null');
        if ($uniqueId !== 'null') {
            SQL::async(static function (RequestAsync $thread, \mysqli $db) use ($uniqueId): void {
                $prepare = $db->prepare("DELETE FROM `hdv_products` WHERE `uuid` = ?;");
                $prepare->bind_param('s', $uniqueId);
                $prepare->execute();
            });
        }
    }

    public function deleteProduct(Player $player, Item $item, Inventory $inv): void
    {
        $api = Main::getInstance()->getEconomyManager();
        $uniqueId = $item->getNamedTag()->getString('uniqueId', 'null');
        if ($uniqueId !== 'null') {
            $price = $item->getNamedTag()->getInt('price');
            if ($player->getInventory()->canAddItem($item)) {

                $player->sendForm(new ModalForm(
                    '- §6Hôtel des ventes §r-',
                    "§cÊtes-vous sur de vouloir retirer votre item de l'hôtel des ventes ? \n§6Item: §e" . $item->getName() . "\n§6Montant: §e" . $item->getCount() . "\n\n§c/!\\ Vous devez payer 1000$",
                    function (Player $player, bool $value) use ($uniqueId, $item, $inv, $price, $api): void {
                        if ($value) {
                            SQL::async(static function (RequestAsync $thread, \mysqli $db) use ($uniqueId): void {
                                $prepare = $db->prepare("SELECT * FROM `hdv_products` WHERE uuid = ?;");
                                $prepare->bind_param('s', $uniqueId);
                                $prepare->execute();
                                $found = false;
                                if ($prepare->get_result()->num_rows > 0) $found = true;
                                $thread->setResult($found);
                            }, static function (RequestAsync $thread) use ($player, $api, $price, $item, $inv) : void {
                                $found = $thread->getResult();
                                if ($player->isConnected()) {
                                    if ($found) {

                                        Main::getInstance()->getEconomyManager()->getMoneySQL($player,
                                            function (Player $player, int $money) use ($api, $item, $inv, ) : void {
                                                if ($money < 1000) {
                                                    $player->sendMessage(Messages::message("§cVous n'avez pas assez d'argent."));
                                                    return;
                                                }
                                                Main::getInstance()->getHdvManager()->removeProduct($item);
                                                $inv->removeItem($item);
                                                $item2 = clone $item;

                                                $item2->getNamedTag()->removeTag('uniqueId');
                                                $item2->getNamedTag()->removeTag('xuidSeller');
                                                $item2->getNamedTag()->removeTag('price');
                                                $loreBasic = unserialize(base64_decode($item2->getNamedTag()->getString('basic_lore')));
                                                $item2->getNamedTag()->removeTag('basic_lore');
                                                $item2->setLore($loreBasic);

                                                $player->getInventory()->addItem($item2);
                                                $player->sendPopup('§c- 1000$');
                                                $api->removeMoney($player, 1000);
                                                $player->getWorld()->addSound($player->getEyePos(), new XpLevelUpSound(5), [$player]);

                                            });

                                    } else  $player->sendMessage(Messages::message("§cLe produit n'est plus disponible."));
                                }
                            });
                        }
                    }
                ));
            } else {
                $player->sendPopup(Messages::message("§cVous n'avez pas assez de place dans votre inventaire."));
                $pk = new PlaySoundPacket();
                $pk->x = $player->getEyePos()->getX();
                $pk->y = $player->getEyePos()->getY();
                $pk->z = $player->getEyePos()->getZ();
                $pk->pitch = 0.80;
                $pk->volume = 5;
                $pk->soundName = 'note.bass';
                $player->getNetworkSession()->sendDataPacket($pk);
            }
        } else $player->sendMessage(Messages::message("§cUne erreur est survenue."));
    }


    public function createProduct(Item $item, int $price, Player $seller): void
    {
        $itemSerialized = Utils::serilizeItem($item);
        $serializedData = base64_encode(serialize([
            "item" => $itemSerialized,
            "price" => $price,
            "xuid" => $seller->getXuid(),
            "lore_basic" => $item->getLore()
        ]));

        SQL::async(static function (RequestAsync $thread, \mysqli $db) use ($serializedData): void {
            $uniqueId = uniqid();
            $prepare = $db->prepare("INSERT INTO hdv_products (uuid, serialized) VALUES (?, ?);");
            $prepare->bind_param('ss', $uniqueId, $serializedData);
            $prepare->execute();
        });
    }
}