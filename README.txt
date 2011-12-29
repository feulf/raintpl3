Rain TPL 3
------------

New features:
Better parser in less code
Back-compatibility with Rain TPL 2
Cascade modifier, {$title|strtolower|ucfirst}
Register Tag, to create custom tags that call closure functions, example: {@translate this text@}
Loop tag improved: {loop="list" as $i => $array} ... {/loop} and {loop="range(0,3)" as $i }
Modifier can be called into if tag {if="$title|streln > 10"} ... {/if}
Added Clean method to delete old cache files
Added autoescape
Removed Sandbox and Static Cache


ToDo/Wish list:
Compatible with Twig and other template engine, as simple as write raintpl::configure( "syntax", "twig" );
Short syntax:  {loop $var}   {if $var}
Template auto generation, from default template (to make easier the process of creating the template)
Better Sandbox
Better Error management
Namespace? May be.
Javascript integration with Ajax/JSON
More examples, with new cool tools as Less CSS and Twitter Bootstrap
