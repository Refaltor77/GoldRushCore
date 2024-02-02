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

class Ban extends Executor
{

    use UtilsTrait;

    public function __construct(string $name = 'ban', string $description = "Bannir un joueur", ?string $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission('ban.use');
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
                $sender->sendMessage(Messages::message("§cVous ne pouvez pas vous bannir !"));
                return;
            }

            $sender->sendForm(new CustomForm(
                '§6- §fGoldRush §6B§fannissement §6-',
                [
                    new Label('§7Vous êtes sur le point de bannir le joueur §f' . $player->getName()),
                    new Input('§6» §fRaison', 'X-Ray'),
                    new Input('§6» §fDurée', ""),
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
                    $this->getPlugin()->getSanctionManager()->addWarn($player->getXuid(), 'Bannissement pour ' . $reason);
                    $this->getPlugin()->getSanctionManager()->ban($player->getXuid(), $reason, intval($timestamp));
                    $sender->sendMessage(Messages::message("§aVous avez banni le joueur §f{$player->getName()}"));
                    Main::getInstance()->getGrafanaManager()->addBanQueue($player->getXuid(), $sender->getXuid(), $reason, intval($timestamp));
                    (new LogEvent($sender->getName()." a banni ".$player->getName() ." pour la raison ".$reason." et pour la durée de ".date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", time() + intval($timestamp)), LogEvent::SANCTION_TYPE))->call();
                }
            ));
        } else {
            $xuidTarget = $this->getPlugin()->getDataManager()->getXuidByName($args[0]);
            if (is_null($xuidTarget)) {
                $sender->sendMessage(Messages::message("§cLe joueur §f{$args[0]}§c est inexistant !"));
                return;
            }

            if ($xuidTarget === $sender->getXuid()) {
                $sender->sendMessage(Messages::message("§cVous ne pouvez pas vous bannir !"));
                return;
            }
            $name = $args[0];

            $sender->sendForm(new CustomForm(
                '§6- §fGoldRush §6B§fannissement §6-',
                [
                    new Label('§7Vous êtes sur le point de bannir le joueur §f' . $args[0]),
                    new Input('§6» §fRaison', 'X-Ray'),
                    new Input('§6» §fDurée', ""),
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
                    $this->getPlugin()->getSanctionManager()->addWarn($xuidTarget, 'Bannissement pour ' . $reason);
                    $this->getPlugin()->getSanctionManager()->ban($xuidTarget, $reason, intval($timestamp));
                    Main::getInstance()->getGrafanaManager()->addBanQueue($xuidTarget, $sender->getXuid(), $reason, intval($timestamp));
                    $sender->sendMessage("§l§c[§GoldRush§c]§r§7 Vous avez banni le joueur §e{$name}");
                    (new LogEvent($sender->getName()." a banni ".$name ." pour la raison ".$reason." et pour la durée de ".date("d/m/Y à H \h\\e\u\\r\\e\(\\§\\6\\s\\§\\e\\) \\e\\t i \m\i\\n\u\\t\\e\(\\§\\6\\s\\§\\e\\)", time() + intval($timestamp)), LogEvent::SANCTION_TYPE))->call();
                }
            ));
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'Listes des joueurs', true, 'Joueurs', $this->getAllPlayersArrayForArgs());
        return parent::loadOptions($player);
    }
}