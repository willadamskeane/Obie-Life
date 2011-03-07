<?php

include_once('constants.php');
include_once('core.php');

$db=new Database(DB_SERVER,DB_NAME,DB_USERNAME,DB_PASSWORD);

$template=new Template($db);
$user = new User($db,1);

if (isset($_GET['getevents']))
{
	$categories=$_GET['categories'];
	$sorting=$_GET['sorting'];
	$template->renderEvents($categories,$sorting,'',$user);
}

if (isset($_GET['getcomments']))
{
	$template->renderComments($_GET['eid'],5);
}

if (isset($_GET['postcomment']))
{
	$template->postComment($_GET['eid'],$_POST['text']);
	ob_start();
	$template->rendercomments($_GET['eid'],5);
	$arr=array('eid'=>$_GET['eid'],'html'=>ob_get_contents());
	ob_end_clean();
	echo json_encode($arr);
	
}

if (isset($_GET['likeevent']))
{
	echo '<div style="height:10px"></div>';
	echo $user->likeEvent($_GET['eid']);
}

?>