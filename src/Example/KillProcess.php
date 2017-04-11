<?php

/**
 * 示例：启动一个进程，并执行某个耗时任务（sleep100秒），在子进程还没有执行完的时候，主进程将其杀死
 * 注意：主进程杀死子进程，需要借助于信号，所以，代码入口，必须声明 declare(ticks=1)
 */

//声明使用信号（必须声明，否则子进程无法捕获信号）
declare(ticks=1);

//如果你的项目没有使用composer，需要先引入Autoloader，并注册自动加载
$autoloader = dirname(__FILE__) . '/../Autoloader.php';
require $autoloader;
\Leaf\Managers\ProcessManager\Autoloader::register();

//先实例化分布式算法的管理器
use \Leaf\Managers\ProcessManager\Process;
use \Leaf\Managers\ProcessManager\SignalHandler;

/**
 * 子进程工作100s
 */
function task()
{
    echo 'task begin' . PHP_EOL;
    $i = 0;
    while ($i < 100) {
        echo '子进程运行：' . $i . PHP_EOL;
        sleep(1);
        $i++;
    }
    echo 'task end' . PHP_EOL;
}

$process = new Process();
//task任务必须是可回调的函数，callback类型
$process->setTask('task')->run();
sleep(5);   //让运行5s后，给子进程发送信号
$process->kill();