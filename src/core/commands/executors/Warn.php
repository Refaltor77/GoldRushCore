<?php

namespace core\commands\executors;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Input;
use core\api\form\elements\Label;
use core\api\webhook\Message;
use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class Warn extends Executor
{
    public function __construct(string $name = 'warn', string $description = "Warn un joueur", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("warn.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendErrorSound();
            $sender->sendMessage(Messages::message("§c/warn <§4playerName§c>"));
            return;
        }

        $player = Server::getInstance()->getPlayerByPrefix($args[0]);
        if ($player instanceof CustomPlayer) {
            $xuid = $player->getXuid();
            $nameP = $player->getName();
            $sender->sendForm(new CustomForm("§c- §fWARN POUR §4" . $player->getName() . " §c-", [
                new Label("Bienvenue sur le formulaire pour warn un joueur, une notif sur sont ecran sera envoyé pour le prévenir de sont infraction."),
                new Input("Raison du warn", "insulte")
            ], function (Player $playerA, CustomFormResponse $response) use ($xuid, $player, $nameP) : void {
                $reason = $response->getInput()->getValue();

                $playerA->sendSuccessSound();
                $playerA->sendMessage(Messages::message("§fLe joueur §6" . $nameP . " §fvient de recevoir sont warn."));
                Main::getInstance()->getSanctionManager()->addWarn($xuid, $reason);
                if ($player->isConnected()) {
                    $player->sendTitle("§c- WARN -", "Raison: " . $reason, 1, 8);
                    $player->sendGhastPeur();
                }
            }));
        } else {
            $sender->sendErrorSound();
            $sender->sendMessage(Messages::message("§cLe joueur n'est pas en ligne."));
        }
    }
}