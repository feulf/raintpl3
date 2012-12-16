<?php if(!class_exists('Rain\Tpl')){exit;}?><!DOCTYPE html>
<html lang="en">
<head>
	<title>Test</title>
	<link rel="stylesheet" href="<?php echo static::$conf['base_url']; ?>templates/test/style.css" type="text/css" />
</head>
<body>

    

	<h1>Test Rain Tpl <?php echo htmlspecialchars( $version, ENT_COMPAT, 'UTF-8', FALSE ); ?></h1>
	<hr>

	<h2>Variables</h2>
	Variable: <?php echo htmlspecialchars( $variable, ENT_COMPAT, 'UTF-8', FALSE ); ?> <br><br>
	Init Variable <?php $v = 10; ?> <br><br>
	Show Variable <?php echo htmlspecialchars( $v, ENT_COMPAT, 'UTF-8', FALSE ); ?> <br><br>
	Modifier <?php echo htmlspecialchars( strlen($variable), ENT_COMPAT, 'UTF-8', FALSE ); ?> <br><br>
	Cascade Modifier <?php echo htmlspecialchars( strlen(substr($variable,2,5)), ENT_COMPAT, 'UTF-8', FALSE ); ?> <br><br>
	Scoping (object) <?php echo htmlspecialchars( $user->name, ENT_COMPAT, 'UTF-8', FALSE ); ?> <br><br>
	Scoping (array) <?php echo htmlspecialchars( $week["0"], ENT_COMPAT, 'UTF-8', FALSE ); ?> <br><br>
	Variable as key <?php echo htmlspecialchars( $week[$numbers["0"]], ENT_COMPAT, 'UTF-8', FALSE ); ?> <br><br>
	Var test <?php echo htmlspecialchars( $variable, ENT_COMPAT, 'UTF-8', FALSE ); ?> <br><br>


	<h2>Loop</h2>
	Simple Loop
	<ul>
		<?php $counter1=-1;  if( isset($week) && ( is_array($week) || $week instanceof Traversable ) && sizeof($week) ) foreach( $week as $key1 => $value1 ){ $counter1++; ?>
		<li>
			<?php echo htmlspecialchars( $key1, ENT_COMPAT, 'UTF-8', FALSE ); ?> <?php echo htmlspecialchars( $value1, ENT_COMPAT, 'UTF-8', FALSE ); ?>
		</li>
		<?php } ?>
	</ul><br><br>

	Modifier on Loop
	<ul>
		<?php $counter1=-1; $newvar1=array_reverse($week); if( isset($newvar1) && ( is_array($newvar1) || $newvar1 instanceof Traversable ) && sizeof($newvar1) ) foreach( $newvar1 as $key1 => $i ){ $counter1++; ?>
		<li><?php echo htmlspecialchars( $i, ENT_COMPAT, 'UTF-8', FALSE ); ?></li>
		<?php } ?>
	</ul><br><br>

	Simple Nested Loop
	<ul>
		<?php $counter1=-1;  if( isset($table) && ( is_array($table) || $table instanceof Traversable ) && sizeof($table) ) foreach( $table as $key1 => $value1 ){ $counter1++; ?>
		<li>
			<?php $counter2=-1;  if( isset($value1) && ( is_array($value1) || $value1 instanceof Traversable ) && sizeof($value1) ) foreach( $value1 as $key2 => $value2 ){ $counter2++; ?>
			<?php echo htmlspecialchars( $value2, ENT_COMPAT, 'UTF-8', FALSE ); ?>,
			<?php } ?>
		</li>
		<?php } ?>
	</ul><br><br>

	Loop on created array
	<ul>
		<?php $counter1=-1; $newvar1=range(5,10); if( isset($newvar1) && ( is_array($newvar1) || $newvar1 instanceof Traversable ) && sizeof($newvar1) ) foreach( $newvar1 as $key1 => $i ){ $counter1++; ?>
		<li><?php echo htmlspecialchars( $i, ENT_COMPAT, 'UTF-8', FALSE ); ?></li>
		<?php } ?>
	</ul><br><br>

	<h2>If</h2>
	True condition: <?php if( true ){ ?>This is true<?php } ?> <br><br>
	Modifier inside if: <?php if( is_string($variable) ){ ?>True<?php } ?> <br><br>


	<h2>Function test</h2>
	Simple function: <?php echo time(); ?> <br><br>
	Function with parameters: <?php echo date('d-m-Y'); ?> <br><br>
	Static method: <?php echo Test::method('123test'); ?> <br><br>

	<h2>Escape Text</h2>
	Malicious content: <?php echo htmlspecialchars( $bad_text, ENT_COMPAT, 'UTF-8', FALSE ); ?> <br><br>

	<h2>Custom tag</h2>
	<?php echo call_user_func( static::$conf['registered_tags']['({@.*?@})']['function'], array (
  0 => 
  array (
    0 => '{@message to translate@}',
  ),
  1 => 
  array (
    0 => 'message to translate',
  ),
) ); ?> <br><br>

	<h2>Custom tag 2</h2>
	<?php echo call_user_func( static::$conf['registered_tags']['({%.*?%})']['function'], array (
  0 => 
  array (
    0 => '{%message to translate|english%}',
  ),
  1 => 
  array (
    0 => 'message to translate',
  ),
  2 => 
  array (
    0 => 'english',
  ),
) ); ?> <br><br>

</body>
</html>