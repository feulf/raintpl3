<?php

	// include
	include "library/RainTpl.php";
	// extend RainTPL to prevent change of charset
	class MyRainTPL extends RainTPL{
		// This example is just a proof of concept, as there are better ones.
		protected static $charset = "UTF-8";
		public static function configure( $setting, $value = null ){
			if(isset($setting) && $setting === 'charset') return;
			if( is_array( $setting ) )
				foreach( $setting as $key => $value )
					static::configure( $key, $value );
			else if( property_exists( get_called_class(), $setting ) ){
				static::$$setting = $value;
				static::$config_check_sum[$setting] = $value; // take trace of all config
			}
		}
	}

	// conf
	$config = array( 
					"base_url"	=> null, 
					"tpl_dir"	=> "templates/raintpl3/",
					"cache_dir"	=> "cache/",
					"debug"		=> true,
					"charset"	=> "Something different";
				   );

	//use Rain;
	MyRainTPL::configure( $config );

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
	MyRainTPL::register_tag( "({@.*?@})", "{@(.*?)@}", $test );



	// draw
	$tpl = new MyRainTPL;
	$tpl->assign( $var );
	echo $tpl->draw( "page" );

        
?>