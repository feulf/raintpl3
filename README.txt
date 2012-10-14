Rain TPL 3
------------

New features:
Better parser in less code
Plugins system to add functionality (included path_replace and image_resize plugins)
Back-compatibility with Rain TPL 2 templates
Cascade modifier, {$title|strtolower|ucfirst}
Register Tag, to create custom tags that call closure functions, example: {@translate this text@}
Loop tag improved: {loop="list" as $i => $array} ... {/loop} and {loop="range(0,3)" as $i }
Modifier can be called into if tag {if="$title|streln > 10"} ... {/if}
Added Clean() method to delete old cache files
Added autoescape
Removed Static Cache
More examples


ToDo/Wish list:
Short syntax:  {loop $var}   {if $var}
Template auto generation, from default template (to make easier the process of creating the template)
Better Sandbox
Better Error management
Javascript integration with Ajax/JSON



Run the unit test after each update of the class:
$phpunit tests