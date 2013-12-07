<?php
/**
* The main template class
*
* PHP version 5
*
* @category Raintpl
* @package  Raintpl
* @author   Federico Ulfoa <Federico_Ulfo@github.com>
* @license  GPL v3
* @link     https://github.com/rainphp/raintpl3
*/
namespace Rain;

/**
* RainTPL v3.0 Alpha
* https://github.com/rainphp/raintpl3/issues/milestones?with_issues=no
* --------
* Realized by Federico Ulfo & maintained by the Rain Team
* Distributed under GNU/LGPL 3 License
*
* @category Raintpl
* @package  Raintpl
* @author   Federico Ulfoa <Federico_Ulfo@github.com>
* @license  GPL v3
* @link     https://github.com/rainphp/raintpl3
*/
class Tpl
{
    // variables
    public $var = array();

    protected $config = array(), $objectConf = array();

    /**
     * Plugin container
     *
     * @var \Rain\Tpl\PluginContainer
     */
    protected static $plugins = null;

    // configuration
    protected static $conf = array(
        'checksum' => array(),
        'charset' => 'UTF-8',
        'debug' => false,
        'tpl_dir' => 'templates/',
        'cache_dir' => 'cache/',
        'tpl_ext' => 'html',
        'base_url' => '',
        'php_enabled' => false,
        'auto_escape' => true,
        'sandbox' => true,
        'remove_comments' => false,
        'registered_tags' => array(),
    );

    // tags registered by the developers
    protected static $registered_tags = array();

    /**
     * Draw the template
     *
     * @param string $templateFilePath name of the template file
     * @param bool   $toString         if the method should return a string
     * or echo the output
     *
     * @return void, string: depending of the $toString
     */
    public function draw($templateFilePath, $toString = false)
    {
        extract($this->var);
        // Merge local and static configurations
        $this->config = $this->objectConf + static::$conf;

        ob_start();
        include $this->checkTemplate($templateFilePath);
        $html = ob_get_clean();

        // Execute plugins, before_parse
        $context = $this->getPlugins()->createContext(
            array(
                'code' => $html,
                'conf' => $this->config,
            )
        );
        $this->getPlugins()->run('afterDraw', $context);
        $html = $context->code;

        if ($toString) {
            return $html;
        } else {
            echo $html;
        }
    }

    /**
     * Draw a string
     *
     * @param string $string   string in RainTpl format
     * @param bool   $toString if the param
     *
     * @return void, string depending on $toString
     */
    public function drawString($string, $toString = false)
    {
        extract($this->var);
        // Merge local and static configurations
        $this->config = $this->objectConf + static::$conf;
        ob_start();
        include $this->checkString($string);
        $html = ob_get_clean();

        // Execute plugins, before_parse
        $context = $this->getPlugins()->createContext(
            array(
                'code' => $html,
                'conf' => $this->config
            )
        );
        $this->getPlugins()->run('afterDraw', $context);
        $html = $context->code;

        if ($toString) {
            return $html;
        } else {
            echo $html;
        }
    }

    /**
     * Object specific configuration
     *
     * @param string|array $setting name of the setting to configure
     *                              or associative array type 'setting' => 'value'
     * @param mixed        $value   value of the setting to configure
     *
     * @return \Rain\Tpl $this
     */
    public function objectConfigure($setting, $value = null)
    {
        if (is_array($setting)) {
            foreach ($setting as $key => $value) {
                $this->objectConfigure($key, $value);
            }
        } else if (isset(static::$conf[$setting])) {
            // add ending slash if missing
            if ($setting == "tpl_dir" || $setting == "cache_dir") {
                $value = self::_addTrailingSlash($value);
            }
            $this->objectConf[$setting] = $value;
        }

        return $this;
    }

    /**
     * Global configurations
     *
     * @param string|array $setting name of the setting to configure
     *                               or associative array type 'setting' => 'value'
     * @param mixed        $value   value of the setting to configure
     *
     * @return nothing
     */
    public static function configure($setting, $value = null)
    {
        if (is_array($setting)) {
            foreach ($setting as $key => $value) {
                static::configure($key, $value);
            }
        } else if (isset(static::$conf[$setting])) {
            // add ending slash if missing
            if ($setting == "tpl_dir" || $setting == "cache_dir") {
                $value = self::_addTrailingSlash($value);
            }

            static::$conf[$setting] = $value;
            static::$conf['checksum'][$setting] = $value; // take trace of all config
        }
    }

    /**
     * Assign variable
     * eg. $t->assign('name','mickey');
     *
     * @param mixed $variable Name of template variable
     *                        or associative array name/value
     * @param mixed $value    value assigned to this variable.
     *                        Not set if variable_name is an associative array
     *
     * @return \Rain\Tpl $this
     */
    public function assign($variable, $value = null)
    {
        if (is_array($variable)) {
            $this->var = $variable + $this->var;
        } else {
            $this->var[$variable] = $value;
        }
        
        return $this;
    }

    /**
     * Clean the expired files from cache
     * 
     * @param integer $expireTime Set the expiration time
     * 
     * @return nothing
     */
    public static function clean($expireTime = 2592000)
    {
        $files = glob(static::$conf['cache_dir'] . "*.rtpl.php");
        $time = time() - $expireTime;
        foreach ($files as $file) {
            if ($time > filemtime($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Allows the developer to register a tag.
     *
     * @param string   $tag      nombre del tag
     * @param regexp   $parse    regular expression to parse the tag
     * @param function $function action to do when the tag is parsed
     *
     * @return nothing
     */
    public static function registerTag($tag, $parse, $function)
    {
        static::$registered_tags[$tag] = array(
            "parse" => $parse,
            "function" => $function
        );
    }

    /**
     * Registers a plugin globally.
     *
     * @param \Rain\Tpl\IPlugin $plugin the plugin to register
     * @param string            $name   name can be used to
     *                                  distinguish plugins of same class.
     *
     * @return nothing
     */
    public static function registerPlugin(Tpl\IPlugin $plugin, $name = '')
    {
        $name = (string) $name ?: \get_class($plugin);

        static::getPlugins()->addPlugin($name, $plugin);
    }

    /**
     * Removes registered plugin from stack.
     *
     * @param string $name which plugin to remove
     *
     * @return nothing
     */
    public static function removePlugin($name)
    {
        static::getPlugins()->removePlugin($name);
    }

    /**
     * Returns plugin container.
     *
     * @return \Rain\Tpl\PluginContainer
     */
    protected static function getPlugins()
    {
        return static::$plugins ?: static::$plugins = new Tpl\PluginContainer();
    }

    /**
     * Check if the template exist and compile it if necessary
     *
     * @param string $template name of the file of the template
     *
     * @throw \Rain\Tpl\NotFoundException the file doesn't exists
     * @return string full filepath that php must use to include
     */
    protected function checkTemplate($template)
    {
        // set filename
        $templateName = basename($template);
        $templateBasedir = strpos($template, DIRECTORY_SEPARATOR) !== false ? dirname($template) . DIRECTORY_SEPARATOR : null;
        $templateDirectory = null;
        $templateFilepath = null;
        $parsedTemplateFilepath = null;

        // Make directories to array for multiple template directory
        $templateDirectories = $this->config['tpl_dir'];
        if (!is_array($templateDirectories)) {
            $templateDirectories = array($templateDirectories);
        }

        $isFileNotExist = true;

        // absolute path
        if ($template[0] == '/') {
            $templateDirectory = $templateBasedir;
            $templateFilepath = $templateDirectory . $templateName . '.' . $this->config['tpl_ext'];
            $parsedTemplateFilepath = $this->config['cache_dir'] . $templateName . "." . md5($templateDirectory . serialize($this->config['checksum'])) . '.rtpl.php';
            // For check templates are exists
            if (file_exists($templateFilepath)) {
                $isFileNotExist = false;
            }
        } else {
            foreach ($templateDirectories as $templateDirectory) {
                $templateDirectory .= $templateBasedir;
                $templateFilepath = $templateDirectory . $templateName . '.' . $this->config['tpl_ext'];
                $parsedTemplateFilepath = $this->config['cache_dir'] . $templateName . "." . md5($templateDirectory . serialize($this->config['checksum'])) . '.rtpl.php';

                // For check templates are exists
                if (file_exists($templateFilepath)) {
                    $isFileNotExist = false;
                    break;
                }
            }
        }

        // if the template doesn't exsist throw an error
        if ($isFileNotExist === true) {
            $e = new Tpl\NotFoundException('Template ' . $templateName . ' not found!');
            throw $e->templateFile($templateFilepath);
        }

        // Compile the template if the original has been updated
        if ($this->config['debug'] || !file_exists($parsedTemplateFilepath) || (filemtime($parsedTemplateFilepath) < filemtime($templateFilepath) )) {
            $parser = new Tpl\Parser($this->config, static::$plugins, static::$registered_tags);
            $parser->compileFile($templateName, $templateBasedir, $templateDirectory, $templateFilepath, $parsedTemplateFilepath);
        }

        return $parsedTemplateFilepath;
    }

    /**
     * Compile a string if necessary
     *
     * @param string $string RainTpl template string to compile
     *
     * @return string full filepath that php must use to include
     */
    protected function checkString($string)
    {
        /**
         * set filename
        */
        $templateName = md5($string . implode($this->config['checksum']));
        $parsedTemplateFilepath = $this->config['cache_dir'] . $templateName . '.s.rtpl.php';
        $templateFilepath = '';
        $templateBasedir = '';

        /**
         * Compile the template if the original has been updated
        */
        if ($this->config['debug'] || !file_exists($parsedTemplateFilepath)) {
            $parser = new Tpl\Parser($this->config, static::$plugins, static::$registered_tags);
            $parser->compileString($templateName, $templateBasedir, $templateFilepath, $parsedTemplateFilepath, $string);
        }

        return $parsedTemplateFilepath;
    }

    /**
     * Internal function to add a trailing slash to folder names (depending on the parameter)
     *
     * @param string|array $folder the folder(s) to add the trailing slash to
     *
     * @return string|array the folder(s) with the trailing slashes applied
    */
    private static function _addTrailingSlash($folder)
    {
        if (is_array($folder)) {
            foreach ($folder as &$f) {
                $f = self::_addTrailingSlash($f);
            }
        } elseif ( strlen($folder) > 0 && $folder[0] != '/' ) {
            $folder = $folder . "/";
        }

        return $folder;
    }
}
