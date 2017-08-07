<?php
class Filter {
	private $tasks;
	private $query;
	private $cached;
	
	private $twauthors;
	private $twtasks;
	private $grand_total;
	
	//public function __get($name) 
  	//{
	//	switch($name)
	//	{
	//		case 'tasks':
	//			return $this->tasks;
	//		case 'query':
	//			return $this->query;
	//		default:
	//			trace("error","cannot access property ".$name);
	//		
	//	}
	//}
	
	function GetData()
	{
		$ref =& $this->tasks;
		return ($ref);
	}
	function IsKeyPresent($key)
	{
		if(isset($this->tasks->$key))
			return true;
		return false;
	}
	
	function __get($name)
	{
		switch($name)
		{
			case 'IsCached':
				return $this->cached=1;
			default:
				echo "Filter does not support ".$name." property".EOL;
				exit;
		}
	}
	function GetField($field,$key)
	{
		$task = $this->tasks->$key;
		
		switch($field)
		{
			
			case 'key':
				return $task->key;
				break;
			case 'summary':
				return $task->summary;
				break;
			case 'start':
				return $task->start;
				break;
			case 'end':
				return $task->end;
				break;
			case 'duration':
				return $task->timeoriginalestimate;
				/*$days = $task->timeoriginalestimate/(60*60*8);
				if($days < 1)
					$days = 1;
				return $days;*/
				break;
			case 'resource':
				return $task->assignee;
				break;
			case 'status':
				return $task->status;
				break;
			case 'timespent':
				return $task->timespent;
				break;
			default:
				echo 'cant get '.$field.EOL;
				exit();
		}
	}
	function __construct($name,$query,$jiraurl,$rebuild=0,$cached=0)
	{
		Jirarest::SetUrl($jiraurl);
		$fields = 'key,status,summary,start,end,timeoriginalestimate,timespent,labels,assignee,created,issuetype,issuelinks';
		$this->query = $query;
		if (file_exists($name) && $rebuild==0) 
		{
			//echo "Updating\n";
			$this->cached=0;
			$last_update_date = date ("Y/m/d H:i" , filemtime($name));
			$data = file_get_contents($name);
			$this->tasks = json_decode( $data );
			if($cached==1)
			{
				$this->cached=1;
				return;
			}
			//$jtasks = Jira::Search("key=".$this->key,1,"key,status,timeoriginalestimate,timespent,progress,".JIRA_SCHEDULED_START.",".JIRA_SCHEDULED_END.",".JIRA_AGG_TIME_ORIG_ESTIMATE.",summary,fixVersion,labels,aggregateprogress,labels,assignee");
			$tasks  = Jirarest::Search($query." and updated>'".$last_update_date."'",1000,$fields);
			if($tasks == null)
			{
				$this->cached=1;
				return ;
			}
			for($i=0;$i<count($tasks);$i++)
			{
				$worklogs = Jirarest::GetWorkLog($tasks[$i]['key']);
				$tasks[$i]['worklogs'] =  $worklogs;
				$this->tasks->$tasks[$i]['key'] = $tasks[$i];
				
			}
		}
		else
		{
			echo "Rebuilding".EOL;
			$tasks = Jirarest::Search($query,1000,$fields);
			if($tasks == null)
			{
				$this->cached=1;
				return;
			}
			
			for($i=0;$i<count($tasks);$i++)
			{
				$worklogs = Jirarest::GetWorkLog($tasks[$i]['key']);
				$tasks[$i]['worklogs'] =  $worklogs;
				$this->tasks[$tasks[$i]['key']] = $tasks[$i];
			}
		}
		file_put_contents( $name, json_encode( $this->tasks ) );
		$data = file_get_contents($name);
		$this->tasks = json_decode( $data );
		return;
	}
	function BuildTimeSheet($date,$users=null)
	{
	 
		if($users !=  null)
			$users = explode(",",$users);

		$date = GetEndWeekDate($date);
		
		//$thisfriday = date('Y-M-d',strtotime('this friday', strtotime( $date)));
		$twtasks = array();
		$twauthors = array();
		
		// Identify users and this week tasks
		foreach($this->tasks as $key=>$task)
		{
			foreach($task->worklogs as $worklog)
			{
				if($users != null)
				{
					if (in_array($worklog->author, $users))
					{ }
					else
						continue;
				}
				$wdate = GetEndWeekDate($worklog->started);
				//$friday = date('Y-M-d',strtotime('this friday', strtotime( $worklog->started)));
				if(strtotime($date) == strtotime($wdate))
				{
					$twtasks[$task->key] = $task;
					$twauthors[$worklog->author] = 0.0;
				}
			}
		}
		
		// Assign all users to each task 
		foreach($twtasks as $key=>$twtask)
			$twtask->authors=$twauthors;
		
		$grand_total = 0.0;
		foreach($twtasks as $key=>$twtask)
		{
			$total=0.0;
			//echo $twtask->key." ".$twtask->summary."\n";
			foreach($twtask->worklogs as $worklog)
			{
				//$friday = date('Y-M-d',strtotime('this friday', strtotime( $worklog->started)));
				$wdate = GetEndWeekDate($worklog->started);
				$worklog->thisweek=0;
				if(strtotime($date) == strtotime($wdate))
				{
					$worklog->thisweek=1;
					//echo $worklog->author." ".$worklog->timespent."\n";
					if( isset($twtask->authors[$worklog->author]))
					{
						$twtask->authors[$worklog->author] += (float)$worklog->timespent;
						$total += (float)$worklog->timespent;
					}
				}
			}
			$twtask->total = $total;
			$this->grand_total += $total;
		}
		
		foreach($twtasks as $key=>$twtask)
		{
			foreach($twtask->authors as $author=>$worklog)
			{
				$twauthors[$author] += $worklog;
			}
		}
		$this->twauthors = $twauthors;
		$this->twtasks = $twtasks;
	}
	function GetTimeSheet($date,$users=null)
	{
		$this->BuildTimeSheet($date,$users);
		$grand_total = $this->grand_total;

		// Fill data in return format
		$rows = array();
		$row = array();
		foreach($this->twauthors as $author=>$worklog)
		{
			$row[] = $author;
		}
		$row[] = "Total";
		$rows['header'] = $row;
		
		$row = array();
		foreach($this->twauthors as $author=>$worklog)
		{
			$row[] = $worklog;
		}
		$row[] = $grand_total;
		$rows['footer'] = $row;
		
		$row = array();
		$i=0;
		foreach($this->twtasks as $key=>$twtask)
		{
			$row = array();

			$row[]= '<a href="'.JIRA_URL.'/browse/'.$twtask->key.'">'. $twtask->summary.'</a>';
			foreach($twtask->authors as $author=>$worklog)
			{
				$row[] = $worklog;
				//$twauthors[$author] += 	$worklog;
			}
			$row[] = $twtask->total;
			$rows[] = $row;
			$i++;
		}
		return $rows;
	}
	function sort($twtask1, $twtask2) 
	{
		/*echo $twtask1->summary.EOL;
		foreach($twtask1->worklogs as $worklog)
		{
			echo $worklog->started." ".$worklog->time.EOL;
		}
		echo $twtask2->summary.EOL;
		foreach($twtask2->worklogs as $worklog)
		{
			echo $worklog->started." ".$worklog->time.EOL;
		}*/
		$date1 = $twtask1->worklogs[count($twtask1->worklogs)-1]->started;
		$time1 = $twtask1->worklogs[count($twtask1->worklogs)-1]->time;
		$date2 =  $twtask2->worklogs[count($twtask2->worklogs)-1]->started;
		$time2 =  $twtask2->worklogs[count($twtask2->worklogs)-1]->time;
		

		if( $date1 < $date2)
		{
			//echo "Task1 < Task2".EOL;
			return 1;
		}
		else if( $date1 > $date2)
		{
			//echo "Task1 > Task2".EOL;
			return -1;
		}
		else
		{
			if($time1 < $time2)
			{
				//echo "T::Task1 < Task2".EOL;
				return 1;
			}
			else
			{
				//echo "T::Task1 >= Task2".EOL;
				return -1;
			}
		}
		
		//echo EOL;
		//foreach($twtask1 as $twtask)
		//	$dateTimestamp1 = strtotime($a);
		//	$dateTimestamp2 = strtotime($b);
		//	return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
		return 1;
	}
	
	function GetWeeklyReport($date,$users=null)
	{
		$tasks = array();
		$this->BuildTimeSheet($date,$users);
		foreach($this->twtasks as $twtask)
		{
			$ignore = false;
			foreach($twtask->labels as $label)
			{
				if($label == "noweeklyreport")
				{
					$ignore = true;
				}
			}
			
			if(!$ignore)
				$tasks[] = $twtask;
		}
		usort($tasks,array($this,'sort'));
		return $tasks;
	}
}
?>