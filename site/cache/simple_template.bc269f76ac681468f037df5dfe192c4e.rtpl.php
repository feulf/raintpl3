<?php if(!class_exists('Rain\Tpl')){exit;}?><!DOCTYPE html>
<html lang="en">
<head>
	<title>Simple template</title>
	<link rel="stylesheet" href="<?php echo static::$conf['base_url']; ?>templates/simple/style.css" type="text/css" />
</head>
<body>

    <?php $var=$var["0"] + $var["1"]; ?>
    
    <?php $name=123+123; ?>
    
    <?php echo htmlspecialchars( $var->name[0], ENT_COMPAT, 'UTF-8', FALSE ); ?>
    
    <?php echo htmlspecialchars( $var["0"]->name, ENT_COMPAT, 'UTF-8', FALSE ); ?>

    <?php echo htmlspecialchars( $var["0"]->name, ENT_COMPAT, 'UTF-8', FALSE ); ?>
    
    <?php echo htmlspecialchars( $var->name.subname, ENT_COMPAT, 'UTF-8', FALSE ); ?>

    
	<h2>Variable</h2>
	Hi <b><?php echo htmlspecialchars( $name, ENT_COMPAT, 'UTF-8', FALSE ); ?></b>, this is a beautiful day for a Jedi!

	<br>
	<br>

	<h2>Week</h2>
	<ul>
		<?php $counter1=-1;  if( isset($week) && ( is_array($week) || $week instanceof Traversable ) && sizeof($week) ) foreach( $week as $key1 => $value1 ){ $counter1++; ?>
		<li>
			<?php echo htmlspecialchars( $value1, ENT_COMPAT, 'UTF-8', FALSE ); ?>
		</li>
		<?php } ?>
	</ul>

</body>
</html>