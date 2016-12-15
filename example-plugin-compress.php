<?php

ini_set("display_errors", true);

// include
require "library/Rain/autoload.php";

// namespace
use Rain\Tpl;

// conf
$config = array(
    "base_url"	=> null,
    "tpl_dir"	=> "templates/compress/",
    "cache_dir"	=> "cache/",
    "debug"         => true // set to false to improve the speed
);

Tpl::configure( $config );
Tpl::registerPlugin( new Tpl\Plugin\PathReplace );

$compress = new Tpl\Plugin\Compress;
$compress->configure('css', array('status'=>true));
$compress->configure('html', array('status'=>true));
$compress->configure('javascript', array('status'=>true, 'position' => 'bottom'));
Tpl::registerPlugin($compress);


global $global_variable;
$global_variable = "I'm Global";

// set variables
$var = array(
    "variable"	=> "Hello",
    "version"	=> "3.0 Alpha",
    "menu"		=> array(
        array("name" => "Home", "link" => "index.php", "selected" => true ),
        array("name" => "FAQ", "link" => "index.php/FAQ/", "selected" => null ),
        array("name" => "Documentation", "link" => "index.php/doc/", "selected" => null )
    ),
    "week"		=> array( "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday" ),
    "title"		=> "Rain TPL 3 - Easy and Fast template engine",
    "user"		=> array(
        array("name" => "Fede", "color" => "blue" ),
        array("name" => "Laura", "color" => "red" ),
        array("name" => "Who", "color" => "yellow" ),
    ),
    "empty_array" => array(),
    "copyright" => "Copyright 2006 - 2012 Rain TPL<br>Project By Rain Team",

);

// draw
$tpl = new Tpl;
$tpl->assign( $var );
$tpl->draw( "test_compress" );
