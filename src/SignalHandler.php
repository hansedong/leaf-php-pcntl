<?php

/**
 * PHP7.1以下，有2种监测信号的方法，一种是 delclare(ticks=1)，一种是pcntl_signal_dispatch
 * declare的方式要求在php代码变以前就得声明，也就是说，在php入口中声明，如果写到类文件中，通过自动加载方式引入，无效。
 * pcntl_signal_dispatch方式，必须在子进程中，手动循环调用
 * 相比2种方法，pcntl_signal_dispatch方式太不智能，也很麻烦，子进程本来就是处理业务逻辑的，还要加上这个信号检测，我个人觉得这种设计不合理
 * declare的方式，最简单，但是由于过分消耗系统资源（http://php.net/manual/zh/control-structures.declare.php），也不够好
 * 据说，PHP7.1开始，有一种新的处理信号的方式，我们不需要自己手动去捕捉了，（https://bugs.php.net/bug.php?id=71448） 但PHP7.1中还未实现
 *
 * 综合来说，推荐的方式，还是delclare方式
 * if (version_compare(PHP_VERSION, '7.0.0') < 0) {
 * declare(ticks=1);
 * }
 */

namespace Leaf\Managers\ProcessManager;

/**
 * Class Process
 */
abstract class SignalHandler
{
    public $handler = null;
    
    public function __construct()
    {
        $this->handler = [$this, 'handleSignal'];
    }
    
    public function handleSignal($signo)
    {
        
    }
    
}