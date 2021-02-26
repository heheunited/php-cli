<?php
require_once 'config/init.php';

use App\Console\IpAustraliaCommand;
use Symfony\Component\Console\Application;

$app = new Application();

$app->add(new IpAustraliaCommand());

$app->run();
