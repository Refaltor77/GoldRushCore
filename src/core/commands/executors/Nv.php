<?php

namespace core\commands\executors;

use core\commands\Executor;
use core\messages\Messages;
use core\player\CustomPlayer;
use core\traits\SoundTrait;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Nv extends Executor
{
    use SoundTrait;

    public static array $nv = [];

    public function __construct(string $name = "nv", string $description = "Voir dans la nuit.", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("nv.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if ($sender->getEffects()->has(VanillaEffects::NIGHT_VISION())) {
            if (isset(self::$nv[$sender->getXuid()])) {
                unset(self::$nv[$sender->getXuid()]);
                $sender->getEffects()->remove(VanillaEffects::NIGHT_VISION());
                $sender->sendMessage(Messages::message("§fNight vision désactivé."));
                $this->sendSuccessSound($sender);
            } else {
                $sender->sendMessage(Messages::message("§cVous avez déjà l'effet night vision de votre casque."));
                $this->sendErrorSound($sender);
            }
        } else {
            $sender->getEffects()->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), 20 * 25555555, 0, false));
            $sender->sendMessage(Messages::message("§fNight vision activé."));
            $this->sendSuccessSound($sender);
            self::$nv[$sender->getXuid()] = true;
        }
    }

    protected function loadOptions(?Player $player): CommandData
    {
        return parent::loadOptions($player);
    }
}