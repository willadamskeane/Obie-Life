<?php

ini_set ("display_errors", "1"); 
error_reporting(E_ALL); 

function renderDate($timestamp)
{
	$time=time();
	$realWeekDay=date('N');
  	$eventWeekDay=date('N',$timestamp);
	if ($timestamp-$time<DAY && $realWeekDay==$eventWeekDay)
	{
		return "Today";
	}
	if ($timestamp-$time<=DAY*2 && ($realWeekDay+1==$eventWeekDay || ($realWeekDay==7 && $eventWeekDay==1)))
	{
		return "Tomorrow";
	}
	if ($timestamp-$time<(7-$realWeekDay)*DAY+DAY*7)
	{
		return date('l',$timestamp);
	}
	return date('M j',$timestamp);
}

class Database
{
	private $server;
	private $name;
	private $username;
	private $password;
	
	function __construct($server,$name,$username,$password)
	{
		$this->server=$server;
		$this->name=$name;
		$this->username=$username;
		$this->password=$password;
		mysql_connect($server,$username,$password);
		mysql_select_db($name);
	}
	
	public function query($query)
	{
		return mysql_query($query);
	}
	
	public function fetchAssoc($result)
	{
		return mysql_fetch_assoc($result);
	}
	
	public function numRows($result)
	{
		return mysql_num_rows($result);
	}
	
	public function getLocation($lid)
	{
		$result=$this->fetchAssoc($this->query('SELECT name FROM locations WHERE lid="'.$lid.'"'));
		return $result['name'];
	}
	
	public function insertEvent(Event $event)
	{
		$this->query('INSERT INTO events (title,description,startTime,category,likes) VALUES ("'.$event->getTitle().'","'.$event->getDescription().'","'.$event->getStartTime().'","'.rand(1,3).'","'.rand(1,30).'")');
	}
	
}

class Event
{
	private $data;
	
	function __construct($data)
	{
		$this->data=$data;
	}
	
	public function getValue($name)
	{
		return $this->data[$name];
	}
	
	public function getTitle()
	{
		return $this->getValue('title');
	}
	
	public function getLocation()
	{
		return $this->getValue('location');
	}
	
	public function getStartTime(){
		return $this->getValue('startTime');
	}
	
	public function getEndTime(){
		return $this->getValue('endTime');
	}
		
	public function getEID(){
		return $this->getValue('eid');
	}
	public function getNumComments(){
		return $this->getValue('numcomments');
	}
	public function getDescription(){
		return $this->getValue('description');
	}
	
	public function getCategory()
	{
		return $this->getValue('category');
	}
	
	public function getLikes()
	{
		return $this->getValue('likes');
	}
}

class Template
{
	private $css;
	private $db;
	
	function __construct($db,$css="default.css")
	{
		$this->css=$css;
		$this->db=$db;
	}
	
	public function renderHeader()
	{
		echo '<head>';
		echo '<link rel="stylesheet" type="text/css" href="css/'.$this->css.'" />';
		echo '<link type="text/css" href="css/Aristo/jquery-ui-1.8.7.custom.css" rel="stylesheet">';
		echo '<link rel="stylesheet" href="css/uniform.aristo.css" type="text/css" media="screen" charset="utf-8" />';
		echo '<script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>';
		echo '<script type="text/javascript" src="js/jquery-ui-1.8.7.custom.min.js"></script>';
		echo '<script type="text/javascript" src="js/jquery.uniform.min.js"></script>';
		echo '<script type="text/javascript" src="js/main.js"></script>';
		
		echo '</head>';
	}
	
	public function renderEvent(Event $event,$like=false)
	{
		echo '<div id="event_'.$event->getEID().'" class="event">';
		echo '<div class="eventInfoContainer">';
		echo '<a href="#"><div name="'.$event->getCategory().'" class="tab ui-corner-left left ';
		if ($like)
		{
			echo 'liked tabcat_'.$event->getCategory().'" style="color:#ffffff">';
		}else{
			echo 'maincat_'.$event->getCategory().'">';
		}
		echo '<div style="height:10px"></div>';
		echo $event->getLikes();
		echo '</div></a>';
		echo '<a href="#"><div title="close" class="ui-corner-right right maincat_'.$event->getCategory().'">';
		echo '<div style="float:right">';
		$startDay=renderDate($event->getStartTime());
		$startTime=date('g:i A',$event->getStartTime());
		echo '<div style="margin-right:5px;float:left;height:100%;vertical-align:middle"><div class="ui-corner-all time">'.$startDay.'</div></div><div class="ui-corner-all time" style="float:right;font-weight:bold">'.$startTime.'</div>';
		// echo '<div class="ui-corner-all" style="padding:5px;background-color:#ffffff;height:18px;font-size:18px;font-weight:bold;vertical-align:middle">7 PM</div>';
		echo '</div>';
		echo '<b style="font-size:14px">'.$event->getTitle().'</b>';
		echo '<br />';
		echo $this->db->getLocation($event->getLocation());
		echo '<br />';
		echo '<div class="eventDescription">';
		echo $event->getDescription();
		echo '</div>';
		echo '</div></a>';
		echo '</div>';
		$this->renderCommentSection();
		echo '</div>';
		
	}
	
	public function renderComments($eID,$limit)
	{
		$queryResult=$this->db->query('SELECT * FROM comments WHERE eid='.$eID.' ORDER BY cnum DESC LIMIT '.$limit);
		if ($this->db->numRows($queryResult)==0){
			echo 'No comments yet. Be the first to comment!';
		}else{
			while($commentRow = $this->db->fetchAssoc($queryResult)){ 
	  			echo '<div><div class="commentbubble" id="'.$commentRow['cid'].'" style="word-wrap:break-word;"><img src="images/testavatar.jpg" class="ui-corner-all" style="float:left" width="35" height="35" /><blockquote class="ui-corner-all"><img src="css/images/pointer.gif" class="commentPointer" /><p>'.$commentRow['text'].'</p></blockquote></div>';
	  			// echo '<cite><a href="index.php?pid=67729#comment_4" rel="nofollow" class="calt"><b>#'.$commentRow['cnum'].'</b></a> on January 20, 2011 at 4:07 pm</cite>';
	  			echo '</div>';
	  		}
	  	}
	}
	
	public function postComment($eID,$text)
	{
		$cNum=$this->db->query('SELECT cnum FROM comments WHERE eid='.$eID.' ORDER BY cnum DESC');
		$cNum=$this->db->fetchAssoc($cNum);
		$cNum=$cNum['cnum'];
		$cNum++;
		$this->db->query('INSERT INTO comments (eid,cnum,text) VALUES ("'.$eID.'","'.$cNum.'","'.$text.'")');
	}
	
	public function renderCommentSection()
	{
		echo '<div class="comments" style="display:none">';
		echo '<div class="commentBoxContainerUnselected"><textarea class="commentBox" rows="1" name="commentBox" style="width:100%"></textarea>
		<br />
		<button class="postComment" style="margin-top:3px;display:none;width:100%">Post Comment</button>
		</div>';
		echo '<div class="commentsContainer">';
		echo 'Loading comments...';
		echo '</div></div>';
	}
	
	public function renderNoEventsFound()
	{
		echo 'No events found';
	}
	
	public function renderCategories()
	{
		$categories=$this->db->query("SELECT * FROM categories");
		echo '<div style="font-weight:bold;border-color:transparent;" class="category ui-corner-all maincat_0"><label><input id="cat_0" checked="true" type="checkbox" />All</label></div><div class="categoryLabel"> - or - </div>';
		while ($category=$this->db->fetchAssoc($categories))
		{
			echo '<div style="font-weight:normal;border-color:transparent;" class="category ui-corner-all maincat_'.$category["id"].'"><label><input id="cat_'.$category["id"].'" type="checkbox" />'.$category["name"].'</label></div>';
		}
	}
	
	public function renderTimeCategory($string)
	{
		// echo '</div>';
		echo '<div style="height:25px;margin-top:-14px;margin-left:-136px" class="timecat" id="'.str_replace(" ","",$string).'"><a href="#"><div style="float:left;width:120px"><div class="ui-corner-left" style="float:right;border:2px solid;border-right-width:0px;padding:5px;padding-left:8px;text-align:right;font-size:14px"><b>'.$string.'</b></div><div style="margin-left:120px;border-width:15px;border-style:solid;border-color: transparent transparent transparent #666666;"></div><div style="margin-top:-30px;margin-left:117px;border-width:15px;border-style:solid;border-color: transparent transparent transparent #ffffff;"></div></div></a>';
		// echo '<hr />';
		echo '</div>';
		// echo '<div id="'.str_replace(" ","",$string).'_div">';
		
	}
	
	public function renderEvents($categories,$sorting,$options='',$user=false)
	{
		if ($user)
		{
			$likedEvents=$user->getLikes();
		}
		$categories=explode(':',$categories);
		$time=time();
		$condition=' WHERE endTime>'.$time.'';
		$filter=false;
		foreach ($categories as $category)
		{
			if ($category>0){
				if ($filter){
					$condition.=' or ';
				}else{
					$filter=true;
					$condition.=' AND (';
				}
				$condition.='category = '.intval($category);
			}
		}
		if ($filter)
		{
			$condition.=') ';
		}
		$sortQuery=' ORDER BY startTime ASC';
		if ($sorting==1)
		{
			$sortQuery=' ORDER BY likes DESC';
		}
		$query='SELECT * FROM events'.$condition.$sortQuery.$options;
		echo '<div class="events">';
		$queryResult=$this->db->query($query);
		if (!$queryResult || $this->db->numRows($queryResult)==0){
			$this->renderNoEventsFound();
			return;
		}
		$now=false;$today=false;$tomorrow=false;$week=false;$weekend=false;$nextweek=false;$future=false;
		while($eventRow = $this->db->fetchAssoc($queryResult)){ 
  			$event=new Event($eventRow);
  			$realWeekDay=date('N');
  			$eventWeekDay=date('N',$event->getStartTime());
  			$timeUntilStarts=$event->getStartTime()-$time;
  			$timeUntilEnds=$event->getEndTime()-$time;
  				if ($event->getEndTime()==0)
  				{
  					$timeUntilEnds=0;
  				}
  			if (!$now && $timeUntilStarts<=0 && $timeUntilEnds>0)
  			{
  				$now=true;
  				$this->renderTimeCategory('Now');
  			}
  			if (!$today && $realWeekDay==$eventWeekDay and $timeUntilStarts<DAY && $timeUntilStarts>0)
  			{
  				$today=true;
  				if ($now)
  				{
  					$this->renderTimeCategory('Later Today');
  				}else{
  					$this->renderTimeCategory('Today');
  				}
  			}
  			if (!$tomorrow && ($realWeekDay==4 || $realWeekDay==6) && $timeUntilStarts<DAY*2 && $realWeekDay!=$eventWeekDay)
  			{
  				$tomorrow=true;
  				$this->renderTimeCategory('Tomorrow');
  			}
  			if (!$week && $realWeekDay!=$eventWeekDay && $timeUntilStarts<DAY*7 && ($realWeekDay<4 || $realWeekDay==7))
  			{
  				$week=true;
  				if ($tomorrow){
  					$this->renderTimeCategory('Later This Week');
  				}else{
  					$this->renderTimeCategory('This Week');
  				}
  			}
  			if (!$weekend && $eventWeekDay>5 && $timeUntilStarts<DAY*7 && $realWeekDay<6)
  			{
  				$weekend=true;
  				$this->renderTimeCategory('This Weekend');
  			}
  			if (!$nextweek && $eventWeekDay!=7 && (($realWeekDay!=7 && $timeUntilStarts>(7-$realWeekDay)*DAY) || ($realWeekDay==7 && $timeUntilStarts>7*DAY))  )
  			{
   				$nextweek=true;
  				$this->renderTimeCategory('Next Week');				
  			}
  			if (!$future && (($realWeekDay!=7 && $timeUntilStarts>(7-$realWeekDay)*DAY+DAY*7) || ($realWeekDay==7 && $timeUntilStarts>(7-$realWeekDay)*DAY+(DAY*14))))
  			{
  				$future=true;
  				$this->renderTimeCategory('Future');				
  			}
  			
  			$this->renderEvent($event,isset($likedEvents[$event->getEID()]));
  		}
  		echo '</div>';
	}
}

class Grabber
{
	
	private $feed;
	
	function __construct($feed)
	{
		$this->feed = $feed;
	}
	
	public function getNewEvents(Database $db)
	{
		$doc = new DOMDocument();
  		$doc->load($this->feed);
		foreach ($doc->getElementsByTagName('item') as $node) {
			$itemRSS = array ( 
				'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
				'description' => $node->getElementsByTagName('description')->item(0)->nodeValue,
				'startTime' => strtotime($node->getElementsByTagName('pubDate')->item(0)->nodeValue)
				);
			$event = new Event($itemRSS);
			$db->insertEvent($event);
		}
	}
	
}

class User
{
	private $uid;
	private $firstName;
	private $lastName;
	private $password;
	private $db;
	
	function __construct(Database $db,$uid)
	{
		$this->db=$db;
		$this->uid=$uid;
	}
	
	public function getLikes()
	{
		$result=$this->db->query('SELECT eid FROM likes WHERE uid="'.$this->uid.'"');
		$likes=array();
		while ($like=$this->db->fetchAssoc($result))
		{
			$likes[$like['eid']]=true;
		}
		return $likes;
	}
	
	public function likeEvent($eid)
	{
		$this->db->query('INSERT INTO likes (uid,eid) VALUES ("'.$this->uid.'","'.$eid.'")');
		$this->db->query('UPDATE events SET likes=likes+1 WHERE eid="'.$eid.'"');
		$numLikes=$this->db->query('SELECT likes FROM events WHERE eid="'.$eid.'"');
		$numLikes=$this->db->fetchAssoc($numLikes);
		return $numLikes['likes'];
	}
	
}

?>