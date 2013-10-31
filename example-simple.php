<?php

// include
require "library/Rain/autoload.php";

// namespace
use Rain\Tpl;

// config
$config = array(
    "tpl_dir"       => "templates/simple/",
    "cache_dir"     => "cache/",
    "debug"         => true, // set to false to improve the speed
);

Tpl::configure( $config );


// Add PathReplace plugin (necessary to load the CSS with path replace)
Tpl::registerPlugin( new Tpl\Plugin\PathReplace() );


// create the Tpl object
$tpl = new Tpl;

// assign a variable
$tpl->assign( "name", "Obi Wan Kenoby" );

// assign an array
$tpl->assign( "week", array( "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday" ) );

// draw the template
$tpl->draw( "simple_template" );


?>