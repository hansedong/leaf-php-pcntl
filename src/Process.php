<?php

namespace Leaf\Pcntl;

/**
 * Class Process
 * process
 *
 * @package Leaf\Pcntl
 */
class Process
{

    /**
     * the pid of this process
     *
     * @var int
     */
    protected $pid = 0;

    /**
     * the task to be executed of this process
     *
     * @var null
     */
    protected $task = null;

    /**
     * the running status of this process
     *
     * @var bool
     */
    protected $runStatus = false;

    public function __construct($callable = null)
    {
    }

    /**
     * get the process id of the process
     *
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }

    public function start()
    {
        if ( !empty( $this->pid ) && $this->runStatus()) {
            throw new \LogicException("the process is already running");
        }

        $callback = $this->getCallable();

        $pid = pcntl_fork();
        if ($pid < 0) {
            throw new \RuntimeException("fork error");
        }
        elseif ($pid > 0) {
            $this->pid = $pid;
            $this->running = true;
            $this->started = true;
        }
        else {
            $this->pid = getmypid();
            $this->signal();
            foreach ($this->signal_handlers as $signal => $handler) {
                pcntl_signal($signal, $handler);
            }

            if (array_key_exists(self::BEFORE_START, $this->callbacks)) {
                $result = call_user_func($this->callbacks[self::BEFORE_START]);
                if ($result !== true) {
                    exit( 0 );
                }
            }

            $result = call_user_func($callback);

            if (array_key_exists(self::AFTER_FINISHED, $this->callbacks)) {
                call_user_func($this->callbacks[self::AFTER_FINISHED], $result);
            }

            exit( 0 );
        }
    }

}