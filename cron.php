<?php

require_once ("Querier.php");
require_once ("Checker.php");

$platforms = $querierInstance = Querier::getInstance()->fetchAllPlatforms();

//Counter for number of processes
$i = 1;

foreach($platforms as $platform) {
    $pid = pcntl_fork();
    if ( ! $pid) {
        echo 'starting child ', $i, PHP_EOL;
        Checker::checkStatus($platform['name']);
        exit();
    }
    $i++;
}

//Wait for all the subprocesses to complete to avoid zombie processes
foreach($platforms as $key)
{
    pcntl_wait($status);
}
