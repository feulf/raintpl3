<?php

/**
 *  RainTPL
 *  --------
 *  Realized by Federico Ulfo & maintained by the Rain Team
 *  Distributed under GNU/LGPL 3 License
 *
 *  @version 3.0 Alpha
 */

class RainTpl{

	// variables
	public				$var				= array();

	// configuration
	protected static	$config_check_sum	= null,
						$debug				= false,
						$tpl_dir			= "templates/",
						$cache_dir			= "cache/",
						$base_url			= null,
						$tpl_ext			= "html",
						$php_enabled		= false,
						$template_syntax	= "Rain",
						$path_replace		= true,
						$path_replace_list	= array( 'a', 'img', 'link', 'script', 'input' ),
						$registered_tags	= array(),
						$auto_escape		= true,
						$tags = array(
										'loop'			=> array( '({loop.*?})'		, '/{loop="\${0,1}(?<variable>[^"]*)"(?: as (?<key>\$.*?)(?: => (?<value>\$.*?)){0,1}){0,1}}/' ),
										'loop_close'	=> array( '({\/loop})'		, '/{\/loop}/' ),
										'if'			=> array( '({if.*?})'		, '/{if="([^"]*)"}/' ),
										'elseif'		=> array( '({elseif.*?})'	, '/{elseif="([^"]*)"}/' ),
										'else'			=> array( '({else})'		, '/{else}/' ),
										'if_close'		=> array( '({\/if})'		, '/{\/if}/' ),
										'noparse'		=> array( '({noparse})'		, '/{noparse}/' ),
										'noparse_close'	=> array( '({\/noparse})'	, '/{\/noparse}/' ),
										'ignore'		=> array( '({ignore})'		, '/{ignore}/' ),
										'ignore_close'	=> array( '({\/ignore})'	, '/{\/ignore}/' ),
										'include'		=> array( '({include.*?})'	, '/{include="([^"]*)"}/' ),
										'variable'		=> array( '({\$.*?})'		, '/{(\$.*?)}/' ),
										'constant'		=> array( '({#.*?})'		, '/{#(.*?)#{0,1}}/' ),
									 );

	/**
	 * Draw the template
	 */
	function draw( $template_file_path, $to_string = false ){
		extract( $this->var );
		ob_start();
		require_once self::_check_template( $template_file_path );
		if( $to_string ) return ob_get_clean(); else echo ob_get_clean();
	}



	/**
	 * Configure the template
	 */
	static function configure( $setting, $value = null ){
		if( is_array( $setting ) )
			foreach( $setting as $key => $value )
				self::configure( $key, $value );
		else if( property_exists( __CLASS__, $setting ) ){
			self::$$setting = $value;
			self::$config_check_sum .= $value; // take trace of all config
		}
	}


	/**
	 * Assign variable
	 * eg. 	$t->assign('name','mickey');
	 *
	 * @param mixed $variable_name Name of template variable or associative array name/value
	 * @param mixed $value value assigned to this variable. Not set if variable_name is an associative array
	 */
	function assign( $variable, $value = null ){
		if( is_array( $variable ) )
			$this->var += $variable;
		else
			$this->$var[ $variable ] = $value;
	}

	
	/**
	 * Clean the expired files from cache
	 * @param type $expire_time Set the expiration time
	 */
	static function clean( $expire_time = 2592000 ){
		$files = glob( self::$cache_dir . "*.rtpl.php" );
		$time = time();
		foreach( $files as $file )
			if( $time - filemtime($file) > $expired_time )
				unlink($file);
	}


	static function register_tag( $tag, $parse, $function ){
		self::$registered_tags[ $tag ] = array( "parse" => $parse, "function" => $function );
	}



	static protected function _check_template( $template ){
		// set filename
		$template_name				= basename( $template );
		$template_basedir			= strpos($template,"/") ? dirname($template) . '/' : null;
		$template_directory			= self::$tpl_dir . $template_basedir;
		$template_filepath			= $template_directory . $template_name . '.' . self::$tpl_ext;
		$parsed_template_filepath	= self::$cache_dir . $template_name . "." . md5( $template_directory . self::$config_check_sum ) . '.rtpl.php';

		// if the template doesn't exsist throw an error
		if( !file_exists( $template_filepath ) ){
			$e = new RainTpl_NotFoundException( 'Template '. $template_name .' not found!' );
			throw $e->setTemplateFile($template_filepath);
		}

		// Compile the template if the original has been updated
		if( self::$debug  ||  !file_exists( $parsed_template_filepath )  ||  ( filemtime($parsed_template_filepath) < filemtime( $template_filepath ) ) )
			self::compileFile( $template_name, $template_basedir, $template_filepath, $parsed_template_filepath );
		
		return $parsed_template_filepath;
	}



	/**
	 * Compile the file
	 */

	static function compileFile( $template_name, $template_basedir, $template_filepath, $parsed_template_filepath ){

		// open the template
		$fp = fopen( $template_filepath, "r" );

		// lock the file
		if( flock( $fp, LOCK_SH ) ){
			
			// read the file			
			$code = fread($fp, filesize( $template_filepath ) );
			
			// xml substitution
			$code = preg_replace( "/<\?xml(.*?)\?>/s", "##XML\\1XML##", $code );
			
			// disable php tag
			if( !self::$php_enabled )
				$code = str_replace( array("<?","?>"), array("&lt;?","?&gt;"), $code );

			//xml re-substitution
			$code = preg_replace_callback ( "/##XML(.*?)XML##/s", function( $match ){ 
																		return "<?php echo '<?xml ".stripslashes($match[1])." ?>'; ?>";
																  }, $code ); 

			$parsed_code = self::_compileTemplate( $code, $template_basedir, $template_filepath );
			$parsed_code = "<?php if(!class_exists('RainTpl')){exit;}?>" . $parsed_code;

			// fix the php-eating-newline-after-closing-tag-problem
			$parsed_code = str_replace( "?>\n", "?>\n\n", $parsed_code );

			// create directories
			if( !is_dir( self::$cache_dir ) )
				mkdir( self::$cache_dir, 0755, true );

			// check if the cache is writable
			if( !is_writable( self::$cache_dir ) )
				throw new RainTpl_Exception ('Cache directory ' . self::$cache_dir . 'doesn\'t have write permission. Set write permission or set RAINTPL_CHECK_TEMPLATE_UPDATE to false. More details on http://www.raintpl.com/Documentation/Documentation-for-PHP-developers/Configuration/');

			//write compiled file
			file_put_contents( $parsed_template_filepath, $parsed_code );

			// release the file lock
			return flock($fp, LOCK_UN);

		}

	}



	/**
	 * Compile template
	 * @access protected
	 */

	static function _compileTemplate( $code, $template_basedir, $template_filepath ){

		//path replace (src of img, background and href of link)
		if( self::$path_replace )
			$code = self::path_replace( $code, $template_basedir );

		// set tags
		foreach( self::$tags as $tag => $tag_array ){
			list( $split, $match ) = $tag_array;
			$tag_split[$tag] = $split;
			$tag_match[$tag] = $match;
		}
		
		$keys = array_keys( self::$registered_tags );
		$tag_split += array_merge( $tag_split, $keys );


		//split the code with the tags regexp
		$code_split = preg_split( "/" . implode( "|", $tag_split ) . "/", $code, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

		//variables initialization
		$parsed_code = $comment_is_open = $ignore_is_open = NULL;
        $open_if = $loop_level = 0;

	 	//read all parsed code
	 	while( $html = array_shift( $code_split ) ){

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

				//variables substitution
				$include_var = self::var_replace( $matches[ 1 ], $loop_level );

				//dynamic include
				$parsed_code .= '<?php $tpl = new RainTpl;' .
							 '$tpl_dir_temp = self::$tpl_dir;' .
							 '$tpl->assign( $this->var );' .
							 ( !$loop_level ? null : '$tpl->assign( "key", $key'.$loop_level.' ); $tpl->assign( "value", $value'.$loop_level.' );' ).
							 '$tpl->draw( dirname("'.$include_var.'") . ( substr("'.$include_var.'",-1,1) != "/" ? "/" : "" ) . basename("'.$include_var.'") );'.
							 '?>';

			}

	 		//loop
			elseif( preg_match( $tag_match['loop'], $html, $matches ) ){

	 			//increase the loop counter
	 			$loop_level++;

				// check if is a function
				if( preg_match( "/.*\(.*\)/", $matches['variable'] ) )
					$var = $matches['variable'];
				else
					//replace the variable in the loop
					$var = self::var_replace( '$' . $matches['variable'], $loop_level-1, $escape = false );


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

			//if
			elseif( preg_match( $tag_match['if'], $html, $matches ) ){

				//increase open if counter (for intendation)
				$open_if++;

				//tag
				$tag = $matches[ 0 ];

				//condition attribute
				$condition = $matches[ 1 ];

				//variable substitution into condition (no delimiter into the condition)
				$parsed_condition = self::var_replace( $condition, $loop_level );

				//if code
				$parsed_code .=   "<?php if( $parsed_condition ){ ?>";

			}

			//elseif
			elseif( preg_match( $tag_match['elseif'], $html, $matches ) ){

				//tag
				$tag = $matches[ 0 ];

				//condition attribute
				$condition = $matches[ 1 ];

				//variable substitution into condition (no delimiter into the condition)
				$parsed_condition = self::var_replace( $condition, $loop_level );

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

			//variables
			elseif( preg_match( $tag_match['variable'], $html, $matches ) ){
				//variables substitution (es. {$title})
				$parsed_code .= "<?php " . self::var_replace( $matches[1], $loop_level, $escape = true, $echo = true ) . "; ?>";
			}
			
			//constants
			elseif( preg_match( $tag_match['constant'], $html, $matches ) ){
				$parsed_code .= "<?php echo " . self::con_replace( $matches[1], $loop_level ) . "; ?>";
			}

			// template info
			else{

				$found = false;
				foreach( self::$registered_tags as $tags => $array ){
					if( preg_match( "/{$array['parse']}/", $html, $matches ) ){
						$found = true;
						$parsed_code .= "<?php echo call_user_func( self::\$registered_tags['$tags']['function'], array('".$matches[1]."') ); ?>";
					}
				}

				if( !$found )
					$parsed_code .= $html;
			}

		}

		if( $open_if > 0 ) {
			$e = new RainTpl_SyntaxException('Error! You need to close an {if} tag in ' . $template_filepath . ' template');
			throw $e->setTemplateFile($template_filepath);
		}

		if( $loop_level > 0 ) {
			$e = new RainTpl_SyntaxException('Error! You need to close the {loop} tag in ' . $template_filepath . ' template');
			throw $e->setTemplateFile($template_filepath);
		}

		return $parsed_code;

	}



	/**
	 * replace the path of image src, link href and a href.
	 * url => template_dir/url
	 * url# => url
	 * http://url => http://url
	 *
	 * @param string $html
	 * @return string html sostituito
	 */
	protected function path_replace( $html, $template_basedir ){

		if( self::$path_replace ){

			// get the template base directory
			$template_directory = self::$base_url . self::$tpl_dir . $template_basedir;
			
			// reduce the path
			$path = preg_replace('/\w+\/\.\.\//', '', $template_directory );

			$exp = $sub = array();

			if( in_array( "img", self::$path_replace_list ) ){
				$exp = array( '/<img(.*?)src=(?:")(http|https)\:\/\/([^"]+?)(?:")/i', '/<img(.*?)src=(?:")([^"]+?)#(?:")/i', '/<img(.*?)src="(.*?)"/', '/<img(.*?)src=(?:\@)([^"]+?)(?:\@)/i' );
				$sub = array( '<img$1src=@$2://$3@', '<img$1src=@$2@', '<img$1src="' . $path . '$2"', '<img$1src="$2"' );
			}

			if( in_array( "script", self::$path_replace_list ) ){
				$exp = array_merge( $exp , array( '/<script(.*?)src=(?:")(http|https)\:\/\/([^"]+?)(?:")/i', '/<script(.*?)src=(?:")([^"]+?)#(?:")/i', '/<script(.*?)src="(.*?)"/', '/<script(.*?)src=(?:\@)([^"]+?)(?:\@)/i' ) );
				$sub = array_merge( $sub , array( '<script$1src=@$2://$3@', '<script$1src=@$2@', '<script$1src="' . $path . '$2"', '<script$1src="$2"' ) );
			}

			if( in_array( "link", self::$path_replace_list ) ){
				$exp = array_merge( $exp , array( '/<link(.*?)href=(?:")(http|https)\:\/\/([^"]+?)(?:")/i', '/<link(.*?)href=(?:")([^"]+?)#(?:")/i', '/<link(.*?)href="(.*?)"/', '/<link(.*?)href=(?:\@)([^"]+?)(?:\@)/i' ) );
				$sub = array_merge( $sub , array( '<link$1href=@$2://$3@', '<link$1href=@$2@' , '<link$1href="' . $path . '$2"', '<link$1href="$2"' ) );
			}

			if( in_array( "a", self::$path_replace_list ) ){
				$exp = array_merge( $exp , array( '/<a(.*?)href=(?:")(http|https)\:\/\/([^"]+?)(?:")/i', '/<a(.*?)href="(.*?)"/', '/<a(.*?)href=(?:\@)([^"]+?)(?:\@)/i'  ) );
				$sub = array_merge( $sub , array( '<a$1href=@$2://$3@', '<a$1href="' . self::$base_url . '$2"', '<a$1href="$2"' ) );
			}

			if( in_array( "input", self::$path_replace_list ) ){
				$exp = array_merge( $exp , array( '/<input(.*?)src=(?:")(http|https)\:\/\/([^"]+?)(?:")/i', '/<input(.*?)src=(?:")([^"]+?)#(?:")/i', '/<input(.*?)src="(.*?)"/', '/<input(.*?)src=(?:\@)([^"]+?)(?:\@)/i' ) );
				$sub = array_merge( $sub , array( '<input$1src=@$2://$3@', '<input$1src=@$2@', '<input$1src="' . $path . '$2"', '<input$1src="$2"' ) );
			}

			return preg_replace( $exp, $sub, $html );

		}
		else
			return $html;

	}



	static protected function var_replace( $html, $loop_level = NULL, $escape = true, $echo = false ){
		
		// change variable name if loop level
		if( $loop_level )
			$html = str_replace( array('$value','$key','$counter'), array('$value'.$loop_level,'$key'.$loop_level,'$counter'.$loop_level), $html );
		
		// if it is a variable
		if( preg_match_all('/(\$.*)/', $html, $matches ) ){

			// substitute . and [] with [" "]
			for( $i=0;$i<count($matches[1]);$i++ ){
				
				$rep = preg_replace( '/\[(\${0,1}[a-zA-Z_0-9]*)\]/', '["$1"]', $matches[1][$i] );
				$rep = preg_replace( '/\.(\${0,1}[a-zA-Z_0-9]*)/', '["$1"]', $rep );

				$html = str_replace( $matches[0][$i], $rep, $html );

			}

			// update modifier
			$html = self::modifier_replace( $html );
			
			// if is not init
			if( !preg_match( '/\$.*=.*/', $rep ) ){
				
				// escape character
				if( self::$auto_escape && $escape )
					$html = "htmlspecialchars( $html )";
			
				// if is an assignment it doesn't add echo
				if( $echo )
						$html = "echo " . $html;

			}

		}
		
		return $html;
		
	}

	static protected function con_replace( $html ){
		$html = self::modifier_replace( $html );
		return $html;
		
	}

	static protected function modifier_replace( $html ){

		if( $pos = strrpos( $html, "|" ) ){
			
			$explode = explode( ":", substr( $html, $pos+1 ) );
			$function = $explode[0];
			$params = isset( $explode[1] ) ? "," . $explode[1] : null;

			$html = $function . "(" . self::modifier_replace( substr( $html, 0, $pos ) ) . "$params)";
		}
		
		return $html;
	
	}

}

/**
 * Basic Rain tpl exception.
 */
class RainTpl_Exception extends Exception{
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