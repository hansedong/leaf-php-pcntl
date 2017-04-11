<?php

/**
 * 示例：启动一个进程，并执行某个任务，并等待任务执行结束
 */

//如果你的项目没有使用composer，需要先引入Autoloader，并注册自动加载
$autoloader = dirname(__FILE__) . '/../Autoloader.php';
require $autoloader;
\Leaf\Managers\ProcessManager\Autoloader::register();

//先实例化分布式算法的管理器
use \Leaf\Managers\ProcessManager\Process;

$process = new Process();
//task任务必须是可回调的函数，callback类型
$process->setTask('task')->run()->wait();


function task()
{
    echo 'task begin' . PHP_EOL;
    sleep(5);
    echo 'task end' . PHP_EOL;
}