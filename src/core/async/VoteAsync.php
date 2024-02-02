<?php

namespace core\async;

use core\api\form\elements\Button;
use core\api\form\MenuForm;
use core\entities\AirDrops;
use core\entities\Nexus;
use core\Main;
use core\messages\Messages;
use core\settings\Ids;
use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\player\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;

class VoteAsync extends AsyncTask
{

    private string $player;


    public function __construct($player)
    {
        $this->player = $player;
    }

    public function onRun(): void
    {
        $result = Internet::getURL("https://minecraftpocket-servers.com/api/?object=votes&element=claim&key=ObiOdJedv0WMpnl1qJe3rRVQP8k28Wb1syT&username=" . str_replace(" ", "+", $this->player));
        if ($result->getBody() === "1") Internet::getURL("https://minecraftpocket-servers.com/api/?action=post&object=votes&element=claim&key=ObiOdJedv0WMpnl1qJe3rRVQP8k28Wb1syT&username=" . str_replace(" ", "+", $this->player));
        $this->setResult($result->getBody());
    }

    public function onCompletion(): void
    {
        $item = CustomiesItemFactory::getInstance()->get(Ids::KEY_COMMON);
        if (Main::getInstance()->isDisabled()) return;
        $result = $this->getResult();
        $player = Server::getInstance()->getPlayerExact($this->player);
        if ($player === null) return;
        unset($this->player);
        switch ($result) {
            case "0":

                $player->sendMessage(Messages::message("§fhttps://minecraftpocket-servers.com/server/125547/"));
                $form = new MenuForm("QRCODE_VOTE", "", [], function (Player $player, Button $button): void {

                });
                $player->sendForm($form);

                break;
            case "1":
                if ($player->getInventory()->canAddItem($item)) {
                    $player->getInventory()->addItem($item);
                } else $player->getWorld()->dropItem($player->getLocation(), $item);


                Main::getInstance()->getVotePartyManager()->add();

                Main::getInstance()->getVotePartyManager()->getVote(function (int $vote): void {
                    if ($vote >= 50) {
                        Main::getInstance()->getVotePartyManager()->vote = 0;
                        Main::getInstance()->getVotePartyManager()->reset();
                        $item = CustomiesItemFactory::getInstance()->get(Ids::KEY_RARE);
                        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                            $player->sendMessage(Messages::message("§eVOTE PARTY ! Une key rare pour toi !"));
                            if ($player->getInventory()->canAddItem($item)) {
                                $player->getInventory()->addItem($item);
                            } else $player->getWorld()->dropItem($player->getLocation(), $item);
                        }
                        switch (mt_rand(0, 1)) {
                            case 0:
                                $airdrops = new AirDrops(AirDrops::getRandomPos());
                                $airdrops->spawnToAll();
                                break;
                            case 1:
                                if (Nexus::$isRunning === false) {
                                    $totem = new Nexus(Nexus::getSpawnPosition());
                                    $totem->spawnToAll();
                                }
                                break;
                        }
                    }
                });
                Server::getInstance()->broadcastMessage(Messages::message("§6{$player->getName()}§f à voté pour le serveur !\n§6https://goldrushmc.fun/vote"));
                break;
            case "2":
                $player->sendMessage(Messages::message("§cVous avez déjà voté pour le serveur !"));
                break;
        }
    }
}