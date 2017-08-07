<?php
require_once('common.php');
// This Class will update TimeSpent  from Jira (Task Remaining Duration will also be updated)
// This Class will also update task data back to Jira
class Sync
{
	private $pod;
	private $plan;
	function __construct($rebuild)
	{
		global $input;
		if(!isset($input))
			$input = 'POD';
		$plan = new Plan(strtoupper($input));
		//$pod = new Pod(POD_FILE);
		
		$tasks =  $plan->TaskList;
		//print_r($tasks);
		echo '<p1 style="background-color: yellow;">State after read POC file</p>';
		$plan->Dump(1);
		$this->plan = $plan;
		
		//$filter = new Filter(FILTER_FILE,$plan->Query,$plan->JiraUrl,$rebuild);
		$cached = $this->SyncFromJira($rebuild);
		$plan->Update;
		echo '<p1 style="background-color: yellow;">State after Update from Jira</p>';
		$plan->Dump(0);
		
		$tj = new Tj($plan->Project,$plan->TasksTree,$plan->Resources);
		$tj->Save(TJ_FILE);
		$error = $tj->Execute();
		if($error != null)
		{
			echo $error.EOL;
			echo "Correct the Plan first";
			exit();
		}
		$data = $tj->ReadOutput();
		foreach($data as $record)
		{
			$tasks[$record->ExtId]->Start = $record->Start;
			$tasks[$record->ExtId]->End = $record->End;
		}
		echo '<p1 style="background-color: yellow;">State after running schedular</p>';
		$plan->Dump(0);
		if($cached == 0)
			$this->SyncToJira();
		
		
		$tasks =  $plan->TasksTree;
		$jsgantt = new JSGantt($tasks);
		$calendar = implode(",",$plan->Calendar);
		$jsgantt->Save(JS_GANTT_FILE,$plan->JiraUrl,$plan->ProjectEnd,$calendar);
		
		//LOG_FOLDER
		$history = new History(LOG_FOLDER);
		
		$today = GetToday("Y-m-d");
		if($plan->ProjectEnd == null)
			$history->Add($today,$plan->Tasks);
		else
		{
			if(strtotime($today)>strtotime($plan->ProjectEnd))
				$history->Add($plan->ProjectEnd,$plan->Tasks);
		}
	}
	/*

	private function UpdateTimeSpend($task)
	{
		if($task->IsParent == 0)
			return $task->TimeSpent;
		$timespent = 0;
		foreach($task->children as $stask)
		{
			$timespent = $timespent + $this->UpdateTimeSpend($stask);
		}
		$task->TimeSpent = $timespent;
		return $timespent;
	}*/
	private function SyncToJira()
	{
		$plan = $this->plan;
		$tasks = $plan->Tasks;
		foreach($tasks as $task)
		{
			if($task->Jira != null)
			{
				if($task->IsParent == 1)
				{
					$result = Jirarest::UpdateTask($task->Jira,$task->Summary,null,$task->Deadline,0,null,$task->ExtId);
					if($result != null)
					{
						print_r($result);
						return -1;
					}
				}
				else
				{
					if($task->DurationType == 'E')
						$result = Jirarest::UpdateTask($task->Jira,$task->Summary,null,$task->Deadline,'',$task->Resource,$task->ExtId);
					else
					{
						$result = Jirarest::UpdateTask($task->Jira,$task->Summary,null,$task->Deadline,$task->Duration,$task->Resource,$task->ExtId);
					}
					if($result != null)
					{
						print_r($result);
						return -1;
					}
				}
			}
		}
	}
	private function SyncFromJira($rebuild)
	{
		$plan = $this->plan;
		$filter = new Filter(FILTER_FILE,$plan->Query,$plan->JiraUrl,$rebuild);
		$jtasks = $filter->GetData();
		$tasks = $plan->Tasks;
		foreach($tasks as $task)
		{
			if($task->Jira != null)
			{
				$key = $task->Jira;
				//echo $jtasks->$key;
				if( isset($jtasks->$key))
				{
					//Update timespent from Jira if it is non summary task
					$jtask = $jtasks->$key;
					if($task->IsSummaryEstimated) // If summary is not accurate in plan then get the jira summary 
					{
						$tasks[$task->Id]->Summary = $jtask->summary;
					}
					if($task->IsParent == 0)
					{
						$timespent  = $jtask->timespent/(60*60*8); // In days
						//echo $task->Duration." ".$task->TimeSpent.EOL;
						$tasks[$task->Id]->TimeSpent = $timespent;
						//$task->TimeSpent = $timespent;
						//echo $key." ".$timespent." ".$task->TimeSpent.EOL;
						if($task->DurationType == ESTIMATED) // if duration is estimated and in jira there is some estimates given , give that priority.
						{
							$jdur = $jtask->timeoriginalestimate;
							if($jdur > 0)
								$tasks[$task->Id]->Duration = $jdur/(60*60*8); // In days
							else
								$tasks[$task->Id]->DurationType = FIXED;// Due to check in update to jira we have set it to update estimated duration in Jira.
						}
						$tasks[$task->Id]->Status = $jtask->status;
						if($jtask->assignee != null)
						{
							if($tasks[$task->Id]->Resource == null)
								$tasks[$task->Id]->Resource = $jtask->assignee;
						}
					}
				}
				else
				{
					echo $task->Jira."  data missing in filter data".EOL;
				}
			}
		}
		return $filter->IsCached;

	}
}
?>



