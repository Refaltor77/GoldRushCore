<?php

namespace core\thread;

use Closure;
use pmmp\thread\Thread;
use pmmp\thread\ThreadSafeArray;

class BaseThread extends Thread
{
    protected ThreadSafeArray $buffer;
    protected bool $syncFlush = false;
    protected bool $shutdown = false;

    public function __construct()
    {
        $this->buffer = new ThreadSafeArray();
    }

    public function run() : void{
        throw new \Error("This method must be overridden");
    }
}