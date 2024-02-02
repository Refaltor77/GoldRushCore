<?php

namespace core\commands\executors;

use core\api\gui\DoubleChestInventory;
use core\commands\Executor;
use core\Main;
use core\managers\Manager;
use core\player\CustomPlayer;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Event extends Executor
{
    const SLOT_BLACKLIST = [0, 1, 9, 7, 8, 17, 36, 45, 46, 44, 53, 52, 18, 27, 35, 26, 2, 3, 4, 5, 6, 47, 48, 49, 50, 51];


    public function __construct(string $name = "event", string $description = "Voir le plannings événementiels", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $inv = new DoubleChestInventory();
        $inv->setName("§6- §fPlannings événementiels §6-");
        $redGlass = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED())->asItem();
        foreach (self::SLOT_BLACKLIST as $i) $inv->setItem($i, $redGlass);
        $inv->setViewOnly(true);
        $inv->setItem(19, VanillaBlocks::WOOL()->setColor(DyeColor::GREEN())->asItem()->setLore([
            "",
            "§f- §l6§r§f Événements",
            "",
            "§f10h00 §8- §eJobs farmeur x2 §7(1h)",
            "§f12h00 §8- §eJobs mineur x2 §7(1h)",
            "§f13h00 §8- §eJChest Refill",
            "§f14h00 §8- §eJobs bucheron x2 §7(1h)",
            "§f16h00 §8- §eJobs farmeur x2 §7(1h)",
            "§f18h00 §8- §eLarguage en warzone",
            "§f19h00 §8- §eBoss Troll en warzone",
            "§f20h00 §8- §eJChest Refill",
            "§f21h00 §8- §eTotem",
            "§f00h00 §8- §eJobs farmeur x2 §7(1h)",
            "§f02h00 §8- §eJobs mineur x2 §7(1h)",
        ])->setCustomName("§6Planning §lLundi"));
        $inv->setItem(20, VanillaBlocks::WOOL()->setColor(DyeColor::CYAN())->asItem()->setLore([
            "",
            "§f- §l6§r§f Événements",
            "",
            "§f10h00 §8- §eJobs farmeur x2 §7(1h)",
            "§f12h00 §8- §eJobs bucheron x2 §7(1h)",
            "§f13h00 §8- §eJChest Refill",
            "§f14h00 §8- §eJobs mineur x2 §7(1h)",
            "§f16h00 §8- §eJobs hunter x2 §7(1h)",
            "§f18h00 §8- §eKOTH §7(/koth)",
            "§f19h00 §8- §eBoss Troll en warzone",
            "§f20h00 §8- §eJChest Refill",
            "§f22h00 §8- §eLarguage en warzone",
            "§f00h00 §8- §eJobs farmeur x2 §7(1h)",
            "§f02h00 §8- §eJobs bucheron x2 §7(1h)",
        ])->setCustomName("§6Planning §lMardi"));
        $inv->setItem(21, VanillaBlocks::WOOL()->setColor(DyeColor::RED())->asItem()->setLore([
            "",
            "§f- §l8§r§f Événements",
            "",
            "§f10h00 §8- §eJobs mineur x2 §7(1h)",
            "§f12h00 §8- §eJobs hunter x2 §7(1h)",
            "§f13h00 §8- §eJChest Refill",
            "§f14h00 §8- §eJobs farmeur x2 §7(1h)",
            "§f14h30 §8- §eLarguage en warzone",
            "§f16h00 §8- §eJobs mineur x2 §7(1h)",
            "§f18h00 §8- §eNexus",
            "§f19h00 §8- §eBoss Troll en warzone",
            "§f20h00 §8- §eKOTH §7(/koth)",
            "§f21h00 §8- §eJChest Refill",
            "§f22h00 §8- §eChasse au trésor",
            "§f00h00 §8- §eJobs mineur x2 §7(1h)",
            "§f02h00 §8- §eJobs hunter x2 §7(1h)",
        ])->setCustomName("§6Planning §lMercredi"));
        $inv->setItem(22, VanillaBlocks::WOOL()->setColor(DyeColor::PURPLE())->asItem()->setLore([
            "",
            "§f- §l6§r§f Événements",
            "",
            "§f10h00 §8- §eJobs farmeur x2 §7(1h)",
            "§f12h00 §8- §eJobs mineur x2 §7(1h)",
            "§f13h00 §8- §eJChest Refill",
            "§f14h00 §8- §eJobs chasseur x2 §7(1h)",
            "§f16h00 §8- §eJobs bucheron x2 §7(1h)",
            "§f18h00 §8- §eLarguage en warzone",
            "§f19h00 §8- §eBoss Troll en warzone",
            "§f20h00 §8- §eJChest Refill",
            "§f21h00 §8- §eNexus",
            "§f00h00 §8- §eJobs mineur x2 §7(1h)",
            "§f02h00 §8- §eJobs farmeur x2 §7(1h)",
        ])->setCustomName("§6Planning §lJeudi"));
        $inv->setItem(23, VanillaBlocks::WOOL()->setColor(DyeColor::LIGHT_BLUE())->asItem()->setLore([
            "",
            "§f- §l6§r§f Événements",
            "",
            "§f10h00 §8- §eJobs farmeur x2 §7(1h)",
            "§f12h00 §8- §eJobs bucheron x2 §7(1h)",
            "§f13h00 §8- §eJChest Refill",
            "§f14h00 §8- §eJobs mineur x2 §7(1h)",
            "§f16h00 §8- §eJobs chasseur x2 §7(1h)",
            "§f18h00 §8- §eKOTH §7(/koth)",
            "§f19h00 §8- §eBoss Troll en warzone",
            "§f20h00 §8- §eJChest Refill",
            "§f22h00 §8- §eLarguage en warzone",
            "§f00h00 §8- §eJobs hunter x2 §7(1h)",
            "§f02h00 §8- §eJobs bucheron x2 §7(1h)",
        ])->setCustomName("§6Planning §lVendredi"));
        $inv->setItem(24, VanillaBlocks::WOOL()->setColor(DyeColor::BROWN())->asItem()->setLore([
            "",
            "§f- §l9§r§f Événements",
            "",
            "§f08h00 §8- §eJobs farmeur x2",
            "§f10h00 §8- §eJobs bucheron x2",
            "§f12h00 §8- §eJobs mineur x2",
            "§f13h00 §8- §eJChest Refill",
            "§f14h00 §8- §eJobs chasseur x2",
            "§f14h30 §8- §eLarguage en warzone",
            "§f16h00 §8- §eKOTH §7(/koth)",
            "§f18h00 §8- §eNexus",
            "§f19h00 §8- §eBoss Troll en warzone",
            "§f20h00 §8- §eChasse au trésor",
            "§f21h00 §8- §eJChest Refill",
            "§f00h00 §8- §eJobs mineur x2 §7(1h)",
            "§f02h00 §8- §eJobs bucheron x2 §7(1h)",
            "§f04h00 §8- §eJobs farmeur x2 §7(1h)",
        ])->setCustomName("§6Planning §lSamedi"));
        $inv->setItem(25, VanillaBlocks::WOOL()->setColor(DyeColor::LIME())->asItem()->setLore([
            "",
            "§f- §l9§r§f Événements",
            "",
            "§f08h00 §8- §eJobs bucheron x2",
            "§f10h00 §8- §eJobs mineur x2",
            "§f12h00 §8- §eJobs farmeur x2",
            "§f13h00 §8- §eJChest Refill",
            "§f14h00 §8- §eJobs chasseur x2",
            "§f14h30 §8- §eLarguage en warzone",
            "§f16h00 §8- §eKOTH §7(/koth)",
            "§f18h00 §8- §eNexus",
            "§f19h00 §8- §eBoss Troll en warzone",
            "§f20h00 §8- §eChasse au trésor",
            "§f21h00 §8- §eJChest Refill",
            "§f00h00 §8- §eJobs hunter x2 §7(1h)",
            "§f02h00 §8- §eJobs farmeur x2 §7(1h)",
            "§f04h00 §8- §eJobs bucheron x2 §7(1h)",
        ])->setCustomName("§6Planning §lDimanche"));
        $inv->send($sender);
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}