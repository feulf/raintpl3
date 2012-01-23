<?php

	// include
	include "library/RainTpl.php";
	
	// configure
	RainTpl::configure( "base_url", null );
	RainTpl::configure( "tpl_dir", "templates/test/" );
	RainTpl::configure( "cache_dir", "cache/" );
	RainTpl::configure( "debug", true );
	RainTpl::configure( "auto_escape", true );


	// set variables
	$var = array(
					"variable"	=> "Hello World!",
					"version"	=> "3.0 Alpha",
					"menu"		=> array(
											array("name" => "Home", "link" => "index.php", "selected" => true ),
											array("name" => "FAQ", "link" => "index.php/FAQ/", "selected" => null ),
											array("name" => "Documentation", "link" => "index.php/doc/", "selected" => null )
										),
					"week"		=> array( "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday" ),
					"user"		=> (object) array("name"=>"Rain", "citizen" => "Earth", "race" => "Human" ),
					"numbers"	=> array( 3, 2, 1 ),
					"bad_text"	=> 'Hey this is a malicious XSS <script>alert(1);</script>',
					"table"		=> array( array( "Apple", "1996" ), array( "PC", "1997" ) ),
					"title"		=> "Rain TPL 3 - Easy and Fast template engine", 
					"copyright" => "Copyright 2006 - 2012 Rain TPL<br>Project By Rain Team",

				);

	// add a function
	RainTpl::register_tag(	"({@.*?@})", // preg split
						"{@(.*?)@}", // preg match
						function( $params ){ // function called by the tag
												$value = $params[0];
												return "Translate: <b>$value</b>";
										   } 
					 );

	// draw
	$tpl = new RainTpl;
	$tpl->assign( $var );
	echo $tpl->draw( "test" );

        
?>