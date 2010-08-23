<?php

function setIncludePath() {
    $currentDir = dirname(__FILE__);
    $includePath = get_include_path() . PATH_SEPARATOR .
        $currentDir . PATH_SEPARATOR .
        "lib";
    set_include_path( $includePath );
}

function __autoload($className) {
    $className = str_replace('_', '/', $className);
    $path = "{$className}.php";
    require_once $path;
}

date_default_timezone_set('America/New_York');
setIncludePath();
