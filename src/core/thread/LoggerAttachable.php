<?php

namespace core\thread;

use pocketmine\thread\log\AttachableThreadSafeLogger;
use pocketmine\utils\Utils;
use pmmp\thread\Thread as NativeThread;

class LoggerAttachable extends AttachableThreadSafeLogger implements \BufferedLogger
{

    private LoggerThread $loggerThread;

    public function __construct(string $datafolder)
    {
        $this->loggerThread = new LoggerThread($datafolder);
        $this->loggerThread->start(NativeThread::INHERIT_NONE);
    }

    public function buffer(\Closure $c): void
    {
        $this->synchronized($c);
    }

    public function emergency($message)
    {
        $this->send($message."\n");
    }

    public function alert($message)
    {
        $this->send($message."\n");
    }

    public function critical($message)
    {
        $this->send($message."\n");
    }

    public function error($message)
    {
        $this->send($message."\n");
    }

    public function warning($message)
    {
        $this->send($message."\n");
    }

    public function notice($message)
    {
        $this->send($message."\n");
    }

    public function info($message)
    {
        $this->send($message."\n");
    }

    public function debug($message)
    {
        $this->send($message."\n");
    }

    public function log($level, $message)
    {
        $this->send($message."\n");
    }

    public function send(string $message, ): void{
        $this->synchronized(function() use ($message) : void{
            $this->loggerThread->write($message."\n");
        });
    }


    public function logException(\Throwable $e, $trace = null)
    {
        $this->critical(implode("\n", Utils::printableExceptionInfo($e, $trace)));
        $this->loggerThread->syncFlushBuffer();
    }

    public function __destruct(){
        if(!$this->loggerThread->isJoined() && NativeThread::getCurrentThreadId() === $this->loggerThread->getCreatorId()){
            $this->shutdownLogWriterThread();
        }
    }

    public function shutdownLogWriterThread() : void{
        $this->loggerThread->shutdown();
    }
}