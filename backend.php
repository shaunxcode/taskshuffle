<?php
namespace TaskShuffle;
session_start();
require_once 'TaskShuffle.php';

function verifyItem($item) {
	foreach(array_keys($item) as $key) {
		if(!in_array($key, array('id', 'user', 'task', 'complete', 'completionDate', 'deleted', 'creationDate', 'deletionDate', 'orderBy'))) {
			die("$key is invalid");
		}
	}

	return $item;
}

function getMessages($filename) {
	return json_decode('[' . substr(file_get_contents($filename), 0, -2) .']');
}

HandleGetJSON('method', array(
	'emailDistinct' => function($email) {
		return TaskShuffle::emailDistinct($email);
	},
	'lists' => function() {
		if($user = TaskShuffle::getUser(Session::uid())) {
			return array(
				'lists' => array_map(
					function($uqn) {
						return TaskShuffle::getListByUQN($uqn)
							->getInfo()
							->set('uqn', $uqn)
							->toArray();
					}, 
					array_map(
						function($list) use($user) {
							return $user->email . '.' . $list;
						}, 
						$user->lists)));
		}
	}
));

HandlePostJSON('list', function($list) {
	return TaskShuffle::createUserList(Session::uid(), $list['name']);
});

if(!isset($_GET['file'])) {
	die('MUST SPECIFY FILE');
}

$filename  = dirname(__FILE__) . '/data/' . $_GET['file'];

if(!file_exists($filename)) {
	touch($filename);
}

if(isset($_POST['after'])) {
	$order = 0;
	$item = verifyItem($_POST['item']);

	$file = '';	
	if($_POST['after'] == 'false') {
		$item['orderBy'] = $order++;
		$file .= json_encode($item) . ",\n";	
	}
	
	foreach(getMessages($filename) as $message) {
		if($message->id == $item['id']) {
			continue;
		}
		
		$message->orderBy = $order++;
		$file .= json_encode($message) . ",\n";
		
		if($message->id == $_POST['after']) {
			$item['orderBy'] = $order++;
			$file .= json_encode($item) . ",\n";
		}
	}
	file_put_contents($filename, $file);
}
else if(isset($_POST['clearAll'])) {
	foreach(getMessages($filename) as $message) {
		$message->deletionDate = date('m/d/y H:i:s');
		file_put_contents($filename . '.deleted', json_encode($message) . ",\n", FILE_APPEND);
	}
	file_put_contents($filename, '');
}
else if(isset($_POST['clearFinished'])) {
	$file = '';
	foreach(getMessages($filename) as $message) {
		if($message->complete != 'true') {
			$message->deletionDate = date('m/d/y H:i:s');
			file_put_contents($filename . '.deleted', json_encode($message) . ",\n", FILE_APPEND);
			$file .= json_encode($message) . ",\n";
		}
	}
	file_put_contents($filename, $file);
}
else if(isset($_POST['item'])) {
	$item = verifyItem($_POST['item']);
	
	if(!empty($item['id'])) {
		//update
		$file = '';
		foreach(getMessages($filename) as $message) {
			if($message->id == $item['id']) {
				if($item['deleted'] == 'true') {
					$item['deletionDate'] = date('m/d/y H:i:s');
					file_put_contents($filename . '.deleted', json_encode($item) . ",\n", FILE_APPEND);
					continue;
				}
				
				if($item['complete'] == 'true') {
					$item['completionDate'] = date('m/d/Y H:i:s');
				}
				$message = $item;
			}
			$file .= json_encode($message) . ",\n";
		}
		
		file_put_contents($filename, $file);
	} 
	else {
		$item['id'] = uniqid();
		$item['creationDate'] = date('m/d/Y H:i:s');
		file_put_contents($filename, json_encode($item) . ",\n", FILE_APPEND);
	}
} 
else {
	echo json_encode(array(
		'messages' => getMessages($filename), 
		'timestamp' => filemtime($filename)));
}