<?php

// include
require "library/Rain/autoload.php";

// namespace
use Rain\Tpl;

// config
$config = array(
    "tpl_dir"   => array(
        "templates/multiple-template-directories/newimplement/",
        "templates/multiple-template-directories/base/"
    ),
    "cache_dir" => "cache/",
    "debug"     => true, // set to false to improve the speed
);

Tpl::configure($config);


// Add PathReplace plugin (necessary to load the CSS with path replace)
Tpl::registerPlugin(new Tpl\Plugin\PathReplace());


// create the Tpl object
$tpl = new Tpl;
$tpl->draw("subfolder/index");

?>