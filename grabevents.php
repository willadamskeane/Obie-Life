<?php
	
	ini_set ("display_errors", "1"); 
	error_reporting(E_ALL); 
	
	include_once('includes/main.php');
	
	echo 'hey';

	$g=new Grabber('http://new.oberlin.edu/calendar/calendar_rss.dot');

	$db=new Database(DB_SERVER,DB_NAME,DB_USERNAME,DB_PASSWORD);
	$g->getNewEvents($db);
  
	echo 'done!';
?>