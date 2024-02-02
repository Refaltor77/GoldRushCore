<?php

namespace core\async;

use Closure;
use core\settings\Settings;
use core\settings\traits\SettingsTrait;
use pocketmine\scheduler\AsyncTask;

class RequestAsync extends AsyncTask implements Settings
{
    use SettingsTrait;

    private $async;
    private $resultC;

    public function __construct(?Closure $async = null, ?Closure $result = null)
    {
        $this->async = $async;
        $this->resultC = $result;
    }

    public function onRun(): void
    {
        $information = $this->getDatabaseInformation();
        $db = new \mysqli(
            $information['hostname'],
            $information['username'],
            $information['password'],
            $information['database']
        );
        $async = $this->async;
        $async($this, $db);
        $db->close();
    }

    public function onCompletion(): void
    {
        $result = $this->resultC;
        if (!is_null($result) && is_callable($result)) call_user_func($result, $this);
    }
}