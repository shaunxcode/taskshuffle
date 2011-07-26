<?php
	$file = isset($_GET['file']) ? substr($_GET['file'], 1) : false;
	if(!$file) {
		$file = 'Project Name';
	//	header('location:index.php?file=/Project Name');// . uniqid());
//		die();
	}

?>
<html>
<head>
	<title>TaskShuffle</title>
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/smoothness/jquery-ui.css" type="text/css" media="all" />
	<link rel="stylesheet" href="style/blueprint/screen.css" type="text/css" media="all" />
	<link rel="stylesheet" href="style/taskshuffle.css" type="text/css" media="all" />

	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-ui.min.js"></script>
	<script type="text/javascript" src="jquery.scrollTo-min.js"></script>
	<script type="text/javascript" src="js/taskshuffle.js"></script>
	<script type="text/javascript">
		$(function(){
			PS.name = <?php echo json_encode($file); ?>;
		})
	</script>
	<style>
	    .NewTask { width: 100%; font-family: inherit; font-size: 3em;}
		.TaskList { font-size: 3em; }
	</style>
</head>
<body>
	<div class="container">
		<input class="NewTask">
		<ul class="TaskList">
	  		<li>Task</li>
		</ul>
	</div>
</body>
</html>