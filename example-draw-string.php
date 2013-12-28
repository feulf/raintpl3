<?php
/**
* Just a test file to showcase several things in raintpl
*
* PHP version 5
*
* @category Raintpl
* @package  Raintpl
* @author   Federico Ulfoa <Federico_Ulfo@github.com>
* @license  GPL v3
* @link     https://github.com/rainphp/raintpl3
*/
require 'library/Rain/autoload.php';

use Rain\Tpl;

memoryUsageStart();
timerStart();

require 'library/Rain/Tpl.php';

$config = array(
    "base_url"      => null,
    "tpl_dir"       => "templates/test/",
    "cache_dir"     => "cache/",
    "debug"         => true // set to false to improve the speed
);

Tpl::configure($config);

// Add PathReplace plugin
Tpl::registerPlugin(new Tpl\Plugin\PathReplace());

// set variables
$var = array(
    "variable"	=> "Hello World!",
    "version"	=> "3.0 Alpha",
    "menu"		=> array(
        array(
              "name" => "Home",
              "link" => "index.php",
              "selected" => true
        ),
        array(
              "name" => "FAQ",
              "link" => "index.php/FAQ/",
              "selected" => null
        ),
        array(
              "name" => "Documentation",
              "link" => "index.php/doc/",
              "selected" => null
        )
    ),
    "week"		=> array(
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
        "Sunday"
    ),
    "user"		=> (object) array(
        "name" => "Rain",
        "citizen" => "Earth",
        "race" => "Human"
    ),
    "numbers"	=> array(3, 2, 1),
    "bad_text"	=> "Hey this is a malicious XSS <script>
                    alert('auto_escape is set enabled by default,
                    so this script is escaped!');</script>",
    "table"		=> array(
        array(
            "Apple",
            "1996"
        ),
        array(
            "PC",
            "1997"
        )
    ),
    "title"		=> "Rain TPL 3 - Easy and Fast template engine",
    "copyright" => "Copyright 2006 - 2012 Rain TPL<br>Project By Rain Team",

);

// add a tag
Tpl::registerTag(
    "({@.*?@})", "{@(.*?)@}", function ($params) { // function called by the tag
        $value = $params[1][0];

        return "Translate: <b>" . $value . "</b>";
    }
);

// add a tag
Tpl::registerTag(
    "({%.*?%})", // preg split
    "{%(.*?)(?:\|(.*?))%}", // preg match
    function ($params) {
        $value = $params[1][0];
        $value2 = $params[2][0];

        return "Translate: <b>$value</b> in <b>$value2</b>";
    }
);

$string = file_get_contents("templates/test/test.html");

/**
* Just a testing class
* 
* @category Raintpl
* @package  Raintpl
* @author   Federico Ulfoa <Federico_Ulfo@github.com>
* @license  GPL v3
* @link     https://github.com/rainphp/raintpl3
*/
class Test
{
    /**
     * Testing method
     *
     * @param mixed $variable Just a variable to output
     * 
     * @return test
    */
    public static function method($variable)
    {
        echo "Hi I am a static method,
        and this is the parameter passed to me: " . $variable . "!";
    }
}

// draw
$tpl = new Tpl;
$tpl->assign($var);
echo $tpl->drawString($string);

//-------------------------------------------------------------
//
//	BENCHMARK/DEBUG FUNCTIONS
//
//-------------------------------------------------------------

echo "<br>---------<br>";
echo memoryUsage();
echo "<br>";
echo timer();

/**
 * Useful for debug, print the variable $mixed and die
 * 
 * @param mixed   $mixed Any variable
 * @param boolean $exit  Die after output?
 * 
 * @return nothing
 */
function dump($mixed, $exit = true)
{
    echo "<pre>dump " . print_r($mixed, true) . "<pre>";
    if ($exit) {
        exit;
    }
}

/**
 * Save the memory used at this point
 *
 * @param string $memName which memory to store
 *
 * @return nothing
 */
function memoryUsageStart($memName = "execution_time")
{
    return $GLOBALS['memoryCounter'][$memName] = memory_get_usage();
}

/**
 * Get the memory used
 *
 * @param string  $memName    which memory to retrieve
 * @param boolean $byteFormat retrieve in byteFormat?
 *
 * @return nothing
 */
function memoryUsage($memName = "execution_time", $byteFormat = true)
{
    $totMem = memory_get_usage() - $GLOBALS['memoryCounter'][ $memName ];

    return $byteFormat ? byteFormat($totMem) : $totMem;
}

/* time functions below */

/**
 * Start the timer
 *
 * @param string $timeName start timer for what
 *
 * @return nothing
 */
function timerStart($timeName = "execution_time")
{
    $stimer = explode(' ', microtime());
    $GLOBALS['timeCounter'][$timeName] = $stimer[1] + $stimer[0];
}

/**
 * Get the time passed
 *
 * @param string  $timeName  which timer to retrieve
 * @param integer $precision which precision to use
 *
 * @return nothing
 */
function timer($timeName = "execution_time", $precision = 10)
{
    $etimer = explode(' ', microtime());
    $timeElapsed = $etimer[ 1 ] + $etimer[ 0 ] - $GLOBALS['timeCounter'][$timeName];

    return substr($timeElapsed, 0, $precision);
}

/**
 * Convert byte to more readable format, like "1 KB" instead of "1024".
 * cut_zero, remove the 0 after comma ex:  10,00 => 10	  14,30 => 14,3
 *
 * @param integer $size the size to format
 * 
 * @return string  formatted number
 */
function byteFormat($size)
{
    if ($size > 0) {
        $unim = array("B", "KB", "MB", "GB", "TB", "PB");
        for ($i=0; $size >= 1024; $i++) {
            $size = $size / 1024;
        }

        return number_format($size, $i ? 2 : 0, ',', '.') . " " . $unim[$i];
    }
}
