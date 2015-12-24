<?php

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoload.php';

function doSt()
{
    $sleep = 1;
    sleep($sleep);
    echo $sleep . '：hello leaf!!' . PHP_EOL;
}


$processPool = new Leaf\Pcntl\ProcessPool\ProcessPoolStatic();

for ($i = 0; $i < 20; $i++) {
    $processPool->addProcess(new Leaf\Pcntl\Process('doSt'));
}

$processPool->setFixedProcessNumber(2)->runAsDaemon();

?>
