<?php

// include
require "library/Rain/autoload.php";

// namespace
use Rain\Tpl;

// conf
$config = array(
    "base_url"	=> null,
    "tpl_dir"	=> "templates/image_resize/",
    "cache_dir"	=> "cache/",
    "debug"     => true // set to false to improve the speed
);
Tpl::configure( $config );


// Add PathReplace plugin (necessary to load the CSS with path replace)
Tpl::registerPlugin( new Tpl\Plugin\PathReplace() );

$plugin_options = array( 'quality' => 100, 'crop' => true );
Tpl::registerPlugin( new Tpl\Plugin\ImageResize( $plugin_options ) );

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
        array("name" => "Sheska", "color" => "red" ),
        array("name" => "Who", "color" => "yellow" ),
    ),
    "empty_array" => array(),
    "copyright" => "Copyright 2006 - 2012 Rain TPL<br>Project By Rain Team",

);

$test = function( $params ){
    $value = $params[0];
    return "Translate: <b>$value</b>";
};
// add a function
Tpl::registerTag( "({@.*?@})", "{@(.*?)@}", $test );



// draw
$tpl = new Tpl;
$tpl->assign( $var );
$tpl->draw( "page" );

// end