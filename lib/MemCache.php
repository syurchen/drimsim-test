<?php

declare(strict_types=1);

namespace Drimsim;

class MemCache {
	
	const readLength = 4096;

	const expTime = 3600;

	private $socket;

	public function __construct(string $ip, int $port) {

		if (false === $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP))
			return false;

		if (!socket_connect($this->socket, $ip, $port))
			return false;

		socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));
	}

	public function __destruct() {
		socket_close($this->socket);
	}

	private function write(string $str){
		
		socket_write($this->socket, trim($str) . "\r\n");
	}
	
	private function read(){
		
		$string = socket_read($this->socket, self::readLength);

		//echo "read {$string} \n\n";

		if ($string)
			$string = trim($string);
		return $string;
	}

	public function checkError(){
		if ($err = $this->read() !== "ERROR")
			return false;
		return $err;
	}


	public function storeKey(string $key, int $flag, $value, int $length = null, int $exptime = self::expTime){

		if ($length === null)
			$length = strlen($value);
		$queue = "set {$key} {$flag} {$exptime} {$length}";
		if ($this->write($queue) < 0 || $this->checkError())
			return false;
		$this->write($value);
		if ($this->read() !== "STORED"){
			return false;
		}

		return true;
	}

	static function parseKey(string $str){
		$result = array();
		/* Can be done with regexp more optimally */
		$expl = explode("\n", $str);
		if (count($expl) == 3){
			$expl2 = explode(" ", trim($expl[0]));
			$result['key'] = $expl2[1];
			$result['flag'] = $expl2[2];
			$result['len'] = $expl2[3];
			$result['value'] = trim($expl[1]);
		}
		return $result;
	}

	public function readKey(string $key){
		
		$queue = "get {$key}";
		if ($this->write($queue) < 0)
			return false;
		$result = $this->read();
		return self::parseKey($result);
	}
	
	public function deleteKey(string $key){
		
		$queue = "delete {$key}";
		if ($this->write($queue) < 0)
			return false;
		$result = $this->read();
		return ("DELETED" === trim($result));;
	}

}

?>