<?php

namespace core\managers\exchange;

use core\Main;
use core\managers\Manager;
use core\messages\Messages;
use MongoDB\Driver\Server;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\world\Position;

class ExchangeManager extends Manager
{
    public array $inExchange = [];
    public array $invitations = [];
    public array $inventories = [];
    public array $sessions = [];
    public array $droppedItem = [
        1 => [],
        2 => [],
        3 => []
    ];


    public array $salles = [
        1 => [
            'status'=> true,
            'place' => [
                1 => true,
                2 => true,
                'xuid' => []
            ]
        ],
        2 => [
            'status'=> true,
            'place' => [
                1 => true,
                2 => true,
                'xuid' => []
            ]
        ],
        3 => [
            'status'=> true,
            'place' => [
                1 => true,
                2 => true,
                'xuid' => []
            ]
        ]
    ];

    public function __construct(Main $plugin)
    {
        parent::__construct($plugin);
    }


    public function sendInvitation(Player $sender, Player $receiver): void {
        $this->invitations[$receiver->getXuid()] = $sender->getXuid();
    }

    public function hasInvitation(Player $receiver): bool {
        return isset($this->invitations[$receiver->getXuid()]);
    }

    public function getInvitationPlayer(Player $receiver): ?Player {
        if (!$this->hasInvitation($receiver)) return null;
        return $this->invitations[$receiver->getXuid()];
    }


    public function addDrops(ItemEntity $item, int $salle) {
        $this->droppedItem[$salle][] = $item;
    }

    public function removeDrops(int $salle): void {
        foreach ($this->droppedItem[$salle] as $item) {
            if ($item instanceof ItemEntity) {
                $item->flagForDespawn();
            }
        }
    }


    public function saveInventory(Player $player): void {
        $this->inventories[$player->getXuid()] = [
            'inv' => $player->getInventory()->getContents(true),
            'ender' => $player->getEnderInventory()->getContents(true),
            'off' => $player->getOffHandInventory()->getContents(true),
            'armor' => $player->getArmorInventory()->getContents(true),
        ];
    }

    public function hasSaveInventory(Player $player): bool {
        return isset($this->inventories[$player->getXuid()]);
    }

    public function getSallePlayer(Player $player): int {
        if (!$this->hasSaveInventory($player)) return 0;

        foreach ($this->salles as $index => $values) {
            foreach ($values['place']['xuid'] as $xuid) {
                if ($player->getXuid() === $xuid) return $index;
            }
        }

        return 0;
    }

    public function cancelSession(Player $player): void {

        $salle = $this->getSallePlayer($player);
        foreach ($this->droppedItem[$salle] as $item) {
            if ($item instanceof ItemEntity) {
                if (!$item->isClosed()) {
                    $item->flagForDespawn();
                }
            }
        }
        $this->distribInventory($player);
        $this->droppedItem[$salle] = [];

        $sessions = [];
        $uuidGet = "";
        foreach ($this->sessions as $uuid => $array) {
            foreach ($array as $xuid => $acceptValues) {
                if ($xuid == $player->getXuid()) {
                    $uuidGet = $uuid;
                    $sessions = $this->sessions[$uuid];
                    break;
                }
            }
        }


        foreach ($sessions as $xuid => $bool) {
            $playerT = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
            if ($playerT instanceof Player) {
                $playerT->teleport(new Position(57, 85, -52, \pocketmine\Server::getInstance()->getWorldManager()->getDefaultWorld()));
                $playerT->sendMessage(Messages::message("§fÉchange annulé"));
                $this->distribInventory($playerT);
                foreach ($this->salles as $index => $values) {
                    foreach ($values['place']['xuid'] as $xuid) {
                        if ($xuid == $playerT->getXuid());
                        $this->salles[$index]['status'] = true;
                        $this->salles[$index]['place'][1] = true;
                        $this->salles[$index]['place'][2] = true;
                        $this->salles[$index]['place']['xuid'] = [];
                    }
                }
            }
        }

        if ($uuidGet !== "") {
            $this->sessions[$uuidGet] = [];
        }

        if (empty($sessions)) {
            $this->salles[$salle]['status'] = true;
            $this->salles[$salle]['place'][1] = true;
            $this->salles[$salle]['place'][2] = true;
            $this->salles[$salle]['place']['xuid'] = [];
        }
    }

    public function distribInventory(Player $player): void {
        if (!$this->hasSaveInventory($player)) return;
        $invs = $this->inventories[$player->getXuid()];

        foreach ($invs as $invName => $content) {
            switch ($invName) {
                case 'inv':
                    $player->getInventory()->setContents($content);
                    break;
                case 'ender':
                    $player->getEnderInventory()->setContents($content);
                    break;
                case 'off':
                    $player->getOffHandInventory()->setContents($content);
                    break;
                case 'armor':
                    $player->getArmorInventory()->setContents($content);
                    break;
            }
        }

        unset($this->inventories[$player->getXuid()]);
    }

    public function setSessions(Player $player, Player $player2): void {
        $this->sessions[uniqid()] = [
            $player->getXuid() => false,
            $player2->getXuid() => false
        ];
    }

    public function acceptPlayer(Player $player): void {
        $sessions = [];
        $hasBreak = false;
        $uuidGet = "";
        foreach ($this->sessions as $uuid => $array) {
            foreach ($array as $xuid => $acceptValues) {
                if ($xuid == $player->getXuid()) {
                    $uuidGet = $uuid;
                    $this->sessions[$uuid][$xuid] = true;
                    $sessions = $this->sessions[$uuid];
                    $hasBreak = true;
                    break;
                }
            }
            if ($hasBreak) break;
        }

        $countBool = 0;
        foreach ($sessions as $xuid => $bool) {
            $playerT = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
            if (!$bool) {
                if ($playerT instanceof Player) {
                    $playerT->sendMessage(Messages::message("§fVotre partenaire a accepté l'échange, à votre tour d'accepter."));
                }
            } else $countBool++;
        }


        if ($countBool === 2) {
            foreach ($sessions as $xuid => $bool) {
                $playerT = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                if ($playerT instanceof Player) {
                    $playerT->teleport(new Position(57, 85, -52, \pocketmine\Server::getInstance()->getWorldManager()->getDefaultWorld()));
                    if ($this->hasSaveInventory($playerT)) {
                        unset($this->inventories[$playerT->getXuid()]);
                    }
                    $playerT->sendMessage(Messages::message("§fÉchange sécurisé terminé !"));
                    foreach ($this->salles as $index => $values) {
                        foreach ($values['place']['xuid'] as $xuid) {
                            if ($xuid === $playerT->getXuid());
                            $this->salles[$index]['status'] = true;
                            $this->salles[$index]['place'][1] = true;
                            $this->salles[$index]['place'][2] = true;
                            $this->salles[$index]['place']['xuid'] = [];
                        }
                    }
                }
            }

            if ($uuidGet !== "") {
                $this->sessions[$uuidGet] = [];
            }
        }
    }


    public function refusePlayer(Player $player): void {
        foreach ($this->sessions as $uuid => $array) {
            foreach ($array as $xuid => $acceptValues) {
                if ($xuid === $player->getXuid()) {
                    $this->sessions[$uuid][$xuid] = false;
                    return;
                }
            }
        }
    }
}