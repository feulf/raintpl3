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

class PluginContainerTest extends PHPUnit_Framework_TestCase
{
	private $container = null;

	public function setUp()
	{
		$this->container = new Rain\Tpl\PluginContainer();
	}

	public function testSetAndRun()
	{
		$context = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
		// mock plugin with default and custom hook methods
		$first = $this->getMock('Rain\Tpl\IPlugin', array('declareHooks', 'before', 'custom', 'setOptions'));
		$first->expects($this->once())
			->method('declareHooks')
			->will($this->returnValue(array('before', 'after' => 'custom')));
		$first->expects($this->once())
			->method('before');
		$first->expects($this->once())
			->method('custom');
		// second mock to test that all plugins are executed
		$second = $this->getMock('Rain\Tpl\IPlugin', array('declareHooks', 'before', 'custom', 'setOptions'));
		$second->expects($this->once())
			->method('declareHooks')
			->will($this->returnValue(array('before' =>'before')));
		$second->expects($this->once())
			->method('before');
		// set plugins and run them
		$this->container->setPlugin('first', $first)
			->setPlugin('second', $second)
			->run('before', $context);
		$this->assertEquals('before', $context->_hook_name);
		$this->container->run('after', $context);
		$this->assertEquals('after', $context->_hook_name);
	}

	public function testRejectBadPlugin()
	{
		$plugin = $this->getMock('Rain\Tpl\IPlugin', array('declareHooks', 'setOptions'));
		$plugin->expects($this->once())
			->method('declareHooks')
			->will($this->returnValue(array('before' => 'no_method')));
		$this->setExpectedException('InvalidArgumentException');
		$this->container->setPlugin('third', $plugin);
	}

	public function testRemovePlugin()
	{
		// mock 2 plugins, one will stay and another will be removed
		// Kept will be executed once, removed should be never executed.
		$kept = $this->getMock('Rain\Tpl\IPlugin', array('declareHooks', 'remove', 'setOptions'));
		$kept->expects($this->once())->method('declareHooks')
			->will($this->returnValue(array('remove')));
		$kept->expects($this->once())->method('remove');

		$removed = $this->getMock('Rain\Tpl\IPlugin', array('declareHooks', 'remove', 'setOptions'));
		$removed->expects($this->once())->method('declareHooks')
			->will($this->returnValue(array('remove')));
		$removed->expects($this->never())->method('remove');

		$this->container->setPlugin('kept', $kept);
		$this->container->setPlugin('removed', $removed);

		// remove one plugin and trigger hook.
		$this->container->removePlugin('removed');
		$this->container->run('remove', new ArrayObject());
	}

	public function testAddPlugin()
	{
		$plugin = $this->getMock('Rain\Tpl\IPlugin', array('declareHooks', 'setOptions'));
		$plugin->expects($this->once())->method('declareHooks')
			->will($this->returnValue(array()));

		$this->container->addPlugin('added', $plugin);
		$this->setExpectedException('InvalidArgumentException');
		$this->container->addPlugin('added', $plugin);
	}

	public function testCreateContext()
	{
		$context = $this->container->createContext(array('param' => 'value'));
		$this->assertEquals('value', $context->param);
		$this->assertEquals('value', $context['param']);
	}
}
