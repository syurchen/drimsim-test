<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MainTestAsync extends TestCase
{

	public function testConnect(){
		$Mem = new \Drimsim\Memcache("localhost", 11211, true);
		$this->assertTrue($Mem instanceof \Drimsim\Memcache);
		return $Mem;
	}

	/**
	* @depends testConnect
	*/
	public function testStore($Mem){
		$key = "key";
		$flag = 0;
		$val = "xyz";
		$this->assertTrue($Mem->storeKey($key, $flag, $val));

		return array($Mem, $key, $flag, $val);

	}

	/**
	* @depends testStore
	*/
	public function testGet($params){
		$result = $params[0]->readKey($params[1]);

		$this->assertTrue(isset($result['value']));

		$this->assertTrue($result['key'] == $params[1]);
		$this->assertTrue($result['flag'] == $params[2]);
		$this->assertTrue($result['value'] == $params[3]);
	}

	/**
	* @depends testStore
	*/
	public function testDelete($params){
		$this->assertTrue($params[0]->deleteKey($params[1]));
		$this->assertFalse($params[0]->deleteKey($params[1]));
		/* reading deleted key, returns empty array */
		$this->assertSame($params[0]->readKey($params[1]), array());
	}

}