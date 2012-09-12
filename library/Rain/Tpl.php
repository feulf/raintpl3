<?php

namespace Rain;
require_once 'Tpl/PluginContainer.php';
require_once 'Tpl/Plugin.php';

/**
 *  RainTPL
 *  --------
 *  Realized by Federico Ulfo & maintained by the Rain Team
 *  Distributed under GNU/LGPL 3 License
 *
 *  @version 3.0 Alpha milestone: https://github.com/rainphp/raintpl3/issues/milestones?with_issues=no
 */

class Tpl{

	// variables
	public				$var				= array();

	/**
	 * Plugin container
	 *
	 * @var \Rain\Tpl\PluginContainer
	 */
	protected static    $plugins = null;

	// configuration
	protected static    $conf = array(
                                        'checksum'          => array(),
                                        'charset'           => 'UTF-8',
                                        'debug'             => FALSE,
                                        'tpl_dir'           => 'templates/',
                                        'cache_dir'         => 'cache/',
                                        'tpl_ext'           => 'html',
										'base_url'			=> '',
                                        'php_enabled'       => false,
                                        'template_syntax'	=> 'Rain',
                                        'registered_tags'	=> array(),
                                        'auto_escape'		=> FALSE,
                                        'tags'              => array(
                                                                        'loop'			=> array( '({loop.*?})'		, '/{loop="(?<variable>\${0,1}[^"]*)"(?: as (?<key>\$.*?)(?: => (?<value>\$.*?)){0,1}){0,1}}/' ),
                                                                        'loop_close'	=> array( '({\/loop})'		, '/{\/loop}/' ),
                                                                        'loop_break'	=> array( '({break})'		, '/{break}/' ),
                                                                        'loop_continue'	=> array( '({continue})'		, '/{continue}/' ),
                                                                        'if'			=> array( '({if.*?})'		, '/{if="([^"]*)"}/' ),
                                                                        'elseif'		=> array( '({elseif.*?})'	, '/{elseif="([^"]*)"}/' ),
                                                                        'else'			=> array( '({else})'		, '/{else}/' ),
                                                                        'if_close'		=> array( '({\/if})'		, '/{\/if}/' ),
                                                                        'noparse'		=> array( '({noparse})'		, '/{noparse}/' ),
                                                                        'noparse_close'	=> array( '({\/noparse})'	, '/{\/noparse}/' ),
                                                                        'ignore'		=> array( '({ignore}|{\*)'	, '/{ignore}|{\*/' ),
                                                                        'ignore_close'	=> array( '({\/ignore}|\*})', '/{\/ignore}|\*}/' ),
                                                                        'include'		=> array( '({include.*?})'	, '/{include="([^"]*)"}/' ),
                                                                        'function'		=> array( '({function.*?})'	, '/{function="([a-zA-Z_][a-zA-Z_0-9\:]*)(\(.*\)){0,1}"}/' ),
                                                                        'variable'		=> array( '({\$.*?})'		, '/{(\$.*?)}/' ),
                                                                        'constant'		=> array( '({#.*?})'		, '/{#(.*?)#{0,1}}/' ),
                                                                    ),
										'sandbox'			=> true,
										'black_list'		=> array('exec','shell_exec','pcntl_exec','passthru','proc_open', 'system','posix_kill','posix_setsid','pcntl_fork','posix_uname','php_uname',
																	 'phpinfo','popen','file_get_contents','file_put_contents','rmdir','mkdir','unlink','highlight_contents','symlink','apache_child_terminate',
																	 'apache_setenv','define_syslog_variables','escapeshellarg','escapeshellcmd','eval','fp','fput','ftp_connect','ftp_exec','ftp_get',
																	 'ftp_login','ftp_nb_fput','ftp_put','ftp_raw','ftp_rawlist','highlight_file','ini_alter','ini_get_all','ini_restore','inject_code',
																	 'mysql_pconnect','openlog','passthru','php_uname','phpAds_remoteInfo','phpAds_XmlRpc','phpAds_xmlrpcDecode','phpAds_xmlrpcEncode',
																	 'posix_getpwuid','posix_kill','posix_mkfifo','posix_setpgid','posix_setsid','posix_setuid','posix_uname','proc_close','proc_get_status',
																	 'proc_nice','proc_open','proc_terminate','syslog','xmlrpc_entity_decode'
															   ),
                        );

	protected			$template_info = array();



	/**
	 * Draw the template
	 */
	public function draw( $_template_file_path, $_to_string = FALSE ){
		extract( $this->var );
		ob_start();
		require $this->_check_template( $_template_file_path );
		if( $_to_string ) return ob_get_clean(); else echo ob_get_clean();
	}




    	/**
	 * Draw the template
	 */
	public function draw_string( $_string, $_to_string = false ){
		extract( $this->var );
		ob_start();
		require $this->_check_string( $_string );
		if( $_to_string ) return ob_get_clean(); else echo ob_get_clean();
	}




	/**
	 * Configure the template
	 */
	public static function configure( $setting, $value = null ){
		if( is_array( $setting ) )
			foreach( $setting as $key => $value )
				static::configure( $key, $value );
		else if( isset( static::$conf[$setting] ) ){
			static::$conf[$setting] = $value;
            
			static::$conf['checksum'][$setting] = $value; // take trace of all config
		}
        
	}


	/**
	 * Assign variable
	 * eg. 	$t->assign('name','mickey');
	 *
	 * @param mixed $variable_name Name of template variable or associative array name/value
	 * @param mixed $value value assigned to this variable. Not set if variable_name is an associative array
	 */
	public function assign( $variable, $value = null ){
		if( is_array( $variable ) )
			$this->var += $variable;
		else
			$this->var[ $variable ] = $value;
	}

	
	/**
	 * Clean the expired files from cache
	 * @param type $expire_time Set the expiration time
	 */
	public static function clean( $expire_time = 2592000 ){
		$files = glob( static::$conf['cache_dir'] . "*.rtpl.php" );
		$time = time();
		foreach( $files as $file )
			if( $time - filemtime($file) > $expired_time )
				unlink($file);
	}


	public static function register_tag( $tag, $parse, $function ){
		static::$conf['registered_tags'][ $tag ] = array( "parse" => $parse, "function" => $function );
	}

	/**
	 * Registers a plugin globally.
	 *
	 * @param \Rain\Tpl\IPlugin $plugin
	 * @param string $name name can be used to distinguish plugins of same class.
	 */
	public static function register_plugin(\Rain\Tpl\IPlugin $plugin, $name = ''){
		if ('' === $name) {
			$name = \get_class($plugin);
		}
		static::get_plugins()->add_plugin($name, $plugin);
    }

	/**
	 * Removes registered plugin from stack.
	 *
	 * @param string $name
	 */
	public static function remove_plugin($name){
		static::get_plugins()->remove_plugin($name);
    }

	/**
	 * Returns plugin container.
	 *
	 * @return \Rain\Tpl\PluginContainer
	 */
	protected static function get_plugins() {
		if (is_null(static::$plugins)) {
			static::$plugins = new \Rain\Tpl\PluginContainer();
		}
		return static::$plugins;
	}

	protected function _check_template( $template ){
		// set filename
		$template_name				= basename( $template );
		$template_basedir			= strpos($template,"/") ? dirname($template) . '/' : null;
		$template_directory			= static::$conf['tpl_dir'] . $template_basedir;
		$template_filepath			= $template_directory . $template_name . '.' . static::$conf['tpl_ext'];
		$parsed_template_filepath	= static::$conf['cache_dir'] . $template_name . "." . md5( $template_directory . serialize( static::$conf['checksum'] ) ) . '.rtpl.php';

		// if the template doesn't exsist throw an error
		if( !file_exists( $template_filepath ) ){
			$e = new RainTpl_NotFoundException( 'Template '. $template_name .' not found!' );
			throw $e->setTemplateFile($template_filepath);
		}

		// Compile the template if the original has been updated
		if( static::$conf['debug']  ||  !file_exists( $parsed_template_filepath )  ||  ( filemtime($parsed_template_filepath) < filemtime( $template_filepath ) ) )
			$this->_compile_file( $template_name, $template_basedir, $template_directory, $template_filepath, $parsed_template_filepath );

        return $parsed_template_filepath;
	}




	protected function _check_string( $string ){

        // set filename
        $template_name              = md5( $string . implode( static::$conf['checksum'] ) );
		$parsed_template_filepath	= static::$conf['cache_dir'] . $template_name . '.s.rtpl.php';
        $template_filepath          = '';
        $template_basedir			= '';


		// Compile the template if the original has been updated
		if( static::$conf['debug']  ||  !file_exists( $parsed_template_filepath ) )
			$this->_compile_string( $template_name, $template_basedir, $template_filepath, $parsed_template_filepath, $string );

        return $parsed_template_filepath;
	}



	/**
	 * Compile the file
	 */

	protected function _compile_file( $template_name, $template_basedir, $template_directory, $template_filepath, $parsed_template_filepath ){

		// open the template
		$fp = fopen( $template_filepath, "r" );

		// lock the file
		if( flock( $fp, LOCK_SH ) ){

			// save the filepath in the info
			$this->template_info['template_filepath'] = $template_filepath;
			
			// read the file			
			$this->template_info['code'] = $code = fread($fp, filesize( $template_filepath ) );

			// xml substitution
			$code = preg_replace( "/<\?xml(.*?)\?>/s", "##XML\\1XML##", $code );

			// disable php tag
			if( !static::$conf['php_enabled'] )
				$code = str_replace( array("<?","?>"), array("&lt;?","?&gt;"), $code );

			// xml re-substitution
			$code = preg_replace_callback ( "/##XML(.*?)XML##/s", function( $match ){
                                                                        return "<?php echo '<?xml ".stripslashes($match[1])." ?>'; ?>";
																  }, $code );

			$parsed_code = $this->_compile_template( $code, $is_string = false, $template_basedir, $template_directory, $template_filepath );
			$parsed_code = "<?php if(!class_exists('Rain\Tpl')){exit;}?>" . $parsed_code;

			// fix the php-eating-newline-after-closing-tag-problem
			$parsed_code = str_replace( "?>\n", "?>\n\n", $parsed_code );

			// create directories
			if( !is_dir( static::$conf['cache_dir'] ) )
				mkdir( static::$conf['cache_dir'], 0755, TRUE );

			// check if the cache is writable
			if( !is_writable( static::$conf['cache_dir'] ) )
				throw new RainTpl_Exception ('Cache directory ' . static::$conf['cache_dir'] . 'doesn\'t have write permission. Set write permission or set RAINTPL_CHECK_TEMPLATE_UPDATE to FALSE. More details on http://www.raintpl.com/Documentation/Documentation-for-PHP-developers/Configuration/');

			// write compiled file
			file_put_contents( $parsed_template_filepath, $parsed_code );

			// release the file lock
			flock($fp, LOCK_UN);

		}

		// close the file
		fclose( $fp );

	}



	/**
	 * Compile the file
	 */

	protected function _compile_string(  $template_name, $template_basedir, $template_filepath, $parsed_template_filepath, $code ){

		// open the template
		$fp = fopen( $parsed_template_filepath, "w" );

		// lock the file
		if( flock( $fp, LOCK_SH ) ){

			// xml substitution
			$code = preg_replace( "/<\?xml(.*?)\?>/s", "##XML\\1XML##", $code );

			// disable php tag
			if( !static::$conf['php_enabled'] )
				$code = str_replace( array("<?","?>"), array("&lt;?","?&gt;"), $code );

			// xml re-substitution
			$code = preg_replace_callback ( "/##XML(.*?)XML##/s", function( $match ){
                                                                        return "<?php echo '<?xml ".stripslashes($match[1])." ?>'; ?>";
																  }, $code );

			$parsed_code = $this->_compile_template( $code, $is_string = true, $template_basedir, $template_directory = null, $template_filepath );

			$parsed_code = "<?php if(!class_exists('Rain\Tpl')){exit;}?>" . $parsed_code;

			// fix the php-eating-newline-after-closing-tag-problem
			$parsed_code = str_replace( "?>\n", "?>\n\n", $parsed_code );

			// create directories
			if( !is_dir( static::$conf['cache_dir'] ) )
				mkdir( static::$conf['cache_dir'], 0755, true );

			// check if the cache is writable
			if( !is_writable( static::$conf['cache_dir'] ) )
				throw new RainTpl_Exception ('Cache directory ' . static::$conf['cache_dir'] . 'doesn\'t have write permission. Set write permission or set RAINTPL_CHECK_TEMPLATE_UPDATE to false. More details on http://www.raintpl.com/Documentation/Documentation-for-PHP-developers/Configuration/');

			// write compiled file
			fwrite( $fp, $parsed_code );

			// release the file lock
			flock($fp, LOCK_UN);

		}

		// close the file
		fclose( $fp );

	}



	/**
	 * Compile template
	 * @access protected
	 */

	protected function _compile_template( $code, $is_string, $template_basedir, $template_directory, $template_filepath ){

		// Execute plugins, before_parse
		$context = $this->get_plugins()->create_context(array(
			'code' => $code,
			'template_basedir' => $template_basedir,
			'template_filepath' => $template_filepath,
			'conf' => static::$conf,
		));
		$this->get_plugins()->run('before_parse', $context);
		$code = $context->code;

		// set tags
		foreach( static::$conf['tags'] as $tag => $tag_array ){
			list( $split, $match ) = $tag_array;
			$tag_split[$tag] = $split;
			$tag_match[$tag] = $match;
		}

		$keys = array_keys( static::$conf['registered_tags'] );
		$tag_split += array_merge( $tag_split, $keys );


		//split the code with the tags regexp
		$code_split = preg_split( "/" . implode( "|", $tag_split ) . "/", $code, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

		//variables initialization
		$parsed_code = $comment_is_open = $ignore_is_open = NULL;
                $open_if = $loop_level = 0;

                // if the template is not empty
                if( $code_split )

                    //read all parsed code
                    foreach ( $code_split as $html ){

                            //close ignore tag
                            if( !$comment_is_open && preg_match( $tag_match['ignore_close'], $html ) )
                                    $ignore_is_open = FALSE;

                            //code between tag ignore id deleted
                            elseif( $ignore_is_open ){
                                    //ignore the code
                            }

                            //close no parse tag
                            elseif( preg_match( $tag_match['noparse_close'], $html ) )
                                    $comment_is_open = FALSE;

                            //code between tag noparse is not compiled
                            elseif( $comment_is_open )
                                    $parsed_code .= $html;

                            //ignore
                            elseif( preg_match( $tag_match['ignore'], $html ) )
                                    $ignore_is_open = TRUE;

                            //noparse
                            elseif( preg_match( $tag_match['noparse'], $html ) )
                                    $comment_is_open = TRUE;

                            //include tag
                            elseif( preg_match( $tag_match['include'], $html, $matches ) ){

                                    //get the folder of the actual template
                                    $actual_folder = substr( $template_directory, strlen(static::$conf['tpl_dir']) );

                                    //get the included template
                                    $include_template = $actual_folder . $this->_var_replace( $matches[ 1 ], $loop_level );

                                    // reduce the path
                                    $include_template = preg_replace('/\w+\/\.\.\//', '', $include_template );

                                    //dynamic include
                                    $parsed_code .= '<?php $tpl = new '.get_called_class().';' .
                                                            '$tpl->assign( $this->var );' .
                                                            ( !$loop_level ? null : '$tpl->assign( "key", $key'.$loop_level.' ); $tpl->assign( "value", $value'.$loop_level.' );' ).
                                                            '$tpl->draw( "'.$include_template.'" );'.
                                                            '?>';

                            }

                            //loop
                            elseif( preg_match( $tag_match['loop'], $html, $matches ) ){

                                    // increase the loop counter
                                    $loop_level++;

                                    //replace the variable in the loop
                                    $var = $this->_var_replace($matches['variable'], $loop_level-1, $escape = FALSE );

                                    // check black list
                                    $this->_black_list( $var );

                                    //loop variables
                                    $counter = "\$counter$loop_level";       // count iteration

                                    if( isset($matches['key']) && isset($matches['value']) ){
                                            $key	 = $matches['key'];
                                            $value	 = $matches['value'];
                                    }
                                    elseif( isset($matches['key']) ){
                                            $key	 = "\$key$loop_level";               // key
                                            $value	 = $matches['key'];
                                    }
                                    else{
                                            $key	 = "\$key$loop_level";               // key
                                            $value	 = "\$value$loop_level";           // value
                                    }

                                    //loop code
                                    $parsed_code .=  "<?php $counter=-1; if( is_array($var) && sizeof($var) ) foreach( $var as $key => $value ){ $counter++; ?>";

                            }

                            //close loop tag
                            elseif( preg_match( $tag_match['loop_close'], $html ) ) {

                                    //iterator
                                    $counter = "\$counter$loop_level";

                                    //decrease the loop counter
                                    $loop_level--;

                                    //close loop code
                                    $parsed_code .=  "<?php } ?>";

                            }

			    //break loop tag
	 		    elseif( preg_match( $tag_match['loop_break'], $html ) ) {
	 			//close loop code
	 			$parsed_code .=  "<?php break; ?>";
	 		    }
	 		
	 		    //continue loop tag
	 		    elseif( preg_match( $tag_match['loop_continue'], $html ) ) {
	 			//close loop code
	 			$parsed_code .=  "<?php continue; ?>";
	 		    }

                            //if
                            elseif( preg_match( $tag_match['if'], $html, $matches ) ){

                                    //increase open if counter (for intendation)
                                    $open_if++;

                                    //tag
                                    $tag = $matches[ 0 ];

                                    //condition attribute
                                    $condition = $matches[ 1 ];

                                    // check black list
                                    $this->_black_list( $condition );

                                    //variable substitution into condition (no delimiter into the condition)
                                    $parsed_condition = $this->_var_replace( $condition, $loop_level, $escape = FALSE );

                                    //if code
                                    $parsed_code .=   "<?php if( $parsed_condition ){ ?>";

                            }

                            //elseif
                            elseif( preg_match( $tag_match['elseif'], $html, $matches ) ){

                                    //tag
                                    $tag = $matches[ 0 ];

                                    //condition attribute
                                    $condition = $matches[ 1 ];

                                    // check black list
                                    $this->_black_list( $condition );

                                    //variable substitution into condition (no delimiter into the condition)
                                    $parsed_condition = $this->_var_replace( $condition, $loop_level, $escape = FALSE );

                                    //elseif code
                                    $parsed_code .=   "<?php }elseif( $parsed_condition ){ ?>";
                            }

                            //else
                            elseif( preg_match( $tag_match['else'], $html ) ) {

                                    //else code
                                    $parsed_code .=   '<?php }else{ ?>';

                            }

                            //close if tag
                            elseif( preg_match( $tag_match['if_close'], $html ) ) {

                                    //decrease if counter
                                    $open_if--;

                                    // close if code
                                    $parsed_code .=   '<?php } ?>';

                            }

                            // function
                            elseif( preg_match( $tag_match['function'], $html, $matches ) ) {

                                    // get function
                                    $function = $matches[1];

                                    // var replace
                                    if( isset($matches[2]) )
                                            $parsed_function = $function . $this->_var_replace( $matches[2], $loop_level, $escape = FALSE, $echo = FALSE );
                                    else
                                            $parsed_function = $function . "()";

                                    // check black list
                                    $this->_black_list( $parsed_function );

                                    // function 
                                    $parsed_code .=   "<?php echo $parsed_function; ?>";

                            }

                            //variables
                            elseif( preg_match( $tag_match['variable'], $html, $matches ) ){
                                    //variables substitution (es. {$title})
                                    $parsed_code .= "<?php " . $this->_var_replace( $matches[1], $loop_level, $escape = TRUE, $echo = TRUE ) . "; ?>";
                            }

                            //constants
                            elseif( preg_match( $tag_match['constant'], $html, $matches ) ){
                                    $parsed_code .= "<?php echo " . $this->_con_replace( $matches[1], $loop_level ) . "; ?>";
                            }

                            // registered tags
                            else{

                                    $found = FALSE;
                                    foreach( static::$conf['registered_tags'] as $tags => $array ){
                                            if( preg_match_all( '/' . $array['parse'] . '/', $html, $matches ) ){
                                                $found = true;
                                                unset($matches[0]); // needed to make it work with arrays
                                                $varray = var_export($matches,1);
						$tmp = preg_split('/\'/', $varray);
						foreach($tmp as $key => $reg){
							if(preg_match('/^\$/', $reg)) $varray = str_replace("'$reg'",$reg,$varray);
						}
						$varray = str_replace('"','\"',$varray);
						$varray = str_replace("'",'"',$varray);
						$parsed_code .= "<?php echo call_user_func( static::\$conf['registered_tags']['$tags']['function'], ".$varray." ); ?>";
                                            }
                                    }

                                    if( !$found )
                                            $parsed_code .= $html;
                            }

                    }


        if( $is_string ){
            if( $open_if > 0 ) {

                $trace=debug_backtrace();
                $caller=array_shift($trace);

                $e = new RainTpl_SyntaxException( "Error! You need to close an {if} tag in the string, loaded by {$caller['file']} at line {$caller['line']}" );
                throw $e->setTemplateFile($template_filepath);
            }

            if( $loop_level > 0 ) {
                $e = new RainTpl_SyntaxException( "Error! You need to close the {loop} tag in the string, loaded by {$caller['file']} at line {$caller['line']}" );
                throw $e->setTemplateFile($template_filepath);
            }
        }
        else{
            if( $open_if > 0 ) {
                $e = new RainTpl_SyntaxException( "Error! You need to close an {if} tag in $template_filepath template");
                throw $e->setTemplateFile($template_filepath);
            }

            if( $loop_level > 0 ) {
                $e = new RainTpl_SyntaxException( "Error! You need to close the {loop} tag in $template_filepath template" );
                throw $e->setTemplateFile($template_filepath);
            }
        }

        // Execute plugins, after_parse
	$context->code = $parsed_code;
        $this->get_plugins()->run('after_parse', $context);

	return $context->code;

	}



	protected function _var_replace( $html, $loop_level = NULL, $escape = TRUE, $echo = FALSE ){
		
		// change variable name if loop level
                if (! empty($loop_level)) 
                    $html = preg_replace(array('/(\$key)\b/', '/(\$value)\b/', '/(\$counter)\b/'), array('${1}'.$loop_level, '${1}'.$loop_level, '${1}'.$loop_level), $html); 
                
		// if it is a variable
		if( preg_match_all('/(\$[a-z_A-Z][\.\[\]\"\'a-zA-Z_0-9]*)/', $html, $matches ) ){

			// substitute . and [] with [" "]
			for( $i=0;$i<count($matches[1]);$i++ ){
				
				$rep = preg_replace( '/\[(\${0,1}[a-zA-Z_0-9]*)\]/', '["$1"]', $matches[1][$i] );
				$rep = preg_replace( '/\.(\${0,1}[a-zA-Z_0-9]*)/', '["$1"]', $rep );
				$html = str_replace( $matches[0][$i], $rep, $html );

			}

			// update modifier
			$html = $this->_modifier_replace( $html );
			
			// if does not initialize a value, e.g. {$a = 1}
			if( !preg_match( '/\$.*=.*/', $html ) ){

				// escape character
				if( static::$conf['auto_escape'] && $escape )
					//$html = "htmlspecialchars( $html )";
                    $html = "htmlspecialchars( $html, ENT_COMPAT, '".static::$conf['charset']."', FALSE )";
			
				// if is an assignment it doesn't add echo
				if( $echo )
						$html = "echo " . $html;

			}

		}
		
		return $html;
		
	}

	protected function _con_replace( $html ){
		$html = $this->_modifier_replace( $html );
		return $html;
		
	}

	protected function _modifier_replace( $html ){

		if( $pos = strrpos( $html, "|" ) ){

			// check black list
			$this->_black_list( $html );

			$explode = explode( ":", substr( $html, $pos+1 ) );
			$function = $explode[0];
			$params = isset( $explode[1] ) ? "," . $explode[1] : null;

			$html = $function . "(" . $this->_modifier_replace( substr( $html, 0, $pos ) ) . "$params)";

		}
		
		return $html;
	
	}

	protected function _black_list( $html ){

		if( !self::$conf['sandbox'] || !self::$conf['black_list'] )
			return true;

		if( empty( self::$conf['black_list_preg'] ) )
			self::$conf['black_list_preg'] = '#[\W\s]*' . implode( '[\W\s]*|[\W\s]*', self::$conf['black_list'] ) . '[\W\s]*#';

		// check if the function is in the black list (or not in white list)
		if( preg_match( self::$conf['black_list_preg'], $html, $match ) ){

			// find the line of the error
			$line = 0;
			$rows = explode( "\n", $this->template_info['code'] );
			while( !strpos( $rows[$line], $html ) && $line+1 < count($rows) )
				$line++;

			// stop the execution of the script
			$e = new RainTpl_SyntaxException('Syntax '.$match[0].' not allowed in template: ' . $this->template_info['template_filepath'] . ' at line '.$line );
			throw $e->setTemplateFile( $this->template_info['template_filepath'] )
				->setTag( $match[0] )
				->setTemplateLine($line);

			return false;
		}


		

	}

}


/**
 * Basic Rain tpl exception.
 */
class RainTpl_Exception extends \Exception{
    
	/**
	 * Path of template file with error.
	 */
	protected $templateFile = '';

	/**
	 * Returns path of template file with error.
	 *
	 * @return string
	 */
	public function getTemplateFile()
	{
		return $this->templateFile;
	}

	/**
	 * Sets path of template file with error.
	 *
	 * @param string $templateFile
	 * @return RainTpl_Exception
	 */
	public function setTemplateFile($templateFile)
	{
		$this->templateFile = (string) $templateFile;
		return $this;
	}
}

/**
 * Exception thrown when template file does not exists.
 */
class RainTpl_NotFoundException extends RainTpl_Exception{
}

/**
 * Exception thrown when syntax error occurs.
 */
class RainTpl_SyntaxException extends RainTpl_Exception{
	/**
	 * Line in template file where error has occured.
	 *
	 * @var int | null
	 */
	protected $templateLine = null;

	/**
	 * Tag which caused an error.
	 *
	 * @var string | null
	 */
	protected $tag = null;

	/**
	 * Returns line in template file where error has occured
	 * or null if line is not defined.
	 *
	 * @return int | null
	 */
	public function getTemplateLine()
	{
		return $this->templateLine;
	}

	/**
	 * Sets  line in template file where error has occured.
	 *
	 * @param int $templateLine
	 * @return RainTpl_SyntaxException
	 */
	public function setTemplateLine($templateLine)
	{
		$this->templateLine = (int) $templateLine;
		return $this;
	}

	/**
	 * Returns tag which caused an error.
	 *
	 * @return string
	 */
	public function getTag()
	{
		return $this->tag;
	}

	/**
	 * Sets tag which caused an error.
	 *
	 * @param string $tag
	 * @return RainTpl_SyntaxException
	 */
	public function setTag($tag)
	{
		$this->tag = (string) $tag;
		return $this;
	}
}

// -- end
