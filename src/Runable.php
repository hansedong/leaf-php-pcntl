<?php

namespace Leaf\Pcntl;

/**
 * Interface Runable
 * you should implements this interface in your process task class
 *
 * @package Leaf\Pcntl
 */
interface Runable
{


    public function run($callBack);

}