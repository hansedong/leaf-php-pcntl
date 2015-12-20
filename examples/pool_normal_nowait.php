<?php

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoload.php';

function doSt()
{
    sleep(5);
    echo 'hello leaf!!' . PHP_EOL;
}


$processPool = new Leaf\Pcntl\ProcessPool();

$processPool->
execute(new Leaf\Pcntl\Process('doSt'))->
execute(new Leaf\Pcntl\Process('doSt'))->
execute(new Leaf\Pcntl\Process('doSt'))->
execute(new Leaf\Pcntl\Process('doSt'))->
execute(new Leaf\Pcntl\Process('doSt'))->
execute(new Leaf\Pcntl\Process('doSt'))->
execute(new Leaf\Pcntl\Process('doSt'))->
execute(new Leaf\Pcntl\Process('doSt'))->
execute(new Leaf\Pcntl\Process('doSt'))->
execute(new Leaf\Pcntl\Process('doSt'));

?>