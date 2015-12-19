<?php

function signalHandler($signal)
{
    var_dump($signal);
    error_log('i received!!! , it is ' . $signal, 3, 'log.log');
}

pcntl_signal(SIGINT, 'signalHandler');

echo "Installing signal handler...\n";

while (true) {
    usleep(200);
    pcntl_signal_dispatch();

}
