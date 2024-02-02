<?php

namespace core\async;

use pocketmine\scheduler\AsyncTask;

class Async extends AsyncTask
{
    private $async;
    private $resultC;

    public function __construct($async, $result = null)
    {
        $this->async = $async;
        $this->resultC = $result;
    }

    public function onRun(): void
    {
        $async = $this->async;
        $async($this);
    }

    public function onCompletion(): void
    {
        $result = $this->resultC;
        if (!is_null($result) && is_callable($result)) call_user_func($result, $this);
    }
}