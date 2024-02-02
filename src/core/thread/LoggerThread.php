<?php

namespace core\thread;

use pmmp\thread\Thread;
use pmmp\thread\ThreadSafeArray;
use function fclose;
use function fopen;
use function fwrite;

class LoggerThread extends BaseThread
{
    private string $dataFolder;

    public function __construct(string $datafolder)
    {
        $this->dataFolder = $datafolder;
        parent::__construct();
    }

    public function write(string $line) : void{
        $this->synchronized(function() use ($line) : void{
            $this->buffer[] = $line;
            $this->notify();
        });
    }

    public function syncFlushBuffer() : void{
        $this->synchronized(function() : void{
            $this->syncFlush = true;
            $this->notify(); //write immediately
        });
        $this->synchronized(function() : void{
            while($this->syncFlush){
                $this->wait(); //block until it's all been written to disk
            }
        });
    }

    public function shutdown() : void{
        $this->synchronized(function() : void{
            $this->shutdown = true;
            $this->notify();
        });
        $this->join();
    }

    private function writeLogStream($logResource) : void{
        while(($chunk = $this->buffer->shift()) !== null){
            fwrite($logResource, $chunk);
        }

        $this->synchronized(function() : void{
            if($this->syncFlush){
                $this->syncFlush = false;
                $this->notify();
            }
        });
    }

    public function run() : void{
        $logResource = fopen($this->dataFolder . 'logs/' . date("d-m-Y") . '-log.log', 'a+');
        while(!$this->shutdown){
            $this->writeLogStream($logResource);
            $this->synchronized(function() : void{
                if(!$this->shutdown && !$this->syncFlush){
                    $this->wait();
                }
            });
        }
        fclose($logResource);
    }


}