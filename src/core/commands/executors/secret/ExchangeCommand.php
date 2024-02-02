<?php

namespace core\commands\executors\secret;

use core\api\form\elements\Button;
use core\api\form\MenuForm;
use core\commands\Executor;
use core\Main;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;

class ExchangeCommand extends Executor
{
    public function __construct(string $name = 'exchange_123456', string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::OPERATOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $salle1 = Main::getInstance()->getExchangeManager()->salles[1];
        $salle2 = Main::getInstance()->getExchangeManager()->salles[2];
        $salle3 = Main::getInstance()->getExchangeManager()->salles[3];


        if (!$salle1['place'][1] && !$salle1['place'][2]) {
            Main::getInstance()->getExchangeManager()->salles[1]['status'] = false;
        }

        if (!$salle2['place'][1] && !$salle2['place'][2]) {
            Main::getInstance()->getExchangeManager()->salles[2]['status'] = false;
        }


        if (!$salle3['place'][1] && !$salle3['place'][2]) {
            Main::getInstance()->getExchangeManager()->salles[3]['status'] = false;
        }



        $buttons = [];
        foreach (Main::getInstance()->getExchangeManager()->salles as $index => $values) {
            if ($values['status']) {

                $buttons[] = new Button("§aSalle $index - OUVERT");
            } else {
                $buttons[] = new Button("§cSalle 1 - OCCUPÉ");
            }
        }

        $form = new MenuForm(
            "§6- §fSalle d'échanges §6-",
            "Bienvenue sur le formulaire des échanges sécurisé",
            $buttons, function (Player $player, Button $button): void {
                switch ($button->getValue()) {
                    case 0:

                        $salle1 = Main::getInstance()->getExchangeManager()->salles[1];
                        if ($salle1['place'][1]) {
                            Main::getInstance()->getExchangeManager()->saveInventory($player);
                            $player->teleport(new Position(65, 93, -56, Server::getInstance()->getWorldManager()->getDefaultWorld()));
                            Main::getInstance()->getExchangeManager()->salles[1]['place'][1] = false;
                            Main::getInstance()->getExchangeManager()->salles[1]['place']['xuid'][] = $player->getXuid();
                        } elseif ($salle1['place'][2]) {
                            Main::getInstance()->getExchangeManager()->saveInventory($player);
                            $player->teleport(new Position(65, 93, -48, Server::getInstance()->getWorldManager()->getDefaultWorld()));
                            Main::getInstance()->getExchangeManager()->salles[1]['place'][2] = false;
                            Main::getInstance()->getExchangeManager()->salles[1]['place']['xuid'][] = $player->getXuid();

                            if (count(Main::getInstance()->getExchangeManager()->salles[1]['place']['xuid']) === 2) {
                                $arrayPlayers = [];
                                foreach (Main::getInstance()->getExchangeManager()->salles[1]['place']['xuid'] as $xuid) {
                                    $arrayPlayers[] = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                                }
                                $hasCo = false;
                                foreach ($arrayPlayers as $playerT) {
                                    if ($playerT instanceof Player) {
                                        $hasCo = true;
                                    } else $hasCo = false;
                                }

                                if (!$hasCo) {
                                    foreach ($arrayPlayers as $playerT) {
                                        if ($playerT instanceof Player) {
                                            Main::getInstance()->getExchangeManager()->distribInventory($playerT);
                                            $playerT->teleport(new Position(56, 85, -52, Server::getInstance()->getWorldManager()->getDefaultWorld()));
                                        }
                                    }
                                    Main::getInstance()->getExchangeManager()->salles[1]['place']['xuid'] = [];
                                    Main::getInstance()->getExchangeManager()->salles[1]['place'][1] = true;
                                    Main::getInstance()->getExchangeManager()->salles[1]['place'][2] = true;
                                    Main::getInstance()->getExchangeManager()->salles[1]['status'] = true;
                                } else {
                                    Main::getInstance()->getExchangeManager()->setSessions($arrayPlayers[0], $arrayPlayers[1]);
                                }
                            }
                        }

                        if (!$salle1['place'][1] && !$salle1['place'][2]) {
                            Main::getInstance()->getExchangeManager()->salles[1]['status'] = false;
                        }
                        break;
                    case 1:

                        $salle2 = Main::getInstance()->getExchangeManager()->salles[2];
                        if ($salle2['place'][1]) {
                            Main::getInstance()->getExchangeManager()->saveInventory($player);
                            $player->teleport(new Position(57, 93, -56, Server::getInstance()->getWorldManager()->getDefaultWorld()));
                            Main::getInstance()->getExchangeManager()->salles[2]['place'][1] = false;
                            Main::getInstance()->getExchangeManager()->salles[2]['place']['xuid'][] = $player->getXuid();
                        } elseif ($salle2['place'][2]) {
                            Main::getInstance()->getExchangeManager()->saveInventory($player);
                            $player->teleport(new Position(57, 93, -48, Server::getInstance()->getWorldManager()->getDefaultWorld()));
                            Main::getInstance()->getExchangeManager()->salles[2]['place'][2] = false;
                            Main::getInstance()->getExchangeManager()->salles[2]['place']['xuid'][] = $player->getXuid();

                            if (count(Main::getInstance()->getExchangeManager()->salles[2]['place']['xuid']) === 2) {
                                $arrayPlayers = [];
                                foreach (Main::getInstance()->getExchangeManager()->salles[2]['place']['xuid'] as $xuid) {
                                    $arrayPlayers[] = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                                }
                                $hasCo = false;
                                foreach ($arrayPlayers as $playerT) {
                                    if ($playerT instanceof Player) {
                                        $hasCo = true;
                                    } else $hasCo = false;
                                }

                                if (!$hasCo) {
                                    foreach ($arrayPlayers as $playerT) {
                                        if ($playerT instanceof Player) {
                                            Main::getInstance()->getExchangeManager()->distribInventory($playerT);
                                            $playerT->teleport(new Position(56, 85, -52, Server::getInstance()->getWorldManager()->getDefaultWorld()));
                                        }
                                    }
                                    Main::getInstance()->getExchangeManager()->salles[2]['place']['xuid'] = [];
                                    Main::getInstance()->getExchangeManager()->salles[2]['place'][1] = true;
                                    Main::getInstance()->getExchangeManager()->salles[2]['place'][2] = true;
                                    Main::getInstance()->getExchangeManager()->salles[2]['status'] = true;
                                }else {
                                    Main::getInstance()->getExchangeManager()->setSessions($arrayPlayers[0], $arrayPlayers[1]);
                                }
                            }
                        }

                        if (!$salle2['place'][1] && !$salle2['place'][2]) {
                            Main::getInstance()->getExchangeManager()->salles[2]['status'] = false;
                        }
                        break;

                    case 2:

                        $salle3 = Main::getInstance()->getExchangeManager()->salles[3];
                        if ($salle3['place'][1]) {
                            Main::getInstance()->getExchangeManager()->saveInventory($player);
                            $player->teleport(new Position(51, 93, -56, Server::getInstance()->getWorldManager()->getDefaultWorld()));
                            Main::getInstance()->getExchangeManager()->salles[3]['place'][1] = false;
                            Main::getInstance()->getExchangeManager()->salles[3]['place']['xuid'][] = $player->getXuid();
                        } elseif ($salle3['place'][2]) {
                            Main::getInstance()->getExchangeManager()->saveInventory($player);
                            $player->teleport(new Position(51, 93, -48, Server::getInstance()->getWorldManager()->getDefaultWorld()));
                            Main::getInstance()->getExchangeManager()->salles[3]['place'][2] = false;
                            Main::getInstance()->getExchangeManager()->salles[3]['place']['xuid'][] = $player->getXuid();

                            if (count(Main::getInstance()->getExchangeManager()->salles[3]['place']['xuid']) === 2) {
                                $arrayPlayers = [];
                                foreach (Main::getInstance()->getExchangeManager()->salles[3]['place']['xuid'] as $xuid) {
                                    $arrayPlayers[] = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                                }
                                $hasCo = false;
                                foreach ($arrayPlayers as $playerT) {
                                    if ($playerT instanceof Player) {
                                        $hasCo = true;
                                    } else $hasCo = false;
                                }

                                if (!$hasCo) {
                                    foreach ($arrayPlayers as $playerT) {
                                        if ($playerT instanceof Player) {
                                            Main::getInstance()->getExchangeManager()->distribInventory($playerT);
                                            $playerT->teleport(new Position(56, 85, -52, Server::getInstance()->getWorldManager()->getDefaultWorld()));
                                        }
                                    }
                                    Main::getInstance()->getExchangeManager()->salles[3]['place']['xuid'] = [];
                                    Main::getInstance()->getExchangeManager()->salles[3]['place'][1] = true;
                                    Main::getInstance()->getExchangeManager()->salles[3]['place'][2] = true;
                                    Main::getInstance()->getExchangeManager()->salles[3]['status'] = true;
                                } else {
                                    Main::getInstance()->getExchangeManager()->setSessions($arrayPlayers[0], $arrayPlayers[1]);
                                }
                            }
                        }

                        if (!$salle3['place'][1] && !$salle3['place'][2]) {
                            Main::getInstance()->getExchangeManager()->salles[3]['status'] = false;
                        }
                        break;
                }
            });

        $sender->sendForm($form);
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}