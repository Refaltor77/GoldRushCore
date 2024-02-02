<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\managers\shop\ShopManager;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class SellAll extends Executor
{
    use SoundTrait;

    public static array $cooldown = [];

    public function __construct(string $name = "sellall", string $description = "Vendre tout vos items dans votre inventaire.", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("sell.all.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $sell = $config->get('shop');
        $money = Main::getInstance()->getEconomyManager();
        $hasSell = false;
        $sellAmount = 0;


        $rank = Main::getInstance()->getRankManager()->getSupremeRankPriority($sender->getXuid());

        switch ($rank) {
            case "COWBOY":
                if (isset(self::$cooldown[$sender->getXuid()])) {
                    if (self::$cooldown[$sender->getXuid()] <= time()) {
                        break;
                    } else {
                        $this->sendErrorSound($sender);
                        $sender->sendNotification("§cVous avez un cooldown de §4" . self::$cooldown[$sender->getXuid()] - time() . " seconde§c(§4s§c).");
                        return;
                    }
                } else {
                    self::$cooldown[$sender->getXuid()] = time() + 60 * 5;
                }
                break;
            case "BRAQUEUR":
                if (isset(self::$cooldown[$sender->getXuid()])) {
                    if (self::$cooldown[$sender->getXuid()] <= time()) {
                        break;
                    } else {
                        $this->sendErrorSound($sender);
                        $sender->sendNotification("§cVous avez un cooldown de §4" . self::$cooldown[$sender->getXuid()] - time() . " seconde§c(§4s§c).");
                        return;
                    }
                } else {
                    self::$cooldown[$sender->getXuid()] = time() + 60 * 10;
                }
                break;
        }


        foreach ($sell as $category => $array) {
            foreach ($array['items'] as $values) {
                if (isset($values['sell'])) {
                    $explode = $values['idMeta'];

                    if (stripos($explode, "goldrush:")) {
                        /** @var Item $item */
                        $item = CustomiesItemFactory::getInstance()->get($explode);
                    } else {
                        $item = StringToItemParser::getInstance()->parse($explode);
                    }

                    foreach ($sender->getInventory()->getContents() as $i) {
                        if ($i instanceof Item) {
                            if ($item->equals($i, false, false)) {
                                $count = $i->getCount();
                                $sender->getInventory()->remove($item);
                                $hasSell = true;
                                $sellAmount += $values['sell'] * $count;
                            }
                        }
                    }
                }
            }
        }
        if ($hasSell) {
            $this->sendSuccessSound($sender);
            $money->addMoney($sender, $sellAmount);
            $sender->sendPopup("§a+" . $sellAmount . "$");
            $sender->sendNotification("§fAvec la vente de vos items, vous avez gagné §6" . $sellAmount . "$ §f!", CustomPlayer::NOTIF_TYPE_MONEY);

        } else {
            $this->sendErrorSound($sender);
            $sender->sendPopup("§c- §4Aucun item n'est à vendre dans votre inventaire. §c-");
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}