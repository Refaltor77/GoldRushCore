<?php

namespace core\commands\executors;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Dropdown;
use core\api\form\elements\Input;
use core\api\form\elements\Label;
use core\commands\Executor;
use core\events\LogEvent;
use core\Main;
use core\messages\Messages;
use core\traits\UtilsTrait;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\player\Player;

class Mute extends Executor
{

    use UtilsTrait;

    public function __construct(string $name = 'mute', string $description = "Mute un joueur", ?string $usageMessage = null, array $aliases = [])
    {
        $this->setPermissionMessage(Messages::message("§cVous n'avez pas la permissions !"));
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission('mute.use');
    }

    public function onRun(Player $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§cVous devez sélectionner un joueur !"));
            return;
        }

        $player = $this->getPlugin()->getServer()->getPlayerByPrefix($args[0]);
        if ($player instanceof Player) {

            if ($player->getXuid() === $sender->getXuid()) {
                $sender->sendMessage(Messages::message("§cVous ne pouvez pas vous mute !"));
                return;
            }
            $sender->sendForm(new CustomForm(
                '§6- §fGoldRush §6M§fute §6-',
                [
                    new Label('§fVous êtes sur le point de mute le joueur §6' . $player->getName()),
                    new Input('§6» §eRaison', 'Insultes'),
                    new Input('§6» §eDurée', "5"),
                    new Dropdown("", ['Minutes', 'Heures', 'Jours', 'Mois'], 1),
                ],
                function (Player $sender, CustomFormResponse $response) use ($player): void {
                    if (!$player->isConnected()) {
                        $sender->sendMessage(Messages::message("§cLe joueur s'est déconnecté"));
                        return;
                    }
                    list($reason, $timestamp, $type) = $response->getValues();

                    if (!(int)$timestamp) {
                        $sender->sendMessage(Messages::message("§cVous devez préciser une valeur de temps en chiffre."));
                        return;
                    }

                    $timestamp *= match ($type) {
                        'Minutes' => 60,
                        'Heures' => 3600,
                        'Jours' => 86400,
                        'Mois' => 2628000,
                    };
                    $this->getPlugin()->getSanctionManager()->addWarn($player->getXuid(), 'Mute pour ' . $reason);
                    $this->getPlugin()->getSanctionManager()->mute($player->getXuid(), $reason, intval($timestamp));
                    Main::getInstance()->getGrafanaManager()->addMuteQueue($player->getXuid(), $sender->getXuid(), $reason, intval($timestamp));
                    $sender->sendMessage(Messages::message("§aVous avez mute le joueur §6{$player->getName()}"));

                    foreach ($this->getPlugin()->getServer()->getOnlinePlayers() as $players) {
                        if ($players->getName() !== $player->getName()) $players->sendMessage("§6------\n§6- §6GoldRush Mute§6 -\n§fJoueur: §6{$player->getName()}\n§fDate de fin: §e" . date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", time() + intval($timestamp)) . "\n§fRaison: §c" . $reason . "\n§6------");
                    }

                    $player->sendMessage("Vous avez été mute pour la raison §c" . $reason . "§f et pour la durée de §e" . date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", time() + intval($timestamp)));
                    (new LogEvent($sender->getName()." a mute ".$player->getName() ." pour la raison ".$reason." et pour la durée de ".date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", time() + intval($timestamp)), LogEvent::SANCTION_TYPE))->call();
                }
            ));
        } else {
            $xuidTarget = $this->getPlugin()->getDataManager()->getXuidByName($args[0]);
            if (is_null($xuidTarget)) {
                $sender->sendMessage(Messages::message("cLe joueur §f{$args[0]}§c est inexistant !"));
                return;
            }
            if ($xuidTarget === $sender->getXuid()) {
                $sender->sendMessage(Messages::message("§cVous ne pouvez pas vous mute !"));
                return;
            }

            $name = $args[0];

            $sender->sendForm(new CustomForm(
                '§6- §fGoldRush §6M§fute §6-',
                [
                    new Label('§fVous êtes sur le point de mute le joueur §6' . $args[0]),
                    new Input('§6» §eRaison', 'Insultes'),
                    new Input('§6» §eDurée', ""),
                    new Dropdown("", ['Minutes', 'Heures', 'Jours', 'Mois'], 1),
                ],
                function (Player $sender, CustomFormResponse $response) use ($xuidTarget, $name): void {
                    list($reason, $timestamp, $type) = $response->getValues();

                    if (!(int)$timestamp) {
                        $sender->sendMessage(Messages::message("§cVous devez préciser une valeur de temps en chiffre."));
                        return;
                    }

                    $timestamp *= match ($type) {
                        'Minutes' => 60,
                        'Heures' => 3600,
                        'Jours' => 86400,
                        'Mois' => 2628000,
                    };
                    $this->getPlugin()->getSanctionManager()->addWarn($xuidTarget, 'Mute pour ' . $reason);
                    $this->getPlugin()->getSanctionManager()->mute($xuidTarget, $reason, intval($timestamp));
                    Main::getInstance()->getGrafanaManager()->addMuteQueue($xuidTarget, $sender->getXuid(), $reason, intval($timestamp));
                    $sender->sendMessage(Messages::message("§aVous avez mute le joueur §6{$name}"));
                    foreach ($this->getPlugin()->getServer()->getOnlinePlayers() as $player) {
                        $player->sendMessage("§6------\n§6- §6GoldRush Mute §6-\n§fJoueur: §6{$name}\n§fDate de fin: §e" . date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", time() + intval($timestamp)) . "\n§fRaison: §c" . $reason . "\n§6------");
                    }
                    (new LogEvent($sender->getName()." a mute ".$name ." pour la raison ".$reason." et pour la durée de ".date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", time() + intval($timestamp)), LogEvent::SANCTION_TYPE))->call();
                }
            ));
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'Liste des joueurs', true, 'Joueurs', $this->getAllPlayersArrayForArgs());
        return parent::loadOptions($player);
    }
}