<?php

	memory_usage_start();
	timer_start();

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


	$var['title'] = 'Federico';

	// draw
	$tpl = new Tpl;
	$tpl->assign( $var );
	$tpl->draw_string( 'Hello {$title} how are you?' );

	echo "<br>---------<br>";
	echo memory_usage();
	echo "<br>";
	echo timer();


// -- end





//-------------------------------------------------------------
//
//	BENCHMARK/DEBUG FUNCTIONS
//
//-------------------------------------------------------------


	/**
	 * Useful for debug, print the variable $mixed and die
	 */
	function dump( $mixed, $exit = 1 ){
		echo "<pre>dump \n---------------------- \n\n" . print_r( $mixed, true ) . "\n----------------------<pre>";
		if( $exit ) exit;
	}



	/**
	 * Save the memory used at this point
	 */
	function memory_usage_start( $memName = "execution_time" ){
		return $GLOBALS['memoryCounter'][$memName] = memory_get_usage();
	}



	/**
	 * Get the memory used
	 */
	function memory_usage( $memName = "execution_time", $byte_format = true ){
		$totMem = memory_get_usage() - $GLOBALS['memoryCounter'][ $memName ];
		return $byte_format ? byte_format($totMem) : $totMem;
	}


//-------------------------------------------------------------
//
//					 TIME FUNCTIONS
//
//-------------------------------------------------------------

	/**
	 * Start the timer
	 */
	function timer_start( $timeName = "execution_time" ){
		$stimer = explode( ' ', microtime( ) );
		$GLOBALS['timeCounter'][$timeName] = $stimer[ 1 ] + $stimer[ 0 ];
	}

	/**
	 * Get the time passed
	 */
	function timer( $timeName = "execution_time", $precision = 10 ){
	   $etimer = explode( ' ', microtime( ) );
	   $timeElapsed = $etimer[ 1 ] + $etimer[ 0 ] - $GLOBALS['timeCounter'][ $timeName ];
	   return substr( $timeElapsed, 0, $precision );
	}


	/**
	 * Convert byte to more readable format, like "1 KB" instead of "1024".
	 * cut_zero, remove the 0 after comma ex:  10,00 => 10	  14,30 => 14,3
	 */
	function byte_format( $size ){
		if( $size > 0 ){
			$unim = array("B","KB","MB","GB","TB","PB");
			for( $i=0; $size >= 1024; $i++ )
				$size = $size / 1024;
			return number_format($size,$i?2:0, ',', '.' )." ".$unim[$i];
		}
	}