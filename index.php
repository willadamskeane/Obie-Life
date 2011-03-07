<?php 
	
	ini_set ("display_errors", "1"); 
	error_reporting(E_ALL); 
	include_once('includes/main.php');
	$event1=new Event(1,"This is a test event","Philips Gym","1/1/1", "12","Hello, this is a test");
	$db=new Database(DB_SERVER,DB_NAME,DB_USERNAME,DB_PASSWORD);
	$user = new User($db,1);
	$template = new Template($db);
	$template->renderHeader();
?>
<body>
	
	<div class="container">
		<div class="userbar">
			<button style="float:right" id="register">Register</button>
		</div>
		<button id="newEventButton">Add Event</button>
		<br /><br /><br /><br />

		<div id="tabs">
		<div style="padding:0px;margin-top:-10px;float:right" id="sorting"> 
			<input type="radio" id="hot" name="radio" /><label for="hot">Hot</label> 
			<input type="radio" id="comingup" name="radio" checked="checked" /><label for="comingup">Coming Up</label> 
		</div> 
	
			<ul>
				<li><a href="#tabs-1">College</a></li>
				<li><a href="#tabs-2">Burton</a></li>
				<li><a href="#tabs-3">Me</a></li>
			</ul>
			
			<div id="tabs-1">
				<div class="categories">
			<?php $template->renderCategories(); ?>
				</div>
				
				<?php 
					// $template->renderCategoryChooser($db); 
					$template->renderEvents(0,0,'',$user); 
				?>
			</div>
			<div id="tabs-2">
				<?php $template->renderEvents(0,0);  ?>
			</div>
			<div id="tabs-3">
				<?php $template->renderEvents(0,0); ?>
			</div>
		</div>
	</div>
</body>