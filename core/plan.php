<?php
define('POD','POD');

class Plan 
{
	private $plan;
	function __construct($type)
	{
		switch($type)
		{
			case 'POD':
				$this->plan = new POD(POD_FILE);
				break;
			default:
				echo $type." is not supported".EOL;
				exit();
		}
	}
	public function __get($name)
	{
		switch($name)
		{
			case 'TasksTree': // Tasks as tree (with children)
				return $this->plan->TasksTree;
				break;
			case 'Tasks': // Tasks array with task id as index
				return $this->plan->Tasks;
				break;
			case 'TaskList': // Tasks array with extid as index
				return $this->plan->TaskList;
				break;
			case 'Dump': // To display tasks data structure
				return $this->plan->Dump;
				break;
			case 'Query': // Jira quesry string
				return $this->plan->Query;
				break;
			case 'JiraUrl': // text string
				return $this->plan->JiraUrl;
				break;
			case 'Update': // Update must be called when data structure is changed
				return $this->plan->Update;
				break;
			case 'Project': // Project Object
				return $this->plan->Project;
				break;
			case 'Resources':  // Resources Object
				return $this->plan->Resources;
				break;
			case 'ProjectEnd':   // Date like 2017-02-01 
				return $this->plan->ProjectEnd;
			case 'Calendar':  // Calendar object
				return $this->plan->Calendar;
				break;
			default:
				trace("Plan does not have ".$name." property",'ERROR');
				exit();
			
		}
	}
}

?>