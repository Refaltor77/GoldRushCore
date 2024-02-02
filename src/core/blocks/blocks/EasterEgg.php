<?php

namespace core\blocks\blocks;

use core\Main;
use core\managers\easteregg\EasterEggManager;
use core\player\CustomPlayer;
use core\tasks\BlockJumpTask;
use core\traits\UtilsTrait;
use customiesdevs\customies\block\permutations\Permutable;
use customiesdevs\customies\block\permutations\RotatableTrait;
use pocketmine\block\Block;
use pocketmine\block\Opaque;
use pocketmine\block\Transparent;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\XpCollectSound;

class EasterEgg extends Transparent implements Permutable
{
    use RotatableTrait;

    public function getEasterEggManager(): EasterEggManager {
        return Main::getInstance()->getEasterEggManager();
    }


    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        $easterEggManager = $this->getEasterEggManager();
        if (!$easterEggManager->isPlayer($player->getXuid())) {
            $easterEggManager->addPlayer($player->getXuid());
        }
        if ($easterEggManager->isEasterEgg($this->position)) {

            if (!$easterEggManager->playerHasEasterEgg($player->getXuid(), $easterEggManager->getEasterEgg($this->position)["id"])) {
                $easterEggManager->addPlayerEasterEgg($player->getXuid(), $easterEggManager->getEasterEgg($this->position));

                $count = count($easterEggManager->getAllEasterEgg());
                $playerCount = $easterEggManager->getPlayerCount($player->getXuid());
                $player->getWorld()->addSound($this->position, new XpCollectSound());
                $player->sendMessage("§l§a(§r§a!§l§a) §r§aVous avez trouvé un easteregg §b{$playerCount}/{$count} !§a");
            } else {
                $player->sendMessage("§l§c(§r§c!§l§c) §r§cVous avez déjà trouvé cet easteregg !§c");
            }
        }
        return parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        $eastereggManager = $this->getEasterEggManager();
        $eastereggManager->addEasterEgg($this->position);
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

   public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
   {
       $eastereggManager = $this->getEasterEggManager();
       $eastereggManager->removeEasterEgg($this->position);
       return parent::onBreak($item, $player, $returnedItems);
   }
}