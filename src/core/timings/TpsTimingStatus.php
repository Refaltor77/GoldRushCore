<?php

namespace core\timings;

class TpsTimingStatus
{
    private int $timeExecute = 10; // ticks
    public int $time = 0;


    public static function start(): self { return new TpsTimingStatus(); }

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