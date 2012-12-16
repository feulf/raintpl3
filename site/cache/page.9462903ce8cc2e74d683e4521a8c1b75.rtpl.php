<?php if(!class_exists('Rain\Tpl')){exit;}?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset=utf-8 />
<title><?php echo htmlspecialchars( $title, ENT_COMPAT, 'UTF-8', FALSE ); ?></title>

<!-- this link will be substituted with the right path : href="THEMES/acid/style.css" -->
<link href="<?php echo static::$conf['base_url']; ?>templates/image_resize/style.css" type="text/css" rel="stylesheet" >
</head>
<body>

    <h1>Image resize plugin</h1>

    Original image <img src="<?php echo static::$conf['base_url']; ?>templates/image_resize/img/wysiwyg.jpg"/><br><br><br>
    
    Resized and cropped image <img src="<?php echo static::$conf['base_url']; ?>templates/image_resize/img/wysiwyg.jpg" width="100" height="70" /><br><br><br>

    Image resized but not cropped <img src="<?php echo static::$conf['base_url']; ?>templates/image_resize/img/wysiwyg.jpg" width="100" height="70" crop="false"/><br><br><br>
    
    This image is not resized by PHP, only by the browser: <img src="<?php echo static::$conf['base_url']; ?>templates/image_resize/img/wysiwyg.jpg" width="100" height="70" resize="false"/><br><br><br>

    <hr>

    Original image <img src="<?php echo static::$conf['base_url']; ?>templates/image_resize/img/logo.jpeg"/><br><br><br>

    Resized <img src="<?php echo static::$conf['base_url']; ?>templates/image_resize/img/logo.jpeg" width="60"/><br><br><br>

    Resized and Cropped <img src="<?php echo static::$conf['base_url']; ?>templates/image_resize/img/logo.jpeg" width="100" height="60"/><br><br><br>


</body>
</html>