<?php

echo('QS Testing Script' . PHP_EOL);

passthru('composer install');

echo('$_ENV Array' . PHP_EOL);
var_dump($_ENV);
echo(PHP_EOL);
echo('$_POST Array' . PHP_EOL);
var_dump($_POST);
echo(PHP_EOL);
echo('End QS Testing Script' . PHP_EOL);
