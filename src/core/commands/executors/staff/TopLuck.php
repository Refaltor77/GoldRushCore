<?php

namespace core\commands\executors\staff;

use core\api\gui\ChestInventory;
use core\api\gui\DoubleChestInventory;
use core\commands\Executor;
use core\Main;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\SoundTrait;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class TopLuck extends Executor
{
    use SoundTrait;

    public function __construct(string $name = "topluck", string $description = "", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("topluck.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if (!isset($args[0])) {
            $inv = new DoubleChestInventory();
            $inv->setName("§7Topluck - Faction");

            $content = [];
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $sessions = Main::getInstance()->getTopLuckManager()->getSession($player);
                $solid = $sessions['solid'];
                $ore = $sessions['ore'];
                $percent = (($ore <= 0 ? 1 : $ore) / ($solid <= 0 ? 1 : $solid)) * 100;
                if ($percent === 100) $percent = 0;
                $item = VanillaBlocks::MOB_HEAD()->asItem();
                $item->setCustomName($player->getName() . " " . $percent . "%");
                $item->getNamedTag()->setString('xuid', $player->getXuid());
                $content[] = $item;
            }

            $inv->setContents($content);
            $inv->setClickCallback(function (Player $player, DoubleChestInventory $inventoryCustom, Item $sourceItem, Item $targetItem, int $slot) use ($inv) : void {
                if ($sourceItem->getNamedTag()->getString('xuid', 'none') !== 'none') {
                    $xuid = $sourceItem->getNamedTag()->getString('xuid');
                    $sessions = Main::getInstance()->getTopLuckManager()->getSessionByXuid($xuid);
                    if (!is_null($sessions)) {

                        $player->removeCurrentWindow();
                        $solid = $sessions['solid'];
                        $ore = $sessions['ore'];
                        $percent = (($ore <= 0 ? 1 : $ore) / ($solid <= 0 ? 1 : $solid)) * 100;
                        if ($percent === 100) $percent = 0;

                        $inv2 = new ChestInventory();
                        $inv2->setName($sourceItem->getCustomName());
                        $inv2->setItem(12, VanillaItems::GUNPOWDER()->setCustomName("§cTéléportation"));
                        $inv2->setItem(13, VanillaItems::GUNPOWDER()->setCustomName("§cPourcentage : " . $percent . "%"));
                        $inv2->setClickCallback(function (Player $player, ChestInventory $inventoryCustom, Item $sourceItem, Item $targetItem, int $slot) use ($xuid, $inv2) : void {
                            if ($slot === 12) {
                                $playerTarget = Main::getInstance()->getDataManager()->getPlayerXuid($xuid);
                                if ($playerTarget instanceof CustomPlayer) {
                                    $player->teleport($playerTarget->getPosition());
                                    $player->removeCurrentWindow();
                                } else {
                                    $player->sendMessage(Messages::message("§cLe joueur est hors ligne."));
                                    $this->sendErrorSound($player);
                                }
                            }
                            $inv2->transacCancel();
                        });
                        $inv2->send($player);
                    }
                }
                $inv->transacCancel();
            });
            $inv->send($player);
        } else {
            // TODO: player args
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}