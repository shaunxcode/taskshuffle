<?php
	$file = isset($_GET['file']) ? ($_GET['file'][0] == '/' ? substr($_GET['file'], 1) : $_GET['file']): false;
	if(!$file) {
		$file = 'Project Name';
		header('location:/' . uniqid());
		die();
	}
	
	//oauth secret
	//MlHWRMVEi9FjdH8ADzCy01sm
	

?>
<!DOCTYPE html>
<html>
<head>
	<title>TaskShuffle</title>
	<link rel="shortcut icon" href="favicon.ico"/>
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/smoothness/jquery-ui.css" type="text/css" media="all" />
	<link rel="stylesheet" href="style/blueprint/screen.css" type="text/css" media="all" />
	<link rel="stylesheet" href="style/taskshuffle.css" type="text/css" media="all" />

	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js"></script>
	<script type="text/javascript" src="jquery.scrollTo-min.js"></script>
	<script type="text/javascript" src="js/taskshuffle.js"></script>
	<script type="text/javascript">
		$(function(){
			TS.name = <?php echo json_encode($file); ?>;
			$('.ShuffleName').val(TS.name);
		});
	</script>
	<link rel="apple-touch-icon" href="ts_icon.png" />
</head>
<body>
	<div class="container">
		<div class="Header">
			<img src="images/title_2.png">
			<div class="UserMenu"><strong>Username</strong> | Settings | Logout</div>
			<div class="clear"></div>
		</div>

			<input class="NewTask Rounded"><img src="images/plus_2.png" id="newTaskPlus" />

		<div class="TaskListButtons">
			<div id="clearFinished">
				<img src="images/clear_finished.png" />
				Clear Finished
			</div>
			<div id="clearAll">
				<img src="images/clear_all.png" />
				Clear All
			</div>
		</div>
		<div class="Tasks Rounded">
			<ul class="TaskList ActiveTasks"></ul>
			<ul class="TaskList CompletedTasks">
		</div>
		<img src="images/745kman.png" id="man" />
	</div>
</body>
</html>
