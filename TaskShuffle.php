<?php

namespace TaskShuffle;

const SALT = 'TaskShizzufIzzle';

require_once 'persysiphus.php';
use \Persysiphus\Collection;
\Persysiphus\dataDir(dirname(__FILE__) . '/data/');
date_default_timezone_set('America/Denver'); 

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
				'score' => 0,
				'lists' => array()));
		}
	}

	public static function saveUser($data) {
		if(!is_object($data)) {
			$data = (object)$data;
		}
		
		if(Session::uid() == $data->id || Session::isAdmin()) {
			Collection::User()->update($data);
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
	
	public static function verifyItem($item) {
		static $options = array(
			'id', 
			'user', 
			'task', 
			'complete',
		 	'completionDate', 
			'deleted', 
			'creationDate', 
			'deletionDate', 
			'orderBy');
		
		foreach(array_keys($item) as $key) {
			if(!in_array($key, $options)) {
				die("$key is invalid");
			}
		}
		
		foreach($options as $key) {
			if(!isset($item[$key])) {
				$item[$key] = null;
			}
		}
		return (object)$item;
	}
	
	public static function saveListItem($uqn, $item) {
		$user = self::getUser(Session::uid());
		
		if($item = self::verifyItem($item) && ($list = self::getListByUQN($uqn))) {
			if(!empty($item->id)) {
				if($item->deleted) {
					$list->itemsTotal--;
				}
				
				if($item->complete) {
					$item->completionDate = date('m/d/Y H:i:s');
					$list->itemsComplete++;
					$user->score++;
					self::saveUser($user);
				}
				
				$list->update($item);
			}
			else {				
				$list->create($item);
				$list->itemsTotal++;
			}
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
	
	public static function clearAllListItems($uqn) {
		self::getListByUQN($uqn)->filterThenSave(function($item) {
			return false;
		});
	}
	
	public static function clearAllFinishedListItems($uqn) {
		self::getListByUQN($uqn)->filterThenSave(function($item) {
			return !$item->complete;
		});
	}
	
	public static function changeListItemOrder($uqn, $after, $item) {
		$item = TaskShuffle::verifyItem($item);
		self::getListByUQN($uqn)->changeOrder($after, $item);
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

class NonMatch {}
function applyHandler($handler, $data) {
	$meta = new \ReflectionFunction($handler);
	$args = array();
	foreach($meta->getParameters() as $param) {
		if(isset($data[$param->getName()])) {
			$args[] = __boolCheck($data[$param->getName()]);
		} else if($param->isOptional()) {
			$args[] = $param->getDefaultValue();
		} else {
			return new NonMatch;
		}
	}
	return call_user_func_array($handler, $args);
}

function HandlePost($postName, $handler) {
	if(isset($_POST[$postName])) {
		$result = applyHandler($handler, $_POST);		
		return $result;
	}
}

function HandleGet($paramName, $handlers) {
	if(isset($_GET[$paramName])) {
		if(is_callable($handlers)) {
			return applyHandler($handlers, $_GET);
		}
		if(isset($handlers[$_GET[$paramName]])) {
			return applyHandler($handlers[$_GET[$paramName]], $_GET);
		}
	}
}

function HandlePostJSON($paramName, $handler) {
	if(isset($_POST[$paramName])) {
		$result = HandlePost($paramName, $handler);
		if(!$result instanceof NonMatch) {
			die(json_encode($result));
		}
	}
}

function HandleGetJSON($paramName, $handlers) {
	if(isset($_GET[$paramName])) {
		$result = HandleGet($paramName, $handlers);
		if(!$result instanceof NonMatch) {
			die(json_encode($result));
		}
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