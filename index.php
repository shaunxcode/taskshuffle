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
			TS.name = <?php echo json_encode($file); ?>;
		})
	</script>
	<style>
	    .NewTask { width: 100%; font-family: inherit; font-size: 3em;}
		.TaskList { 
			padding: 0;
			font-size: 3em; 
			list-style-type: none;
		}
		
		.TaskList button.ui-button { width: .8em; margin-right: .3em;}
		.TaskList button span.ui-button-icon-primary { left: 0.2em;}
		.TaskList li div { cursor: move; display: inline-block;}

		.TaskList .TaskComplete { 
			text-decoration: line-through;
			color: #ccc;
		}

	</style>
</head>
<body>
	<div class="container">
		<input class="NewTask">
		<ul class="TaskList"></ul>
	</div>
</body>
</html>