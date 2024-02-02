<?php

namespace core\commands\executors\staff;

use core\api\form\elements\Button;
use core\api\form\MenuForm;
use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class SeeHome extends Executor
{
    public function __construct(string $name = 'seehome', string $description = "Voir les homes d'un joueur", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission('seehome.use');
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $sender->sendMessage(Messages::message("§c/seehome <playerName>"));
            return;
        }

        $entity = Server::getInstance()->getPlayerByPrefix($args[0]);
        if ($entity instanceof CustomPlayer) {
            $homes = Main::getInstance()->getHomeManager()->getAllHomesPlayer($entity);
            $btn = [];

            $i = 1;
            foreach ($homes as $homeName => $posHash) {
                $btn[] = new Button("Home #" . $i . "\nNom: " . $homeName);
                $i++;
            }
            $namePlayer = $entity->getName();
            $xuidPlayer = $entity->getXuid();

            $sender->sendForm(new MenuForm("§6- §fHome Manager §6-", "Voir et gérer les homes des joueurs, que demandé de plus ? :)",
                $btn, function (Player $player, Button $button) use ($homes, $namePlayer, $entity, $xuidPlayer) : void {
                    $value = $button->getValue();
                    $i = 0;
                    $data = [];
                    foreach ($homes as $homeName => $posHash) {
                        if ($i === $value) {
                            $data = [
                                $posHash,
                                $homeName
                            ];
                        }
                        $i++;
                        if ($i >= 25) {
                            $player->sendMessage(Messages::message("§cUne erreur est survenue, nous nous excusons de la gêne occasionnée."));
                            return;
                        }
                    }

                    if ($data !== []) {
                        $player->sendForm(new MenuForm("§6- §fHOME : §6" . $data[1] . " §6-", "Nom du joueur : " . $namePlayer, [
                            new Button("§6Se teleporter"),
                            new Button("§cSupprimer")
                        ], function (Player $player, Button $button) use ($data, $namePlayer, $entity, $xuidPlayer) : void {
                            switch ($button->getValue()) {
                                case 0:
                                    $pos = $this->stringToPosition($data[0]);
                                    if ($pos !== null) {
                                        $player->teleport($pos);
                                        $player->sendMessage("§c[§4STAFF§c] §fTéléportation chez le home §c" . $data[1] . "§f du joueur §c" . $namePlayer);
                                        $player->sendSuccessSound();
                                    } else $player->sendMessage(Messages::message("§cUne erreur est survenue, nous nous excusons de la gêne occasionnée."));
                                    break;
                                case 1:
                                    Main::getInstance()->getHomeManager()->deleteHome($xuidPlayer, $data[1]);
                                    $player->sendSuccessSound();
                                    $player->sendMessage("§c[§4STAFF§c]§f Home §c" . $data[1] . " §fdu joueur §c" . $namePlayer . " §fsupprimé !");
                                    break;
                            }
                        }));
                    } else $player->sendMessage(Messages::message("§cUne erreur est survenue, nous nous excusons de la gêne occasionnée."));
                }));
        } else {
            $xuidTarget = Main::getInstance()->getDataManager()->getXuidByName($args[0]);
            if (is_null($xuidTarget)) {
                $sender->sendMessage(Messages::message("§cLe joueur n'existe pas."));
                return;
            }

            Main::getInstance()->getHomeManager()->getAllHomesDisconnected($xuidTarget, function (array $homes) use ($sender, $args) : void {
                if (!$sender->isConnected()) return;



                $btn = [];

                $i = 1;
                foreach ($homes as $homeName => $posHash) {
                    $btn[] = new Button("Home #" . $i . "\nNom: " . $homeName);
                    $i++;
                }

                $namePlayer = $args[0];
                $sender->sendForm(new MenuForm("§6- §fHome Manager §6-", "Voir et gérer les homes des joueurs, que demandé de plus ? :)",
                    $btn, function (Player $player, Button $button) use ($homes, $namePlayer) : void {
                        $value = $button->getValue();
                        $i = 0;
                        $data = [];
                        foreach ($homes as $homeName => $posHash) {
                            if ($i === $value) {
                                $data = [
                                    $posHash,
                                    $homeName
                                ];
                            }
                            $i++;
                            if ($i >= 25) {
                                $player->sendMessage(Messages::message("§cUne erreur est survenue, nous nous excusons de la gêne occasionnée."));
                                return;
                            }
                        }

                        if ($data !== []) {
                            $player->sendForm(new MenuForm("§6- §fHOME : §6" . $data[1] . " §6-", "Nom du joueur : " . $namePlayer, [
                                new Button("§6Se teleporter"),
                            ], function (Player $player, Button $button) use ($data, $namePlayer) : void {
                                switch ($button->getValue()) {
                                    case 0:
                                        $pos = $this->stringToPosition($data[0]);
                                        if ($pos !== null) {
                                            $player->teleport($pos);
                                            $player->sendMessage("§c[§4STAFF§c] §fTéléportation chez le home §c" . $data[1] . "§f du joueur §c" . $namePlayer);
                                            $player->sendSuccessSound();
                                        } else $player->sendMessage(Messages::message("§cUne erreur est survenue, nous nous excusons de la gêne occasionnée."));
                                        break;
                                }
                            }));
                        } else $player->sendMessage(Messages::message("§cUne erreur est survenue, nous nous excusons de la gêne occasionnée."));
                    }));
            });
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        $this->addOptionEnum(0, 'Liste des joueurs', true, 'Joueurs', $this->getAllPlayersArrayForArgs());
        return parent::loadOptions($player);
    }
}