<?php

if(!defined("BASE_DIR"))
    define("BASE_DIR", dirname( dirname(__DIR__) ) );

// register the autoloader
spl_autoload_register( "RainTplAutoloader" );


// autoloader
function RainTplAutoloader( $class ){

    // it only autoload class into the Rain scope
    if (strpos($class,'Rain\\Tpl') !== false){

        // transform the namespace in path
        $path = str_replace("\\", DIRECTORY_SEPARATOR, $class );

        // filepath
        $abs_path = BASE_DIR . "/library/" . $path . ".php";

        if (!file_exists($abs_path)) {
            echo "<br>";
            echo $path;
            echo "<br>";
            echo $abs_path;
            echo "<br><br>";
        }

        // require the file
        require_once $abs_path;
    }

}