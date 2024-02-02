<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\Main;
use core\managers\shop\ShopManager;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\SoundTrait;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class Sell extends Executor
{
    use SoundTrait;

    public static array $cooldown = [];

    public function __construct(string $name = "sell", string $description = "Vendre l'item dans votre main.", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("sell.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $config = new Config(Main::getInstance()->getDataFolder() . 'shop.json', Config::JSON);
        $sell = $config->getAll()['shop'];
        $money = Main::getInstance()->getEconomyManager();
        $hasSell = false;
        $sellAmount = 0;


        $rank = Main::getInstance()->getRankManager()->getSupremeRankPriority($sender->getXuid());

        switch ($rank) {
            case "FARMER":
            case "BANDIT":
                if (isset(self::$cooldown[$sender->getXuid()])) {
                    if (self::$cooldown[$sender->getXuid()] <= time()) {
                        break;
                    } else {
                        $this->sendErrorSound($sender);
                        $sender->sendMessage(Messages::message("§cVous avez un cooldown de §4" . self::$cooldown[$sender->getXuid()] - time() . " seconde§c(§4s§c)."));
                        return;
                    }
                } else {
                    self::$cooldown[$sender->getXuid()] = time() + 60 * 3;
                }
                break;
        }


        foreach ($sell as $category => $array) {
            foreach ($array['items'] as $values) {
                if (isset($values['sell'])) {
                    $explode = $values['idMeta'];

                    if (stripos($explode, "goldrush:")) {
                        $item = CustomiesItemFactory::getInstance()->get($explode);
                    } else {
                        $item = StringToItemParser::getInstance()->parse($explode);
                    }

                    if (!$item instanceof Item) return;

                    $count = max(1, $item->getCount());
                    $checkTags = $item->hasNamedTag();



                    $itemInHand = $sender->getInventory()->getItemInHand();

                    if ($item->equals($itemInHand, false, $checkTags)) {
                        $count = $itemInHand->getCount();
                        $sender->getInventory()->setItemInHand($itemInHand->setCount(0));
                        $hasSell = true;
                        $sellAmount += $values['sell'] * $count;
                    }
                }
            }
        }
        if ($hasSell) {
            $this->sendSuccessSound($sender);
            $money->addMoney($sender, $sellAmount);
            $sender->sendPopup("§a+" . $sellAmount . "$");
            $sender->sendMessage(Messages::message("§fAvec la vente de votre item, vous avez gagné §6" . $sellAmount . "$ §f!"));

        } else {
            $this->sendErrorSound($sender);
            $sender->sendPopup("§c- §4Votre item n'est pas à vendre. §c-");
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}