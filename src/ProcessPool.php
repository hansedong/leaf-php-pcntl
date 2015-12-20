<?php

namespace Leaf\Pcntl;

use Leaf\Pcntl\ProcessPool\ProcessPoolDynamic;
use Leaf\Pcntl\ProcessPool\ProcessPoolStatic;
use Leaf\Pcntl\ProcessPool\ProcessPoolNormal;

/**
 * Class ProcessPool
 * process pool manager. It's designed as PHP-FPM. you can set a min/max child processes for this manager for the
 * manager dynamically or a fixed of child processes staticly
 *
 * @package Leaf\Pcntl
 */
class ProcessPool
{

    /**
     * process pool mode
     *
     * @var string it can be 'normal'、'static'、'dynamic' etc.
     */
    protected $mode = 'normal';

    /**
     * the type of the pool
     *
     * @var ProcessPoolStatic|ProcessPoolNormal|ProcessPoolDynamic
     */
    protected $pool = null;

    /**
     * ProcessPool constructor.
     *
     * @param string $mode
     */
    public function __construct($mode = 'normal')
    {
        if (in_array($mode, ['normal', 'static', 'dynamic'])) {
            $this->mode = $mode;
            $this->initProcessPool();
        }
        else {
            throw new \InvalidArgumentException('the running mode of the process pool is invalid! the mode can be
            normal、static、dynamic etc.');
        }
    }

    /**
     * init process pool according to the process type
     *
     * @return $this
     */
    protected function initProcessPool()
    {
        switch ($this->mode) {
            case 'static':
                $pool = new ProcessPoolFixed();
                break;
            case 'dynamic':
                $pool = new ProcessPoolDynamic();
                break;
            default:
                $pool = new ProcessPoolNormal();
        }
        $this->pool = $pool;

        return $this;
    }

    public function execute(Process $process)
    {
        return $this->pool->execute($process);
    }

    public function __call($name, $arguments)
    {
        if (is_callable([$this->pool, 'wait'])) {
            if (isset( $arguments[0] )) {
                $this->pool->$name($arguments[0]);
            }
            else {
                $this->pool->$name();
            }
        }
    }

}