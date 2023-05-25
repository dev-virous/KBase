<?php
error_reporting(0);
class KBase{
	private $conn;
	public function __construct($host, $username, $password, $database){
		$this->conn = new mysqli($host, $username, $password, $database);
		if ($this->conn->connect_error){
			die("Connection failed: " . $this->conn->connect_error);
		}
		$this->conn->query("CREATE TABLE IF NOT EXISTS KBase (key_name VARCHAR(255) PRIMARY KEY, value TEXT, expire INT(11))");
		$this->conn->query("CREATE TABLE IF NOT EXISTS sets (key_name VARCHAR(255), member TEXT, PRIMARY KEY (key_name, member))");
	}
	public function __destruct(){
		$this->conn->close();
	}
	public function set($key, $value, $expire = null){
		$expire_time = $expire ? time() + $expire : null;
		$stmt = $this->conn->prepare("INSERT INTO KBase (key_name, value, expire) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value), expire = VALUES(expire)");
		$stmt->bind_param("ssi", $key, $value, $expire_time);
		$stmt->execute();
		$stmt->close();
	}
	public function get($key){
		$stmt = $this->conn->prepare("SELECT value, expire FROM KBase WHERE key_name = ?");
		$stmt->bind_param("s", $key);
		$stmt->execute();
		$stmt->bind_result($value, $expire_time);
		$stmt->fetch();
		$stmt->close();
		if ($expire_time && $expire_time < time()){
			$this->delete($key);
			return null;
		}
		return $value;
	}
	public function sadd($key, $member){
		$stmt = $this->conn->prepare("INSERT INTO sets (key_name, member) VALUES (?, ?)");
		$stmt->bind_param("ss", $key, $member);
		$stmt->execute();
		$stmt->close();
	}
	public function smembers($key){
		$stmt = $this->conn->prepare("SELECT member FROM sets WHERE key_name = ?");
		$stmt->bind_param("s", $key);
		$stmt->execute();
		$stmt->bind_result($member);
		$members = array();
		while ($stmt->fetch()){
			$members[] = $member;
		}
		$stmt->close();
		return $members;
	}
	public function expire($key, $expire){
		$expire_time = time() + $expire;
		$stmt = $this->conn->prepare("UPDATE KBase SET expire = ? WHERE key_name = ?");
		$stmt->bind_param("is", $expire_time, $key);
		$stmt->execute();
		$stmt->close();
	}
	public function delete($key){
		$stmt = $this->conn->prepare("DELETE FROM KBase WHERE key_name = ?");
		$stmt->bind_param("s", $key);
		$stmt->execute();
		$stmt->close();
		$stmt = $this->conn->prepare("DELETE FROM sets WHERE key_name = ?");
		$stmt->bind_param("s", $key);
		$stmt->execute();
		$stmt->close();
	}
	public function keys(){
		$stmt = $this->conn->prepare("SELECT key_name FROM KBase");
		$stmt->execute();
		$stmt->bind_result($key);
		$keys = array();
		while ($stmt->fetch()){
			$keys[] = $key;
		}
		$stmt->close();
		return $keys;
	}
	public function ttl($key){
		$stmt = $this->conn->prepare("SELECT expire FROM KBase WHERE key_name = ?");
		$stmt->bind_param("s", $key);
		$stmt->execute();
		$stmt->bind_result($expire_time);
		$stmt->fetch();
		$stmt->close();
		if($expire_time && $expire_time < time()){
			$this->delete($key);
			return -2;
		}elseif(!$expire_time){
			return -1;
		}else{
			return $expire_time - time();
		}
	}
}