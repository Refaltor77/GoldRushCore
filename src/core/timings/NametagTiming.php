<?php

namespace core\timings;

class NametagTiming
{
    private int $timeExecute = 100;
    public int $time = 0;


    public static function start(): self { return new NametagTiming(); }

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