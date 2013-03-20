<?php
// you need to copy lessphp to 'lessphp/lessphp.inc.php'
// how to use
//<link rel="stylesheet" type="text/css" href="{lesscss="css/fonts/ptsans/stylesheet.css"}" media="screen">
// use {lesscss="file"} tag
// author amunhoz

namespace Rain\Tpl\Plugin;
require_once __DIR__ . '/../Plugin.php';

class LessCSS extends \Rain\Tpl\Plugin
{
  protected $hooks = array('beforeParse');

	/**
	 * replace the path of image src, link href and a href.
	 * url => template_dir/url
	 * url# => url
	 * http://url => http://url
	 *
	 * @param \ArrayAccess $context
	 */
	public function beforeParse(\ArrayAccess $context){

		// set variables
		$html = $context->code;
		$template_basedir = $context->template_basedir;
		$matches = array();

		if (preg_match_all('/{lesscss="([^"]*)"}/', $html, $matches, PREG_OFFSET_CAPTURE, 3)) {
			for( $i=0,$n=count($matches[0]); $i<$n; $i++ ){
				$tag = $matches[0][$i][0];
				$file = $matches[1][$i][0];
				$newfile = $this->compfile($file, $context);
				$html = str_replace( $tag, $newfile, $html );
			}
		}
		$context->code = $html;

	}

	private function compfile($file, $context) {
		// get lesscss file
		$lessfile =  $context->conf['tpl_dir'] . $context->template_basedir . $file;
		$abslessfile = $lessfile;
		//checking if it is an absolute path
		if (substr($abslessfile,0,1)=='/') {
			$abslessfile =  $_SERVER['DOCUMENT_ROOT'].$lessfile;
		}

		$path_parts = pathinfo($lessfile);

		// setting new file name and path
		$compfileurl =  $path_parts['filename'] . abs(crc32($lessfile)).'.css';
		$compfile = $context->conf['cache_dir'] . $compfileurl;


		if( $context->conf['debug'] || !file_exists($compfile) || (filemtime($compfile) < filemtime($file)) ){
			//getting data
			$data = file_get_contents($abslessfile, true);

			//compiling with less
			require_once('lessphp/lessc.inc.php');
			$less = new \lessc;
			$less->addImportDir($path_parts['dirname']); //in case of using less import command --> not really tested!
			$data = $less->compile($data);
			
			//change url path to images and import
			$urlpath = $path_parts['dirname'] .'/';
			$search = '#url\((?!\s*[\'"]?(?:https?:)?//)\s*([\'"])?#';
			$replace = "url($1{$urlpath}";
			$data =  preg_replace($search, $replace, $data);
			
			file_put_contents( $compfile, $data );
		}
		$urlfile =  str_replace( $_SERVER['DOCUMENT_ROOT'], '', $context->conf['cache_dir']) . $compfileurl;
		//dynamic include
		return  $urlfile;
	}
}
