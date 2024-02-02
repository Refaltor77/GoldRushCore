<?php

namespace core\timings;

class BossBarTiming
{
    private int $timeExecute = 20; // ticks
    public int $time = 0;


    public static function start(): self { return new BossBarTiming(); }

    public function update(): void {
        $this->time++;
    }

    public function execute(callable $callable): void {
        if ($this->time >= $this->timeExecute) {
            $callable();
            $this->time = 0;
        }
    }
}