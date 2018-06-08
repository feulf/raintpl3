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

require_once __DIR__ . '/../TplTest.php';

class PluginTest extends PHPUnit_Framework_TestCase
{
	public function testDeclareHooks()
	{
		$plugin = new PluginTestPlugin();
		$plugin->set_hooks(array('before_parse'));
		$this->assertEquals(array('before_parse'), $plugin->declareHooks());
	}

	public function testSetOption()
	{
		$plugin = $this->getMock('Rain\Tpl\Plugin', array('setParam'));
		$plugin->expects($this->once())
			->method('setParam')
			->with($this->equalTo('value'));
		$plugin->setOption('param', 'value');
		$this->setExpectedException('InvalidArgumentException');
		$plugin->setOption('unknown_param', 'value');
	}

	public function testSetOptions()
	{
		$plugin = $this->getMock('Rain\Tpl\Plugin', array('setParam'));
		$plugin->expects($this->once())
			->method('setParam')
			->with($this->equalTo('value'));
		$plugin->setOptions(array('param' => 'value'));
	}
}

class PluginTestPlugin extends Rain\Tpl\Plugin
{
	public $param = null;
	public function set_hooks($hooks) {$this->hooks = $hooks;}
	public function set_param($param) {$this->param = $param;}
}
