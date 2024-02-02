<?php

namespace core\timings;

class ChestFinderTiming
{
    private int $timeExecute = 40; // ticks
    public int $time = 0;


    public static function start(): self { return new ChestFinderTiming(); }

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