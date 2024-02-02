<?php

namespace core\commands\executors;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Toggle;
use core\commands\Executor;
use core\events\BossBarReloadEvent;
use core\events\ScoreboardReloadEvent;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Settings extends Executor
{
    const SETTINGS_LIST = [
        "coordinates" => "Afficher les coordonnées",
        "private-chat" => "Activer le chat privé",
        "scoreboard" => "Activer le scoreboard",
        "xp-jobs" => "Afficher les particules d'xp des jobs",
        "inv" => "Afficher le message d'inventaire plein",
        "cps" => "Afficher les cps",
        "bossbar" => "Afficher la bossbar",
    ];

    public function __construct(string $name = "settings", string $description = "changer ses paramètres", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args): void
    {
        $choices = [];
        foreach (self::SETTINGS_LIST as $index => $value) {
            $choices[] = new Toggle($value, $this->getPlugin()->getSettingsManager()->getSetting($sender, $index) ?? false);
        }
        $sender->sendForm(new CustomForm("§l§eParamètres",
            $choices,
            function (Player $player, CustomFormResponse $response): void {
                foreach ($response->getValues() as $index => $value) {
                    $index = array_keys(self::SETTINGS_LIST)[$index];
                    $this->getPlugin()->getSettingsManager()->setSetting($player, $index,$value);
                    if($index === "coordinates") {
                        $pk = new GameRulesChangedPacket();
                        $pk->gameRules = [
                            "showcoordinates" => new BoolGameRule((int)($value), false)
                        ];
                        $player->getNetworkSession()->sendDataPacket($pk);
                    } elseif ($index === 'scoreboard') {
                        (new ScoreboardReloadEvent($player))->call();
                        $scoreboard = Main::getInstance()->getScoreboardManager()->getScoreboardApi()->getScoreboard("objectif");
                        if (!Main::getInstance()->getSettingsManager()->getSetting($player, "scoreboard")) {
                            Main::getInstance()->getScoreboardManager()->getScoreboardApi()->removeScoreboard($scoreboard, [$player]);
                        }
                    }elseif ($index === 'bossbar') {
                        if (!Main::getInstance()->getSettingsManager()->getSetting($player, "bossbar")) {
                            $pk = BossEventPacket::hide($player->getId());
                            if ($player->isConnected()) $player->getNetworkSession()->sendDataPacket($pk);
                        }
                    }
                }

            }));
    }
}