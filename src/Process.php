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
     * the task to be executed of this process. it's a callback concret
     *
     * @var $mixed
     */
    protected $task = null;

    /**
     * the running status of this process
     *
     * @var bool
     */
    protected $runStatus = 0;

    /**
     * if run this process as a daemon
     *
     * @var int
     */
    protected $daemonStatus = 0;

    /**
     * Process constructor.
     *
     * @param mixed $callBack
     */
    public function __construct($callBack = null)
    {
        $this->setProcessTask($callBack);
    }

    /**
     * set the task for this process
     *
     * @param mixed $callBack
     *
     * @return $this
     */
    public function setProcessTask($callBack = null)
    {
        if ( !empty( $callBack ) && is_callable($callBack)) {
            $this->task = $callBack;
        }
        elseif ( !empty( $callBack )) {
            throw new \InvalidArgumentException('the callback for the process task is invaliad');
        }

        return $this;
    }

    /**
     * set the process running background as a daemon
     */
    public function asDaemon()
    {
        $this->daemonStatus = 1;
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

    /**
     * get the task of this process
     */
    public function getTask()
    {
        return $this->task;
    }

    protected function updateRuntimeStatus()
    {

    }

    /**
     * start this process
     * when started, the system will fork a process
     */
    public function start()
    {
        //check runtime params
        if ( !empty( $this->pid ) && ( $this->runStatus === 1 )) {
            throw new \RuntimeException('the process is already running');
        }
        $callback = $this->getTask();
        if ( !is_callable($callback)) {
            throw new \RuntimeException('the task of this process is invalid');
        }
        $pid = pcntl_fork();
        if ($pid < 0) {
            throw new \RuntimeException("fork error");
        }
        elseif ($pid > 0) {
            $this->pid = $pid;
            $this->runStatus = 1;
        }
        else {
            //set process status
            $this->pid = getmypid();
            $this->updateRunningStatus();
            $this->runStatus = 1;
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
            //the process forked should better exit when finished it's task
            exit( 0 );
        }
    }

}