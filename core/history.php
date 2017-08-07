<?php
require_once('common.php');	

class History
{
	private $folder;
	function __construct($folder)
	{
		$this->folder = $folder;
	}
	public function Add($date,$tasks)
	{
		$date = date("Y-m-d",strtotime($date));
		$filename = $this->folder."//".$date.".txt";
		$file = fopen($filename,"w");
		$data = array();
		foreach($tasks as $task)
		{
			$obj =  new Obj();
			$obj->Id = $task->Id;
			$obj->Jira = $task->Jira;
			$obj->Status = $task->Status;
			$obj->Duration = $task->Duration;
			$obj->TimeSpent = $task->TimeSpent;
			$data[] = $obj;
		}
		$str =  Serialize($data);
		fwrite($file,$str);
		fclose($file);
	}
	private function ReadDirectory($directory)
	{
		$files = array();
		$dir = opendir($directory); // open the cwd..also do an err check.
		//echo $directory.EOL;
		while(false != ($file = readdir($dir))) 
		{
			//echo $file.EOL;
			if(($file != ".") and ($file != "..")) 
			{
				$files[] = $directory.$file; // put in array.
			}  
		}
		//echo count($files).EOL;
		natsort($files); // sort.
		return $files;
	}
	public function Read($Jira,$field)
	{
		$files = $this->ReadDirectory($this->folder);
		$returndata =  array();
		foreach($files as $filename)
		{
			$sz = filesize($filename);
			$file = fopen($filename,"r");
			$data = fread ( $file , $sz );
			$data = unserialize($data);
			foreach($data as $task)
			{
				if($task->Jira == $Jira)
				{
					$returndata[] = $task->$field;
					break;
				}
			}
		}
		return $returndata;
		
		/*$date = date("Y-m-d",strtotime($date));
		$filename = $this->folder."//".$date.".txt";
		if(file_exists($filename ))
		{
			$sz = filesize($filename);
			$file = fopen($this->folder."//".$date.".txt","r");
			$data = fread ( $file , $sz );
			$data = unserialize($data);
			foreach($data as $task)
			{
				echo $task->Jira.EOL;
			}
		}*/
		
	}
} 
?>