<?php

/*-
 * Copyright © 2011–2014 Federico Ulfo and a lot of awesome contributors
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * “Software”), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

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
		Rain\Tpl::registerPlugin($plugin);
		$this->engine->draw('template', true);
	}
}