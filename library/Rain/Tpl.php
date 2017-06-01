<?php

namespace Rain;

/**
 *  RainTPL
 *  --------
 *  Realized by Federico Ulfo & maintained by the Rain Team
 *  Distributed under GNU/LGPL 3 License
 *
 *  @version 3.0 Alpha milestone: https://github.com/rainphp/raintpl3/issues/milestones?with_issues=no
 */
class Tpl {

    // variables
    public $var = array();

    protected $config = array(),
              $objectConf = array();

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
        'registered_tags' => array(),
        'tags' => array(
            'loop' => array('({loop.*?})', '/{loop="(?P<variable>\${0,1}[^"]*)"(?: as (?P<key>\$.*?)(?: => (?P<value>\$.*?)){0,1}){0,1}}/'),
            'loop_close' => array('({\/loop})', '/{\/loop}/'),
            'loop_break' => array('({break})', '/{break}/'),
            'loop_continue' => array('({continue})', '/{continue}/'),
            'if' => array('({if.*?})', '/{if="([^"]*)"}/'),
            'elseif' => array('({elseif.*?})', '/{elseif="([^"]*)"}/'),
            'else' => array('({else})', '/{else}/'),
            'if_close' => array('({\/if})', '/{\/if}/'),
            'noparse' => array('({noparse})', '/{noparse}/'),
            'noparse_close' => array('({\/noparse})', '/{\/noparse}/'),
            'ignore' => array('({ignore}|{\*)', '/{ignore}|{\*/'),
            'ignore_close' => array('({\/ignore}|\*})', '/{\/ignore}|\*}/'),
            'include' => array('({include.*?})', '/{include="([^"]*)"}/'),
            'function' => array('({function.*?})', '/{function="([a-zA-Z_][a-zA-Z_0-9\:]*)(\(.*\)){0,1}"}/'),
            'variable' => array('({\$.*?})', '/{(\$.*?)}/'),
            'constant' => array('({#.*?})', '/{#(.*?)#{0,1}}/'),
        )
    );

    // tags registered by the developers
    protected static $registered_tags = array();


    /**
     * Draw the template
     *
     * @param string $templateFilePath: name of the template file
     * @param bool $toString: if the method should return a string
     * or echo the output
     *
     * @return void, string: depending of the $toString
     */
    public function draw($templateFilePath, $toString = FALSE) {
        extract($this->var);
        // Merge local and static configurations
        $this->config = $this->objectConf + static::$conf;
        
        ob_start();
        require $this->checkTemplate($templateFilePath);
        $html = ob_get_clean();

        // Execute plugins, before_parse
        $context = static::getPlugins()->createContext(array(
            'code' => $html,
            'conf' => $this->config,
        ));
        static::getPlugins()->run('afterDraw', $context);
        $html = $context->code;

        if ($toString)
            return $html;
        else
            echo $html;
    }

    /**
     * Draw a string
     *
     * @param string $string: string in RainTpl format
     * @param bool $toString: if the param
     *
     * @return void, string: depending of the $toString
     */
    public function drawString($string, $toString = false) {
        extract($this->var);
        // Merge local and static configurations
        $this->config = $this->objectConf + static::$conf;
        ob_start();
        require $this->checkString($string);
        $html = ob_get_clean();

        // Execute plugins, before_parse
        $context = static::getPlugins()->createContext(array(
            'code' => $html,
            'conf' => $this->config,
        ));
        static::getPlugins()->run('afterDraw', $context);
        $html = $context->code;

        if ($toString)
            return $html;
        else
            echo $html;
    }

    /**
     * Configure the object
     *
     * @param string, array $setting: name of the setting to configure
     * or associative array type 'setting' => 'value'
     * @param mixed $value: value of the setting to configure
     * @return \Rain\Tpl $this
     */
    public function objectConfigure($setting, $value = null) {
        if (is_array($setting))
            foreach ($setting as $key => $value)
                $this->objectConfigure($key, $value);
        else if (isset(static::$conf[$setting]))
            $this->objectConf[$setting] = $value;

        return $this;
    }

    /**
     * Configure the template
     *
     * @param string, array $setting: name of the setting to configure
     * or associative array type 'setting' => 'value'
     * @param mixed $value: value of the setting to configure
     */
    public static function configure($setting, $value = null) {
        if (is_array($setting))
            foreach ($setting as $key => $value)
                static::configure($key, $value);
        else if (isset(static::$conf[$setting])) {
            static::$conf[$setting] = $value;

            static::$conf['checksum'][$setting] = $value; // take trace of all config
        }
    }

    /**
     * Assign variable
     * eg.     $t->assign('name','mickey');
     *
     * @param mixed $variable Name of template variable or associative array name/value
     * @param mixed $value value assigned to this variable. Not set if variable_name is an associative array
     *
     * @return \Rain\Tpl $this
     */
    public function assign($variable, $value = null) {
        if (is_array($variable))
            $this->var = $variable + $this->var;
        else
            $this->var[$variable] = $value;

        return $this;
    }

    /**
     * Clean the expired files from cache
     * @param type $expireTime Set the expiration time
     */
    public static function clean($expireTime = 2592000) {
        $files = glob(static::$conf['cache_dir'] . "*.rtpl.php");
        $time = time() - $expireTime;
        foreach ($files as $file)
            if ($time > filemtime($file) )
                unlink($file);
    }

    /**
     * Allows the developer to register a tag.
     *
     * @param string $tag nombre del tag
     * @param regexp $parse regular expression to parse the tag
     * @param anonymous function $function: action to do when the tag is parsed
     */
    public static function registerTag($tag, $parse, $function) {
        static::$registered_tags[$tag] = array("parse" => $parse, "function" => $function);
    }

    /**
     * Registers a plugin globally.
     *
     * @param \Rain\Tpl\IPlugin $plugin
     * @param string $name name can be used to distinguish plugins of same class.
     */
    public static function registerPlugin(Tpl\IPlugin $plugin, $name = '') {
        $name = (string)$name ?: \get_class($plugin);

        static::getPlugins()->addPlugin($name, $plugin);
    }

    /**
     * Removes registered plugin from stack.
     *
     * @param string $name
     */
    public static function removePlugin($name) {
        static::getPlugins()->removePlugin($name);
    }

    /**
     * Returns plugin container.
     *
     * @return \Rain\Tpl\PluginContainer
     */
    protected static function getPlugins() {
        return static::$plugins
            ?: static::$plugins = new Tpl\PluginContainer();
    }

    /**
     * Check if the template exist and compile it if necessary
     *
     * @param string $template: name of the file of the template
     *
     * @throw \Rain\Tpl\NotFoundException the file doesn't exists
     * @return string: full filepath that php must use to include
     */
    protected function checkTemplate($template) {
        // set filename
        $templateName = basename($template);
        $templateBasedir = strpos($template, DIRECTORY_SEPARATOR) ? dirname($template) . DIRECTORY_SEPARATOR : null;
        $templateDirectory = $this->config['tpl_dir'] . $templateBasedir;
        $parsedTemplateFilepath = $this->config['cache_dir'] . $templateName . "." . md5($templateDirectory . serialize($this->config['checksum']) . $template) . '.rtpl.php';
        
        // check if its an absolute path
        if ($template[0] === "/")
            $templateFilepath = $template. "." .$this->config['tpl_ext'];
        else
            $templateFilepath = $templateDirectory.$templateName. '.' .$this->config['tpl_ext'];

        // check if its an absolute path - Damian Kęska        
        if ($template[0] === "/")
            $templateFilepath = $template;

        // if the template doesn't exsist throw an error
        if (!is_file($templateFilepath)) {
            $e = new Tpl\NotFoundException('Template ' . $templateFilepath . ' not found!');
            throw $e->templateFile($templateFilepath);
        }

        // Compile the template if the original has been updated
        if ($this->config['debug'] || !file_exists($parsedTemplateFilepath) || ( filemtime($parsedTemplateFilepath) < filemtime($templateFilepath) )) {
            $parser = new Tpl\Parser($this->config, $this->objectConf, static::$conf, static::$plugins, static::$registered_tags);
            $parser->compileFile($templateName, $templateBasedir, $templateDirectory, $templateFilepath, $parsedTemplateFilepath);
        }
        return $parsedTemplateFilepath;
    }

    /**
     * Compile a string if necessary
     *
     * @param string $string: RainTpl template string to compile
     *
     * @return string: full filepath that php must use to include
     */
    protected function checkString($string) {

        // set filename
        $templateName = md5($string . implode($this->config['checksum']));
        $parsedTemplateFilepath = $this->config['cache_dir'] . $templateName . '.s.rtpl.php';
        $templateFilepath = '';
        $templateBasedir = '';


        // Compile the template if the original has been updated
        if ($this->config['debug'] || !file_exists($parsedTemplateFilepath)) {            
            $parser = new Tpl\Parser($this->config, $this->objectConf, static::$conf, static::$plugins, static::$registered_tags);
            $parser->compileString($templateName, $templateBasedir, $templateFilepath, $parsedTemplateFilepath, $string);
        }

        return $parsedTemplateFilepath;
    }

}
