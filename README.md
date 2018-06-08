RainTPL 3
=========

By Federico Ulfo and a lot [awesome contributors](https://github.com/rainphp/raintpl3/network)!

[RainTPL](http://raintpl.com) is an easy template engine for PHP that enables designers and developers to work better together, it loads HTML template to separate the presentation from the logic.

Features
--------
* Easy for designers, only 10 tags, *{$variable}*, *{#constant#}*, *{include}*, *{loop}*, *{if}*, *{* comment *}*, *{noparse}*, *{function}*
* Easy for developers, 5 methods to load and draw templates.
* Powerful, modifier and operation with variables
* Extensible, load plugins and register new tags
* Secure, sandbox with blacklist.


Installation / Usage
--------------------

1. Install composer https://github.com/composer/composer
2. Create a composer.json inside your application folder:

    ``` json
    {
        "require": {
            "rain/raintpl": ">=3.0.0"
        }
    }
    ```
3. Run the following code

    ``` sh
    $ php composer.phar install
    ```

4. Run one example of RainTPL with your browser: ```http://localhost/raintpl3/example.php```

Documentation
-------------
The [documentation](https://github.com/rainphp/raintpl3/wiki/Documentation) of RainTPL is divided in [documentation for web designers](https://github.com/rainphp/raintpl3/wiki/Documentation-for-web-designers) and [documentation for PHP developers](https://github.com/rainphp/raintpl3/wiki/Documentation-for-PHP-developers).


Licence
-------

RainTPL 3, like its antecessor Rain.TPL version 2, is, as of 2018-06-07, published under the MIT Licence.

The above applies to RainTPL 3 itself, not the entire content of this repository. Some of the `example-*.php` files and the content below `templates/` in this repository are copies of external code under various licences, such as:

* Twitter Bootstrap, under the Apache v2 licence
* LESS - Leaner CSS, under the Apache v2 licence

Note that not all external content comes with full source code, it’s usually just a copy of (possibly minified or otherwise modified) parts of their distribution. The example files may be used as starting points without limitation except the licences on the external content.
