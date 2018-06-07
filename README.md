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
