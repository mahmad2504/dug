<?php
class Tj
{
	private $output;
	private $filename = null;
	function FlushProjectHeader($project)
	{
		$today = GetToday("Y-m-d");
		$end  =  $project->End;
		$start = $project->Start;
		if($end == null) // No end defined so schedule from start or from today
		{
			if(strtotime($start) < strtotime($today))
				$header =  'project acs "'.$project->Name.'" '.$today;
			else
				$header =  'project acs "'.$project->Name.'" '.$start;
		}
		else
		{
			if(strtotime($start) > strtotime($today))
			{
				$header =  'project acs "'.$project->Name.'" '.$start;
			}
			else
			{
				if(strtotime($end) > strtotime($today))
					$header =  'project acs "'.$project->Name.'" '.$today;
				else
					$header =  'project acs "'.$project->Name.'" '.$end;
			}
		}
		$header = $header." +12m"."\n";
		$header = $header.'{ '."\n";
		$header = $header.'   timezone "Asia/Karachi"'."\n";
		$header = $header.'   timeformat "%Y-%m-%d"'."\n";
		$header = $header.'   numberformat "-" "" "," "." 1 '."\n";
		$header = $header.'   currencyformat "(" ")" "," "." 0 '."\n";
		$header = $header.'   now 2017-07-21-01:00'."\n";
		$header = $header.'   currency "USD"'."\n";
		$header = $header.'   scenario plan "Plan" {}'."\n";
		$header = $header.'   extend task { text Jira "Jira"}'."\n";
		$header = $header.'} '."\n";
		return $header;
	}
	function FlushLeavesHeader($project)
	{
		$header = "";
		$calendar = $project->Calendar;
		foreach($calendar as $holiday)
			$header = $header.'leaves holiday "holiday "'.$holiday."\n";
		return $header;
	}
	function FlushResourceHeader($resources)
	{
		$header =  "macro allocate_developers ["."\n";
		foreach($resources as $resource)
			$header = $header."   allocate ".$resource->Username."\n";
 
		$header = $header."]"."\n";
		$header = $header.'resource dev "Developers" {'."\n";
		
		$header = $header.'    resource u "Unassigned" {}';
  
		foreach($resources as $resource)
		{
			$calendar = $resource->Calendar;
			$header = $header.'    resource '.$resource->Username.' "'.$resource->Name.'" {'."\n";
			
			foreach($calendar as $holiday)
				$header = $header.'      leaves annual '.$holiday."\n"; 

			
			$header = $header.'    }'."\n";
		}
		$header = $header.'}'."\n";

		
		return $header;
	}
	function DependsHeader($task)
	{
		$header = "";
		if(count($task->predecessors) > 0)
		{
			
			$del = "";
			$count = count(explode(".",$task->ExtId));
			$pre = "";;
			while($count--)
				$pre = $pre."!";
			
			foreach($task->predecessors as $stask)
			{
				//depends !!!t1.t1a1.t1a1a1,!!!t1.t1a2.t1a2a1 
				//echo $stask->ExtId." ";
				
				$post = "";
				$codes = explode(".",$stask->ExtId);
				$lastcode = "";
				for($i=0;$i<count($codes);$i++)
				{
					if($i == 0)
					{
						$lastcode = "t".$codes[$i];
						$post = $lastcode;
					}
					else
					{
						$lastcode = $lastcode."a".$codes[$i];
						$post  =  $post.".".$lastcode;
					}
				}
				$header = $header.$del.$pre.$post;
				$del=",";
				//echo $stask->ExtId." ";
				//echo "[".$pre.$post."]";
				//echo EOL;
			}
			return $header;
		}
		else
			return null;
		//echo $header.EOL;
	}
	function FlushTask($task)
	{
		$header = "";
		$spaces = "";
		for($i=0;$i<$task->Level-1;$i++)
			$spaces = $spaces."     ";
		
		$tag = str_replace(".", "a", $task->ExtId);
		$header = $header.$spaces.'task t'.$tag.' "'.$task->Summary.'" {'."\n";
		
		
		$header = $header.$spaces."   complete ".round($task->Progress,0)."\n";
		$dheader = $this->DependsHeader($task);
		
		if($dheader != null)
			$header = $header.$spaces."   depends ".$dheader."\n";
		
		
		$sdate = $task->StartConstraintDate;
		if($sdate != null)
			$header = $header.$spaces."   start ".$sdate."\n";
		
		if($task->IsParent == 0)
		{
			
			$header = $header.$spaces.'   Jira "'.$task->Jira.'"'."\n";
			$remffort  = $task->Duration - $task->TimeSpent;
			if($remffort > 0)
			{
				$header = $header.$spaces."   effort ".$remffort."d"."\n";
				if($task->Resource != null)
					$header = $header.$spaces."   allocate ".$task->Resource."\n";
				else
					$header = $header.$spaces."   allocate u"."\n";
			}
		}
		
		foreach($task->children as $stask)
			$header = $header.$this->FlushTask($stask);
		
		$header = $header.$spaces.'}'."\n";
		return $header;
		
	}
	function FlushTasks($tasks)
	{
		$header = "";
		foreach($tasks as $task)
		{
			$header = $header.$this->FlushTask($task);
		}
		return $header;
	}
	function FlushReportHeader()
	{
		
		$header =
		# Now the project has been specified completely. Stopping here would
		# result in a valid TaskJuggler file that could be processed and
		# scheduled. But no reports would be generated to visualize the
		# results.

		"navigator navbar {
		  hidereport @none
		}

		macro TaskTip [
		  tooltip istask() -8<-
			'''Start: ''' <-query attribute='start'->
			'''End: ''' <-query attribute='end'->
			----
			'''Resources:'''

			<-query attribute='resources'->
			----
			'''Precursors: '''

			<-query attribute='precursors'->
			----
			'''Followers: '''

			<-query attribute='followers'->
			->8-
		]

		textreport frame \"\" {
		  header -8<-
			== Accounting Software Project ==
			<[navigator id=\"navbar\"]>
		  ->8-
		  footer \"----\"
		  textreport index \"Overview\" {
			formats html
			center '<[report id=\"overview\"]>'
		  }

		  textreport development \"Development\" {
			formats html
			center '<[report id=\"development\"]>'
		  }

		 #textreport \"Deliveries\" {
		 #   formats html
		 #   center '<[report id=\"deliveries\"]>'
		 # }

		  textreport \"ContactList\" {
			formats html
			title \"Contact List\"
			center '<[report id=\"contactList\"]>'
		  }
		  textreport \"ResourceGraph\" {
			formats html
			title \"Resource Graph\"
			center '<[report id=\"resourceGraph\"]>'
		  }
		}

		# A traditional Gantt chart with a project overview.
		taskreport overview \"\" {
		  header -8<-


		  ->8-
		  columns bsi { title 'WBS' },
				  name, start, end, effort,
				  resources, complete,Jira, chart { \${TaskTip} }
		  # For this report we like to have the abbreviated weekday in front
		  # of the date. %a is the tag for this.
		  timeformat \"%a %Y-%m-%d\"
		  loadunit days
		  hideresource @all
		  caption 'All effort values are in man days.'

		  footer -8<-
			
		  ->8-
		}

		# Macro to set the background color of a cell according to the alert
		# level of the task.
		macro AlertColor [
		  cellcolor plan.alert = 0 \"#00D000\" # green
		  cellcolor plan.alert = 1 \"#D0D000\" # yellow
		  cellcolor plan.alert = 2 \"#D00000\" # red
		]



		# A list of tasks showing the resources assigned to each task.
		taskreport development \"\" {
		  headline \"Development - Resource Allocation Report\"
		  columns bsi { title 'WBS' }, name, start, end, effort { title \"Work\" },
				  duration, chart { \${TaskTip} scale day width 500 }
		  timeformat \"%Y-%m-%d\"
		  hideresource ~(isleaf() & isleaf_())
		  sortresources name.up
		}

		# A list of all tasks with the percentage completed for each task
		#taskreport deliveries \"\" {
		#  headline \"Project Deliverables\"
		#  columns bsi { title 'WBS' }, name, start, end, note { width 150 }, complete,
		#          chart { \${TaskTip} }
		#  taskroot AcSo.deliveries
		#  hideresource @all
		#  scenarios plan, delayed
		#}
		# A list of all employees with their contact details.
		resourcereport contactList \"\" {
		  headline \"Contact list and duty plan\"
		  columns name,
				  email { celltext 1 \"[mailto:<-email-> <-email->]\" },
				  chart { scale day }
		  hideresource ~isleaf()
		  sortresources name.up
		  hidetask @all
		}

		# A graph showing resource allocation. It identifies whether each
		# resource is under- or over-allocated for.
		resourcereport resourceGraph \"\" {
		  headline \"Resource Allocation Graph\"
		  columns no, name, effort, rate, weekly { \${TaskTip} }
		  loadunit shortauto
		  # We only like to show leaf tasks for leaf resources.
		  hidetask ~(isleaf() & isleaf_())
		  sorttasks plan.start.up
		}";

		return $header;
	}
	function __construct($project,$tree,$resources)
	{
		//$fp = fopen('project.tjp', 'w');
		
		$pheader = $this->FlushProjectHeader($project);
		//fwrite($fp, $pheader);
		$lheader = $this->FlushLeavesHeader($project);
		//fwrite($fp, $lheader);
		$rheader = $this->FlushResourceHeader($resources);
		//fwrite($fp, $rheader);
		$fheader = $this->FlushTasks($tree);
		//fwrite($fp, $fheader);
		$rpheader = $this->FlushReportHeader();
		//fwrite($fp, $rpheader);
		//fclose($fp);
		$this->output = $pheader.$lheader.$rheader.$fheader.$rpheader;
	}
	function Save($filename)
	{
		$fp = fopen($filename, 'w');
		fwrite($fp, $this->output);
		fclose($fp);
		$this->filename = $filename;
	}
	function ReadOutput()
	{
		
		$dom = new DOMDocument();
		libxml_use_internal_errors(true);
		$html = file_get_contents(TJ_OUTPUT_FOLDER."//Overview.html");
		// load html
		$dom->loadHTML($html);
		libxml_use_internal_errors(false);
		$xpath = new DOMXPath($dom);

		//this will gives you all td with class name is jobs.
		$my_xpath_query = "//table//td[contains(@class, 'tj_table')]";
		$result_rows = $xpath->query($my_xpath_query);

		$lvalue = "";
		$extid = "";
		$start = "";
		$end = "";
		$tasks = array();
		foreach ($result_rows as $result_object){
			
			$value = $result_object->nodeValue;
			if((string)$value == null)
			{
				$extid = $lvalue;
			}
			$lvalue = $value;
			$dates = explode(" ",$value);
			if( count($dates) > 1)
			{
				if(strlen($dates[0])==3)
				{
					if($start == "")
						$start = $dates[1];
					else
						$end = $dates[1];
				}
			}
			if($end != "")
			{
				$obj = new Obj();
				$obj->ExtId = $extid;
				$obj->Start = $start;
				$obj->End = $end;
				//echo $extid." ".$start." ".$end.EOL;
				$start = "";
				$end = "";
				$tasks[] = $obj;
			}
		}
		return $tasks;
	}
	function Execute($showoutput=0)
	{
		global $project_folder;
		if($this->filename != null)
		{
			//." 2>&1"
			$cmd = "tj3 -o ".TJ_OUTPUT_FOLDER."  ".$this->filename." 2>&1";
			if($showoutput == 0)
			ob_start();
			exec($cmd,$result);
			if($showoutput == 0)
				ob_end_clean();
			//foreach($result as $line)
			//	echo $line.EOL;
			//print_r($result)."--".EOL;
			$pos1 = strpos($result[0], 'Error');
			//echo $pos1.EOL;
			if ($pos1 != false)
			{
				return  $result[0];
			}
			//$result
			//Error: Task t1.t1a2 (2017-08-17-00:00-+0000) must start after end (2017-08-23-17:00-+0000) of task t1.t1a1.t1a1a2. This condition could not be met. TaskJuggler v3.6.0
			
		}
		return null;
	}
}


?>