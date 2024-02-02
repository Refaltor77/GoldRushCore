<?php

namespace core\events;

use core\Main;
use pocketmine\event\Event;
use pocketmine\utils\TextFormat;

class LogEvent extends Event
{
    /**
     * /pay, recois de l'argent, recois de l'argent
     */
    const MONEY_TYPE = 'money'; //V
    /**
     * modification du rank
     */
    const RANK_TYPE = 'rank'; //V
    /**
     * quand un joueur tue un autre
     */
    const KILL_TYPE = 'kill'; //V
    /**
     * quand il achete quelque chose dans le shop/hdv
     */
    const BUY_TYPE = 'buy';
    /**
     * quand il vend quelque chose dans le shop/hdv
     * //todo; buy/sell
     */
    const SELL_TYPE = 'sell';
    /**
     * quand il execute une command
     */
    const COMMAND_TYPE = 'command'; //V
    /**
     * quand il rejoint le serveur
     */
    const JOIN_TYPE = 'join'; //V
    /**
     * quand il quitte le serveur
     */
    const LEAVE_TYPE = 'leave'; //v
    /**
     * quand il envoie un message dans le chat
     */
    const MESSAGE_TYPE = 'message'; //v
    /**
     * quand il casse un block d'or
     */
    const OR_TYPE = 'or'; //v
    /**
     * quand il recois/enleve une sanction
     */
    const SANCTION_TYPE = 'sanction'; //V
    /**
     *  quand il passe un jobs, reclame ses recompense de job
     */
    const JOB_TYPE = 'job'; //V
    /**
     * quand il cree un home/del ou se tp a un home
    */
    const HOME_TYPE = 'home'; //V
    /**
     * tpa, tpahere, tpaccept, tpdeny
     * //todo: add le /tp dans les logs
    **/
    const TP_TYPE = 'tp'; //V
    /**
     * genre koth,largage,totem,boss
     */
    const EVENT_TYPE = 'event'; //V
    /**
     * ouverture de box
     */
    const BOX_TYPE = 'box'; //V
    /**
     * faction create,del,join,leave
     */
    const FACTION_TYPE = 'faction'; //V

    private string $transformed;

    public function __construct(string $message, string $type)
    {
        $this->transformed = "[" . date("Y-m-d H:i:s", time()) . "] [LOGS/" . strtoupper($type) . "] //: " . TextFormat::clean($message);
    }


    public function call(): void
    {
        parent::call();

        Main::getInstance()->threadManager->worker->send($this->transformed);
    }

}