<?php
namespace Rain\Tpl;

require_once __DIR__ . '/IPlugin.php';

/**
 * Basic plugin implementation.
 * - It allows to define hooks as property.
 * - Options can be passed in constructor.
 *   When a setter set_{optionname}() exists it is used to store the option value.
 *   Otherwise \InvalidArgumentException is thrown.
 */
class Plugin implements IPlugin
{
	/**
	 * This should be an array containing:
	 * - a key/value pair where key is hook name and value is implementing method,
	 * - a value only when hook has same name as method.
	 *
	 * @var array
	 */
	protected $hooks = array();

	public function  __construct($options = array())
	{
		$this->set_options($options);
	}
	/**
	 * Returns a list of hooks that are implemented by the plugin.
	 * This should be an array containing:
	 * - a key/value pair where key is hook name and value is implementing method,
	 * - a value only when hook has same name as method.
	 */
	public function declare_hooks() {
		return $this->hooks;
	}

	/**
	 * Sets plugin options.
	 *
	 * @var array
	 */
	public function set_options($options) {
		foreach ((array) $options as $key => $val) {
			$this->set_option($key, $val);
		}
		return $this;
	}

	/**
	 * Sets plugin option.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @throws \InvalidArgumentException Wrong option name or value
	 * @return Plugin
	 */
	public function set_option($name, $value) {
		$method = 'set_' . $name;
		if (!\method_exists($this, $method)) {
			throw new \InvalidArgumentException('Key "' . $name . '" is not a valid settings option' );
		}
		$this->{$method}($value);
		return $this;
	}
}
