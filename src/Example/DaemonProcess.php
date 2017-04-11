<?php

/**
 * 示例：设置某个进程为守护进程
 * 我们可以让其sleep 100s，然后关闭终端，重新开一个终端，看进程是否依然存在
 */

//如果你的项目没有使用composer，需要先引入Autoloader，并注册自动加载
$autoloader = dirname(__FILE__) . '/../Autoloader.php';
require $autoloader;
\Leaf\Managers\ProcessManager\Autoloader::register();

//先实例化分布式算法的管理器
use \Leaf\Managers\ProcessManager\Process;

$process = new Process();
//task任务必须是可回调的函数，callback类型
$process->setTask('task')->run(1);


function task()
{
    echo 'task begin' . PHP_EOL;
    sleep(100);
    echo 'task end' . PHP_EOL;
}