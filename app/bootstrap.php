<?php
/**
 * Copyright 2015 Simon Erhardt <me@rootlogin.ch>
 *
 * This file is part of kengrabber.
 * kengrabber is free software: you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * kengrabber is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with kengrabber.
 * If not, see http://www.gnu.org/licenses/.
 */

$values = array();
if(defined("PHAR")) {
    require_once("phar://kengrabber.phar/vendor/autoload.php");

    $values['app_root'] = dirname(Phar::running(false));
    $values['root'] = dirname(__DIR__);
} else {
    require_once("vendor/autoload.php");

    $values['app_root'] = $values['root'] = dirname(__DIR__);
}

use rootLogin\Kengrabber\Kengrabber;
use Symfony\Component\Console\Input\ArgvInput;

$input = new ArgvInput();
$env = $input->getParameterOption(array('--env', '-e'), getenv('APP_ENV') ?: 'dev');
$debug = $input->hasParameterOption(array('--debug'));

//Remove the options
$bad = ['--env', '-e', '--debug'];
foreach($_SERVER['argv'] as $key => $value) {
    if(in_array($value, $bad)) {
        unset($_SERVER['argv'][$key]);
    }
}


$values['debug'] = isset($debug) ? $debug : false;
$values['env'] = isset($env) ? $env : 'prod';

$app = new Kengrabber($values);
$app->run();