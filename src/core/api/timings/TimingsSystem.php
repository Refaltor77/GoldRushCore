<?php

namespace core\api\timings;

use core\Main;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;

class TimingsSystem
{
    public int $seconds = 0;
    private ?TaskHandler $taskHandler = null;

    public static function schedule(callable $task, int $period = 20): void
    {
        $timing = new self();
        $timing->createTiming($task, $period);
    }

    public function createTiming(callable $task, int $period = 20): void
    {
        $taskHandler = Main::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($task): void {
            $task($this, $this->seconds);
            $this->seconds++;
        }), $period);

        $this->setTaskHandler($taskHandler);
    }

    private function setTaskHandler(?TaskHandler $taskHandler): void
    {
        $this->taskHandler = $taskHandler;
    }

    public function addSecondsTiming(int $seconds): void
    {
        $this->seconds += $seconds;
    }

    public function removeSecondsTiming(int $seconds): void
    {
        $this->seconds -= $seconds;
    }

    public function setSecondsTiming(int $seconds): void
    {
        $this->seconds = $seconds;
    }

    public function stopTiming(): bool
    {
        if (!$this->getTaskHandler()->isCancelled()) {
            $this->getTaskHandler()->cancel();
            $this->setTaskHandler(null);
        }
        return !$this->hasTiming();
    }

    private function getTaskHandler(): ?TaskHandler
    {
        return $this->taskHandler;
    }

    public function hasTiming(): bool
    {
        return !is_null($this->getTaskHandler());
    }

}