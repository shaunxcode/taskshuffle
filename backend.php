<?php
namespace TaskShuffle;
session_start();
require_once 'TaskShuffle.php';

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

HandlePostJSON('private', function($uqn, $private) {
	return TaskShuffle::setListPrivacy($uqn, $private);
});

HandlePostJSON('readOnly', function($uqn, $readOnly) {
	return TaskShuffle::setListReadOnly($uqn, $readOnly);
});

HandlePostJSON('list', function($list) {
	return TaskShuffle::createUserList(Session::uid(), $list['name']);
});

HandlePostJSON('after', function($uqn, $after, $item) {
	return TaskShuffle::changeListItemOrder($uqn, $after, $item);
});

HandlePostJSON('clearAll', function($uqn) {
	return TaskShuffle::clearAllListItems($uqn);
});

HandlePostJSON('clearFinished', function($uqn) {
	return TaskShuffle::clearAllFinishedListItems($uqn);
});

HandlePostJSON('create', function($uqn, $item) {
	return TaskShuffle::saveListItem($uqn, $item);
});

HandlePostJSON('update', function($uqn, $item) {
	return TaskShuffle::saveListItem($uqn, $item);
});

HandlePostJSON('delete', function($uqn, $item) {
	return TaskShuffle::deleteListItem($uqn, $item);
});
 
HandleGetJSON('uqn', function($uqn) { 
	$list = TaskShuffle::getListByUQN($uqn);
	return array(
		'messages' => $list->getAll(),
		'timestamp' => $list->now());
});