<?php

namespace TaskShuffle;

function verifyItem($item) {
	foreach(array_keys($item) as $key) {
		if(!in_array($key, array('id', 'user', 'task', 'complete'))) {
			return false;
		}
	}

	return true;
}

function getMessages($filename) {
	return json_decode('[' . substr(file_get_contents($filename), 0, -2) .']');
}

if(!isset($_GET['file'])) {
	die('MUST SPECIFY FILE');
}

$filename  = dirname(__FILE__) . '/data/' . $_GET['file'] . '.txt';

if(!file_exists($filename)) {
	touch($filename);
}

if(isset($_POST['item'])) {
	$item = $_POST['item'];
	if(!verifyItem($item)) {
		die('INVALID');
	}
	
	if(!empty($item['id'])) {
		//update
		$file = '';
		foreach(getMessages($filename) as $message) {
			if($message->id == $item['id']) {
				$message = $item;
			}
			$file .= json_encode($message) . ",\n";
		}
		
		file_put_contents($filename, $file);
	} 
	else {
		$item['id'] = uniqid();
		file_put_contents($filename, json_encode($item) . ",\n", FILE_APPEND);
	}
} 
else {
	echo json_encode(array(
		'messages' => getMessages($filename), 
		'timestamp' => filemtime($filename)));
}