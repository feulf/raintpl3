<?php

/**
 *  RainTPL
 *  --------
 *  Realized by Federico Ulfo & maintained by the Rain Team
 *  Distributed under GNU/LGPL 3 License
 *
 *  @version 3.0 Alpha milestone: https://github.com/rainphp/raintpl3/issues/milestones?with_issues=no
 */



/**
    * replace the path of image src, link href and a href.
    * url => template_dir/url
    * url# => url
    * http://url => http://url
    *
    * @param string $html
    * @return string html sostituito
    */
function image_resize_after_parse( $parameters, $conf ){

    // set variables
    $html = $parameters['code'];
    $template_basedir = $parameters['template_basedir'];
    $quality = $conf['plugins']['image_resize']['quality'];
    $auto_crop = $conf['plugins']['image_resize']['crop'];
    $img_cache_dir = $conf['cache_dir'];


    // get the template base directory
    $template_directory = $conf['base_url'] . $conf['tpl_dir'] . $template_basedir;

    // reduce the path
    $path = preg_replace('/\w+\/\.\.\//', '', $template_directory );

    $exp = $sub = array();

    $image_resized = false;

    // match the images
    if( preg_match_all( '/<img((?:\s*(src="(?<src>.*?)"))|(\s*(width="(?<width>.*?)"))|(\s*height="(?<height>.*?)")|(\s*resize="(?<resize>.*?)")|(\s*crop="(?<crop>.*?)"))*.*?>/', $html, $matches ) ){

        for( $i=0,$n=count($matches[0]); $i<$n; $i++ ){
            $tag = $matches[0][$i];
            $src = $matches['src'][$i];
            $w = $matches['width'][$i];
            $h = $matches['height'][$i];
            $resize = $matches['resize'][$i];
            if( $auto_crop )
                $crop = $matches['crop'][$i] == 'false' ? false : true;
            else
                $crop = $matches['crop'][$i] == 'true' ? true : false;

            if( $w > 0 && $h > 0 && $resize != 'false' ){
                $new_tag = preg_replace( '/(.*?)src="(.*?)"(.*?)/', '$1src="<?php echo rain_image_resize(\''.$src.'\', \''.$img_cache_dir.'\', \''.$w.'\', \''.$h.'\', \''.$quality.'\', \''.$crop.'\' ); ?>"$3', $tag );
                $html = str_replace( $tag, $new_tag, $html );
                $image_resized = true;
            }

        }

        if( $image_resized )
            $html = '<?php require_once(__FILE__); ?>' . $html;

    }

    return $html;
}


function rain_image_resize( $src, $dest, $w, $h, $quality, $crop ){

    $ext = substr(strrchr($src, '.'),1);
    $dest = $dest . 'img.'. md5( $src . $crop . $quality ) . $w . 'x' . $h . '.' . $ext;

    if( !file_exists( $dest ) )
        img_resize( $src, $dest, $w, $h, $quality, $crop );
    return $dest;

}

function img_resize($src, $dst, $width, $height, $quality, $crop=0){

  if(!list($w, $h) = getimagesize($src)) return "Unsupported picture type!";

  $type = strtolower(substr(strrchr($src,"."),1));
  if($type == 'jpeg') $type = 'jpg';
  switch($type){
    case 'bmp': $img = imagecreatefromwbmp($src); break;
    case 'gif': $img = imagecreatefromgif($src); break;
    case 'jpg': $img = imagecreatefromjpeg($src); break;
    case 'png': $img = imagecreatefrompng($src); break;
    default : return "Unsupported picture type!";
  }

  // resize
  if($crop){
    if($w < $width or $h < $height) return "Picture is too small!";
    $ratio = max($width/$w, $height/$h);
    $h = $height / $ratio;
    $x = ($w - $width / $ratio) / 2;
    $w = $width / $ratio;
  }
  else{
    if($w < $width and $h < $height) return "Picture is too small!";
    $ratio = min($width/$w, $height/$h);
    $width = $w * $ratio;
    $height = $h * $ratio;
    $x = 0;
  }

  $new = imagecreatetruecolor($width, $height);

  // preserve transparency
  if($type == "gif" or $type == "png"){
    imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
    imagealphablending($new, false);
    imagesavealpha($new, true);
  }

  imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);

  switch($type){
    case 'bmp': imagewbmp($new, $dst, $quality); break;
    case 'gif': imagegif($new, $dst, $quality); break;
    case 'jpg': imagejpeg($new, $dst, $quality); break;
    case 'png': imagepng($new, $dst, $quality); break;
  }
  return true;
}