<?php

namespace core\commands\executors\secret;

use core\api\form\ModalForm;
use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class FarmerRankBuy extends Executor
{
    public function __construct(string $name = 'farmer_buy_215849562', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {

        if (Main::getInstance()->getRankManager()->hasRank($sender->getXuid(), "FARMER")) {
            $sender->sendMessage(Messages::message("§cVous avez déjà le grade §4farmeur ! §c/rank"));
            $sender->sendErrorSound();
            return;
        }


        Main::getInstance()->getEconomyManager()->getMoneySQL($sender, function (Player $player, int $money): void {
            if ($money < 10000000) {
                $player->sendMessage(Messages::message("§cIl vous faut §410 000 000$ §cpour acheter le grade §4farmeur !"));
                $player->sendErrorSound();
                return;
            }


            $player->sendForm(new ModalForm("§6- §fAchat grade Farmeur §6-", "Êtes vous sur de vouloir dépenser 10 000 000$ pour acheter le grade farmeur ?",
            function (Player $player, bool $result) use ($money) : void {
                if (!$result) return;
                Main::getInstance()->getEconomyManager()->removeMoney($player, 10000000);
                Main::getInstance()->getRankManager()->addRank($player->getXuid(), "FARMER");
                $player->sendMessage(Messages::message("§6Bravo ! §fVous venez d'obtenir le grade §6farmeur !"));
                $player->sendSuccessSound();
            }));
        });
    }
}