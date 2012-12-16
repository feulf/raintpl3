<?php if(!class_exists('Rain\Tpl')){exit;}?><!DOCTYPE html>
<html lang="en">
    <head>
        <title>Test</title>
        <link rel="stylesheet" href="<?php echo static::$conf['base_url']; ?>templates/nested_loop/style.css" type="text/css" />
    </head>
    <body>

        <h3>Loop <font color="red">NEST</font> example with associative array</h3>
    <tt>
        <ul> 
            <li>ID _ Name _ Color</li> 
            <?php $counter1=-1;  if( isset($user) && ( is_array($user) || $user instanceof Traversable ) && sizeof($user) ) foreach( $user as $key1 => $value1 ){ $counter1++; ?>
            <li class="color<?php echo htmlspecialchars( $counter1%2+1, ENT_COMPAT, 'UTF-8', FALSE ); ?>"><?php echo htmlspecialchars( $key1, ENT_COMPAT, 'UTF-8', FALSE ); ?>) - <?php echo htmlspecialchars( strtoupper($value1["name"]), ENT_COMPAT, 'UTF-8', FALSE ); ?> - <?php echo htmlspecialchars( $value1["color"], ENT_COMPAT, 'UTF-8', FALSE ); ?> 
                <?php if( !empty($value1["orders"]) ){ ?> 
                <ul> 
                    <?php $counter2=-1;  if( isset($value1["orders"]) && ( is_array($value1["orders"]) || $value1["orders"] instanceof Traversable ) && sizeof($value1["orders"]) ) foreach( $value1["orders"] as $key2 => $value2 ){ $counter2++; ?>
                    <li><?php echo htmlspecialchars( $value1["name"], ENT_COMPAT, 'UTF-8', FALSE ); ?> - <?php echo htmlspecialchars( $key1, ENT_COMPAT, 'UTF-8', FALSE ); ?> - <?php echo htmlspecialchars( $counter1, ENT_COMPAT, 'UTF-8', FALSE ); ?> - <?php echo htmlspecialchars( $counter2, ENT_COMPAT, 'UTF-8', FALSE ); ?> - <?php echo htmlspecialchars( $key2, ENT_COMPAT, 'UTF-8', FALSE ); ?> - {function str_replace(array('sn','rf','fsd'), '',$value.order_id)} - <?php echo htmlspecialchars( $value2["order_id"], ENT_COMPAT, 'UTF-8', FALSE ); ?> - <?php echo htmlspecialchars( $value2["order_name"], ENT_COMPAT, 'UTF-8', FALSE ); ?></li> 
                    <?php } ?> 
                </ul> 
                <?php } ?> 
            </li>
            <?php } ?> 
        </ul> 
    </tt> 

</body>
</html>