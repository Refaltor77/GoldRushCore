<?php

namespace core\commands\executors;

use core\api\form\CustomForm;
use core\api\form\CustomFormResponse;
use core\api\form\elements\Input;
use core\api\webhook\Embed;
use core\api\webhook\Message;
use core\api\webhook\Webhook;
use core\commands\Executor;
use core\player\CustomPlayer;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\player\Player;

class Report extends Executor
{
    public function __construct(string $name = "report", string $description = "Permet de rapporter un bug", ?string $usageMessage = null, array $aliases = [], int $permission = PlayerPermissions::VISITOR)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function onRun(CustomPlayer $sender, string $commandLabel, array $args)
    {
        $form = new CustomForm("§l§4Report",[
            new Input("§l§4Raison §l§6»", "Raison du report")
        ],function (Player $player,CustomFormResponse $response): void{
            $raison = $response->getInput()->getValue();
            $player->sendMessage("§lReport §l§6» §r§fVotre report a bien été envoyé");
            $webhook = new Webhook("https://discord.com/api/webhooks/1160949734941413498/sL-9wNJvmY8cbDMQbNQ2BFI9-FU9EAeVJYZi2EK3Hbx_XiboPGxXDU2UM9iDO9xQr7ma");

            $embed = new Embed();
            $embed->setTitle("Report");
            $embed->setDescription("Un joueur a report un bug");
            $embed->addField("Raison", $raison,true);
            $embed->addField("Joueur", $player->getName(),true);
            $embed->setColor(0x00FF00);
            $msg = new Message();
            $msg->addEmbed($embed);
            $webhook->send($msg);
        });

        $sender->sendForm($form);
    }
}