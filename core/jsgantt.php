<?php
require_once('common.php');	

class JSGantt 
{
	private $tasks;
	function __construct($tasks)
	{
		$this->tasks = $tasks;
	}
	function TaskJSGanttXML($jiraurl,$xml, $task,$pid)
	{
		$node = $xml->addChild("task");
		$node->addChild("pID",$task->Id);
		$node->addChild("pName",$task->Summary);
		//$node->addChild("pStart",date("m/d/Y",strtotime($task->Start)));
		//$node->addChild("pEnd",date("m/d/Y",strtotime($task->End)));
		$node->addChild("pStart",$task->Start);
		$node->addChild("pEnd",$task->End);
		$node->addChild("pColor");
		if($task->Jira != null)
			$node->addChild("pLink",$jiraurl."/browse/".$task->Jira);
		else
			$node->addChild("pLink");
		$node->addChild("pMile",0);
		$node->addChild("pRes",$task->Resource);
		$node->addChild("pComp",$task->Progress);
		$node->addChild("pGroup",$task->IsParent);
		$node->addChild("pParent",$pid);
		$node->addChild("pCaption",$task->Jira);
		$node->addChild("pDuration",$task->Duration);
		$node->addChild("pDeadline",$task->Deadline);
		$node->addChild("pDepend",$task->DependenciesIds);
		
		if($task->IsParent == 1)
		{
			if($task->Status == 'RESOLVED')
				$node->addChild("pOpen",0);
			else
				$node->addChild("pOpen",1);
		}
		if($task->IsParent == 0)
		{
			if($task->Status == 'RESOLVED')
				$node->addChild("pClass",'gtaskcomplete');
			else if($task->Status == 'OPEN')
				$node->addChild("pClass",'gtaskopen');
			else
			{
				if($task->Progress == 100)
				{
					if($task->IsDelayed)
						$node->addChild("pClass",'gtaskred');
					else
						$node->addChild("pClass",'gtaskyellow');
				}
				else
					$node->addChild("pClass",'gtaskgreen');
			}
		}
		if($task->Status == 'RESOLVED')
			$node->addChild("pRowColor",'lightgrey');
		else if($task->Status == 'OPEN')
			$node->addChild("pRowColor",'black');
		else
			$node->addChild("pRowColor",'black');
		
		if($task->Deadline != null)
		{
			if($task->Status == 'RESOLVED')
			{}
			else if((strtotime($task->End)) < (strtotime($task->Deadline)))
				$node->addChild("pDeadlineColor",'limegreen');
			else
				$node->addChild("pDeadlineColor",'red');
		}
		
		foreach($task->children as $stask)
		{
			$ntid = $this->TaskJSGanttXML($jiraurl,$xml,$stask,$task->Id);
		}
	}
	function Save($filename,$jiraurl,$projectend=null,$calendar=null)
	{
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<project/>', null, false);
		$xml['xmlns:xsi'] = "http://www.w3.org/2001/XMLSchema-instance";

		foreach($this->tasks as $task)
		{
			$node = $xml->addChild("task");
			$node->addChild("pID",$task->Id);
			$node->addChild("pName",$task->Summary);
			$node->addChild("pStart",$task->Start);
			$node->addChild("pEnd",$task->End);
			$node->addChild("pColor");
			if($task->Jira != null)
				$node->addChild("pLink",$jiraurl."/browse/".$task->Jira);
			else
				$node->addChild("pLink");
			$node->addChild("pMile",0);
			$node->addChild("pRes",$task->Resource);
			$node->addChild("pComp",$task->Progress);
			$node->addChild("pGroup",$task->IsParent);
			$node->addChild("pParent",0);
			$node->addChild("pCaption",$task->Jira);
			$node->addChild("pDuration",$task->Duration);
			$node->addChild("pDeadline",$task->Deadline);
			$node->addChild("pTimeSpent",$task->TimeSpent);
			$node->addChild("pDepend",$task->DependenciesIds);
			if($projectend != null)
				$node->addChild("pProjectEnd",$projectend);
			if(  strtotime(GetToday("Y-m-d")) != strtotime(Date("Y-m-d")))
				$node->addChild("pToDay",GetToday("Y-m-d"));
			//else
			//	$node->addChild("pToDay");
			
			if($task->IsParent == 1)
			{
				if($task->Status == 'RESOLVED')
					$node->addChild("pOpen",0);
				else
					$node->addChild("pOpen",1);
			}
			if($task->IsParent == 0)
			{
				if($task->Status == 'RESOLVED')
				{}
				else if($task->Status == 'OPEN')
					$node->addChild("pClass",'gtaskopen');
				else
				{
					if($task->Progress == 100)
						$node->addChild("pClass",'gtaskred');
					else
						$node->addChild("pClass",'gtaskgreen');
				}
			}
			if($task->Status == 'RESOLVED')
				$node->addChild("pRowColor",'lightgrey');
			else if($task->Status == 'OPEN')
				$node->addChild("pRowColor",'black');
			else
				$node->addChild("pRowColor",'black');
			
			if($task->Deadline != null)
			{
				if($task->Status == 'RESOLVED')
					$node->addChild("pDeadlineColor");
				else if((strtotime($task->End)) < (strtotime($task->Deadline)))
					$node->addChild("pDeadlineColor",'limegreen');
				else
					$node->addChild("pDeadlineColor",'red');
			}
			if($projectend != null)
			{
				$node->addChild("pProjectEnd",$projectend);
			}
			if($calendar != null)
			{
				$node->addChild("pCalendar",$calendar);
			}
			foreach($task->children as $stask)
			{
				$ntid = $this->TaskJSGanttXML($jiraurl,$xml,$stask,$task->Id);
			}
		}
		$data = $xml->asXML();
		file_put_contents($filename, $data);
	}
}
?>