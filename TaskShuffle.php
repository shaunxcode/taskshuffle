<?php

namespace TaskShuffle;

const SALT = 'TaskShizzufIzzle';

require_once 'persysiphus.php';
use \Persysiphus\Collection;
\Persysiphus\dataDir(dirname(__FILE__) . '/data/');

class TaskShuffle {
	public static function register($email, $password) {
		if(empty($email)) {
			throw new \Exception('Email can not be empty');
		}

		$User = Collection::User();

		if($User->getBy('email', $email)) {
			throw new \Exception('Email has already been used');
		}

		if(empty($error)) {
			return $User->create(array(
				'email' => $email,
				'password' => md5(SALT . $password), 
				'lists' => array()));
		}
	}
	
	public static function authenticate($email, $password) {
		if($user = Collection::User()->getFirstBy('email', $email)) {
			if($user->password == md5(SALT . $password)) {
				return $user;
			}
		}
		Errors::add("Incorrect email or password");
		return false;
	}

	public static function createUserList($uid, $name) {
		$Users = Collection::User(); 
		$user = $Users->get($uid);
		$name = str_replace(array(' ', '.'), array('_', '_'), $name);
		$uqn = $user->email . '.' . $name;
		if(!Collection::doesExist($uqn)) {
			$list = \Persysiphus\Collection($user->email . '.' . $name)
				->saveInfo(array(
					'user' => $uid,
					'private' => true,
					'readOnly' => true,
					'sharedWith' => array(),
					'itemsTotal' => 0,
					'itemsComplete' => 0));
		
			$user->lists[] = $name;
			$Users->update($user);
			return $list;
		}
	}
	
	public static function setListPrivacy($uqn, $state) {
		return self::getListByUQN($uqn)->private = $state;
	}
	
	public static function setListReadOnly($uqn, $state) {
		return self::getListByUQN($uqn)->readOnly = $state;
	}
	
	public static function getUser($uid) {
		return Collection::User()->get($uid);
	}
	
	public static function getListByUQN($uqn) {
		return Collection::doesExist($uqn) ? new Collection($uqn) : false;
	}
	
	public static function emailDistinct($email) {
		return !(bool)Collection::User()->getBy('email', $email);
	}
}

class Session {
	public static function __callStatic($name, $args) {
		if(!empty($args)) { 
			$_SESSION[$name] = current($args);
		} else {
			return isset($_SESSION[$name]) ? $_SESSION[$name] : false;
		}
	}
}

class Errors {
	private static $errors = array(); 
	
	public static function add($error) {
		self::$errors[] = $error;
	}
	
	public static function doExist() { 
		return !empty(self::$errors);
	}
	
	public static function getAll() {
		return empty(self::$errors) ? false : self::$errors;
	}
}

function applyHandler($handler, $data) {
	try { 
		$meta = new \ReflectionFunction($handler);
		$args = array();
		foreach($meta->getParameters() as $param) {
			if(isset($data[$param->getName()])) {
				$args[] = __boolCheck($data[$param->getName()]);
			} else if($param->isOptional()) {
				$args[] = $param->getDefaultValue();
			} else {
				throw new \Exception("Missing argument " . $param->getName());
			}
		}
		return call_user_func_array($handler, $args);
	} catch (\Exception $e) {
		Errors::add($e->getMessage());
	}
}

function HandlePost($postName, $handler) {
	if(isset($_POST[$postName])) {
		$result = applyHandler($handler, $_POST);
		if(Errors::doExist()) {
			return Errors::getAll();
		}
		
		return $result;
	}
}

function HandleGet($paramName, $handlers) {
	if(isset($_GET[$paramName])) {
		if(isset($handlers[$_GET[$paramName]])) {
			return applyHandler($handlers[$_GET[$paramName]], $_GET);
		}
	}
}

function HandlePostJSON($paramName, $handler) {
	if(isset($_POST[$paramName])) {
		die(json_encode(HandlePost($paramName, $handler)));
	}
}

function HandleGetJSON($paramName, $handlers) {
	if(isset($_GET[$paramName])) {
		die(json_encode(HandleGet($paramName, $handlers)));
	}
}

function __boolCheck($val) {
	return $val == 'false' ? false : ($val == 'true' ? true : $val);
}

function PostVar($key, $val = false) {
	return __boolCheck(isset($_POST[$key]) ? $_POST[$key] : $val);
}

function GetVar($key, $val = false) {
	return __boolCheck(isset($_GET[$key]) ? $_GET[$key] : $val);
}