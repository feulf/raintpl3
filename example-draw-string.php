<?php

    // namespace
    use Rain\Tpl;

	// include
	include "library/Rain/Tpl.php";
	
	// config
	$config = array(
					"base_url"      => null,
					"tpl_dir"       => "templates/test/",
					"cache_dir"     => "cache/",
					"debug"         => true,
                    "auto_escape"   => true
				   );

	Tpl::configure( $config );


	$var['name'] = 'Federico';

	// draw
	$tpl = new Tpl;
	$tpl->assign( $var );
	$tpl->draw_string( 'Hello {$title} how are you?' );

// -- end