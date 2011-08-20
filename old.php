<?php

namespace TaskShuffle;
session_start();
require_once 'TaskShuffle.php';

//oauth secret
//MlHWRMVEi9FjdH8ADzCy01sm
	
HandleGet('action', array(
	'logout' => function() {
		session_destroy();
		header("location:/");
	},
	
	'settings' => function() {
		
	}
));

HandlePost('Register', function($registerPassword1, $registerPassword2, $registerEmail, $registerListName) {	
	if($registerPassword1 != $registerPassword2 || empty($registerPassword1)) {
		Errors::add('Passwords must match');
	} 
	else if(empty($registerListName)) {
		Errors::add('Must provide name for your first list');
	}
	else {
		if($user = TaskShuffle::register($registerEmail, $registerPassword1)) {
			$list = TaskShuffle::createUserList($user->id, $registerListName);
			Session::uid($user->id);
			header("location:/" . $list->getName());
		}
	}
});

HandlePost('Login', function($loginEmail, $loginPassword) {
	if($user = TaskShuffle::authenticate($loginEmail, $loginPassword)) {
		Session::uid($user->id);
		header("location:/");
	}
});


?>
<!DOCTYPE html>
<html>
<head>
	<title>TaskShuffle</title>
	<link rel="shortcut icon" href="/favicon.ico"/>
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/smoothness/jquery-ui.css" type="text/css" media="all" />
	<link rel="stylesheet" href="style/blueprint/screen.css" type="text/css" media="all" />
	<link rel="stylesheet" href="style/taskshuffle.css" type="text/css" media="all" />
    <link rel="stylesheet" href="chosen/chosen.css" />
	<link rel="apple-touch-icon" href="ts_icon.png" />	
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js"></script>
	<script type="text/javascript" src="chosen/chosen.jquery.js"></script>
	<script type="text/javascript" src="js/taskshuffle.js"></script>
</head>
<body>
<?php if(Session::uid()) { 
	$user = TaskShuffle::getUser(Session::uid());
	$userMenu = '<div class="UserMenu"><strong>' . $user->email .'</strong> | <a href="/">Settings</a> | <a href="index.php?action=logout">Logout</a></div>';

	if($list = GetVar('list')) {
		$list = $list[0] == '/' ? substr($list, 1) : $list;
		if(!TaskShuffle::getListByUQN($list)) {
			die("List does not exist. Create it? ");
		}
		
?>
	<script type="text/javascript">
		$(function(){
			TS.name = <?php echo json_encode($list); ?>;
			$('.ListName').text(TS.name.split('.').pop());
			TS.connect();
		});
	</script>
	<div class="container">
		<div class="TopPanel span-24">
			<div class="LeftColumn span-18 first">
				<h1 class="ListName">Name of List</h1>
				<div class="TaskListButtons">
					<div class="ToggleGroup" id="private">
						<span class="ToggleLabel">Private</span>
						<span class="Toggle ToggleOn" id="privateToggle"></span>
					</div>
					<div class="ToggleGroup" id="readOnly">
						<span class="ToggleLabel">Read Only</span>
						<span class="Toggle ToggleOn" id="readOnlyToggle"></span>
					</div>			
					<div class="ToggleGroup" id="addToBottom">
						<span class="ToggleLabel">Add to Bottom</span>
						<span class="Toggle ToggleOff" id="addToBottomToggle"></span>
					</div>			
					<div id="share"></div>
					<div class="clear"></div>
				</div>
				<div class="clear"></div>
				<div class="NewItemControl">
					<input class="Rounded NewTask">
					<img src="images/plus_2.png" id="newTaskPlus" />
				</div>
			</div>
			
			<div class="RightColumn span-6 last">
				<img src="images/title_2.png">
				<?php echo $userMenu; ?>
				
				<div class="clear"></div>
				<div id="ListComboContainer"> 				
					<select id="listCombo"><option>Choose a list</option></select>
				</div>
				
				<div id="clearAll"></div>
				<div id="clearFinished"></div>
			</div>	
		</div>
		<div class="clear"></div>

		<div id="collapseHandle"><img src="images/collapse-handle.png" /></div>

		<div class="Tasks Rounded">
			<ul class="TaskList ActiveTasks"></ul>
			<ul class="TaskList CompletedTasks">
		</div>
		<img src="images/745kman.png" id="man" />
	</div>
<?php 
	} 
	else {?>
<div class="container" id="yourListsContainer">
	<div class="LeftColumn span-18 first">
		<h1 class="ListName">Your Account</h1>
	</div>
	<div class="RightColumn span-6 last">
		<img src="images/title_2.png">

		<?php echo $userMenu; ?>
		
		<div class="clear"></div>
		<div id="ListComboContainer"> 				
			<select id="listCombo"><option>Choose a list</option></select>
		</div>
	</div>
	<div class="span-24 first last">
		<input type="text" class="Rounded NewList">
		<img src="images/plus_2.png" id="newListPlus" />
	</div>
	<h2>Your Lists</h2>
	<script type="text/javascript" src="js/summary.js"></script>
	<div id="listsContainer"></div>
</div>

<?php
	
	}
} else  { 

?>
	<script type="text/javascript">
		$(function(){
			TS.createButton('registerButton');
			TS.createButton('loginButton');
			TS.createButton('goButton');
			TS.createToggle('loginRemember');
			
			var checkEmailDistinct = function() { 
				$.get('backend.php', {method: 'emailDistinct', email: $(this).val()}, function(result) {
					console.log(result);
				})
			};
			
			var checkPasswordMatch = function() {
				return $('#registerPassword1').val().length > 0 && 
					   $('registerPassword1').val() == $('#registerPassword2').val();
			};
			
			$('#registerEmail').blur(checkEmailDistinct);
			$('#registerPassword1, #registerPassword2').blur(checkPasswordMatch); 
			$('#registerForm').submit(function(){
				return true;
			});
		});
	</script>
	<div class="container" id="loginContainer">
		<div class="span-24 first last LoginHeader">
			<img src="images/splash_logo.png" />
		</div>
	<?php if($errors = Errors::getAll()) { ?>
		<div class="span-24 first last LoginErrors"><?php echo implode('<br />', $errors); ?></div>
	<?php } ?>
		<div class="LoginBox span-12 first">
			<h3>Create your free account</h3>
			<form method="POST" class="Rounded" id="registerForm">
				<label for="registerEmail">Your Email</label>
				<input type="text" id="registerEmail" value="<?php echo PostVar('registerEmail'); ?>"name="registerEmail" />
				<label for="registerPassword">Password</label>
				<input type="password" id="registerPassword1" value="<?php echo PostVar('registerPassword2'); ?>" name="registerPassword1" />
				<input type="password" id="registerPassword2" value="<?php echo PostVar('registerPassword2'); ?>" name="registerPassword2" />
				<input type="text" id="registerListName" value="<?php echo PostVar('registerListName'); ?>" name="registerListName" />
				<input type="submit" name="Register" value="Register" id="registerButton" />
			</form>
		</div>
		<div class="LoginBox span-12 last">
			<h3>Sign into your lists!</h3>
			<form method="POST" class="Rounded">
				<label for="loginEmail">Your Email</label>
				<input type="text" id="loginEmail" name="loginEmail" />
				<label for="loginPassword">Password</label>
				<input type="password" id="loginPassword" name="loginPassword"/>
				<input type="submit" name="Login" value="Login" id="loginButton" />
				<div id="loginRememberGroup">
					<div class="ToggleGroup" id="loginRemember">
						<span class="ToggleLabel">Stay signed in on this computer?</span>
						<span class="Toggle ToggleOn" id="loginRememberToggle"></span>
						<a class="LoginBoxLink" href="#">Forgot Password?</a>
					</div>
					<div class="clear"></div>
				</div>
				<div id="loginBoxBottom">You will be taken to your default list</div>
			</form>
		</div>
		<div class="span-24 first last" id="loginFooter">
			<h3>No time to Register?! Start a list right away!</h3>
			<div id="noTime">You must register an account to have more than one list</div>
			<form method="POST" id="noTimeForm">
				<input type="text" id="noTimeListName" />
				<input type="submit" value="Go!" id="goButton"/>
			</form>
		</div>
	</div>
<?php } ?>
</body>
</html>
