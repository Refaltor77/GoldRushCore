<?php

namespace core\commands\executors\staff;

use core\commands\Executor;
use core\entities\vanilla\Chicken;
use core\entities\vanilla\Cow;
use core\entities\vanilla\Creeper;
use core\entities\vanilla\Enderman;
use core\entities\vanilla\Mouton;
use core\entities\vanilla\Pig;
use core\entities\vanilla\Skeleton;
use core\entities\vanilla\ZombieCustom;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\Server;

class Summon extends Executor
{

    public function __construct(string $name = "summon", string $description = "faire apparaitre une entité", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
        $this->setPermission("summon.use");
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        if(Server::getInstance()->isOp($sender->getName())){
            if(isset($args[1]) && is_int((int)$args[1])) {
                $nombre = $args[1];
                switch ($args[0]) {
                    case 'chicken':
                        $entity = new Chicken($sender->getLocation());
                        break;
                    case 'cow':
                        $entity = new Cow($sender->getLocation());
                        break;
                    case 'creeper':
                        $entity = new Creeper($sender->getLocation());
                        break;
                    case 'enderman':
                        $entity = new Enderman($sender->getLocation());
                        break;
                    case 'sheep':
                        $entity = new Mouton($sender->getLocation());
                        break;
                    case 'pig':
                        $entity = new Pig($sender->getLocation());
                        break;
                    case 'skeleton':
                        $entity = new Skeleton($sender->getLocation());
                        break;
                    case 'zombie':
                        $entity = new ZombieCustom($sender->getLocation());
                        break;
                    default:
                        $sender->sendMessage("§cEntité inconnue");
                        return;
                }

                for ($i = 1; $i < $nombre; $i++) {
                    $entity->spawnToAll();
                    $entity = new ($entity::class)($sender->getLocation());
                }
                $sender->sendMessage("§aVous avez fait apparaitre §e" . $nombre . " §aentité(s) de type §e" . $args[0]);
            }else{
                $sender->sendMessage("§cVeuillez indiquer un nombre");
            }
        }else{
            $sender->sendMessage("§cVous n'avez pas la permission d'utiliser cette commande");
        }
    }


    public function getCommandData(): CommandData
    {
        $this->addOptionEnum(0,"string",false,"entite",[
            "chicken",
            "cow",
            "creeper",
            "enderman",
            "sheep",
            "pig",
            "skeleton",
            "zombie"
        ]);
        $this->addOption(1,"nombre",false,self::ARG_TYPE_INT);
        return parent::getCommandData();
    }
}