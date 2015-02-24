<?php
date_default_timezone_set('UTC');
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) .'/../sites/default/settings.php';


$app = new ContentFix\App($databases['default']['default']);

//header('Content-Type: text/plain');
$app->run();