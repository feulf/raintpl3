<?php

class TplTest extends PHPUnit_Framework_TestCase
{
	private $engine = null;

	/**
	 * Sets include path to raintpl lib.
	 */
	public function setup()
	{
		$this->configure();
		$this->engine = new \Rain\Tpl();
	}

	private function configure()
	{
		Rain\Tpl::configure(array(
			'debug' => true,
			'tpl_dir' => __DIR__ . '/_files/',
			'cache_dir' => __DIR__ . '/../../cache/',
		));
	}

	public function testCallHooks()
	{
		// Make a mock that registers to all hooks and check required context keys.
		$hooks = array(
			'beforeParse' => array('code', 'template_basedir', 'template_filepath', 'conf'),
			'afterParse' => array('code', 'template_basedir', 'template_filepath', 'conf'),
		);

		// init mock plugin
		$methods = array_keys($hooks);
		$plugin = $this->getMock(
			'Rain\Tpl\IPlugin',
			array_merge(array('declareHooks', 'setOptions'), $methods)
		);
		$plugin->expects($this->once())
			->method('declareHooks')
			->will($this->returnValue($methods));
		foreach ($hooks as $method => $required) {
			$contstrains = array();
			foreach ($required as $key) {
				$contstrains[] = $this->arrayHasKey($key);
            }
			$plugin->expects($this->once())
				->method($method)
				->with(call_user_func_array(array($this, 'logicalAnd'), $contstrains));
		}

		// register plugin and draw template
		Rain\Tpl::register_plugin($plugin);
		$this->engine->draw('template', true);
	}
}