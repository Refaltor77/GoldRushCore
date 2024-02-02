<?php

namespace core\commands\executors;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Toggle;
use core\api\timings\TimingsSystem;
use core\commands\Executor;
use core\events\ScoreboardReloadEvent;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Scoreboard extends Executor
{
    public function __construct(string $name = 'scoreboard', string $description = "Paramètres du scoreboard.", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }



    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {


        $scoreboardData = Main::getInstance()->getScoreboardManager()->getData($sender);

        $money = $scoreboardData['money'] ?? false;
        $cps = $scoreboardData['cps'] ?? false;
        $faction = $scoreboardData['faction'] ?? false;
        $onlinePlayers = $scoreboardData['online_player'] ?? false;
        $goldMined = $scoreboardData['gold'] ?? false;


        $sender->sendForm(new CustomForm("Paramètres du scoreboard", [
            new Toggle("Affichier le nom de faction", $faction),
            new Toggle("Afficher l'argent", $money),
            new Toggle("Afficher les joueurs en ligne", $onlinePlayers),
            new Toggle("Afficher l'or miné", $goldMined),
        ], function (Player $player, CustomFormResponse $response): void {
            $data = $response->getValues();

            $facName = $data[0];
            $money = $data[1];
            $online = $data[2];
            $gold = $data[3];


            Main::getInstance()->getScoreboardManager()->setData($player,  [
                'money' => $money,
                'faction' => $facName,
                'online_player' => $online,
                'gold' => $gold,
                'cps' => false,
            ]);

            $player->sendErrorSound();
            $player->sendMessage(Messages::message("§fVous avez modifier votre scoreboard !"));

            (new ScoreboardReloadEvent($player))->call();
        }));
    }


    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}