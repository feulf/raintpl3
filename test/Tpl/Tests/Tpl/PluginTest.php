<?php
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
