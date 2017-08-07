<?php


$project_folder = "projects\\".$project_name;

if (!is_dir($project_folder)) {
   trace($project_name." ".' Does not exist','MSG');
   exit();
}

define('POD_FILE',$project_folder."\\Project.pod");
define('FILTER_FILE',$project_folder."\\filter");
define('TJ_FILE',$project_folder."\\Project.tjp");
define('TJ_OUTPUT_FOLDER',$project_folder."\\tj");
define('JS_GANTT_FILE',$project_folder."\\jsgantt.xml");
define('LOG_FOLDER',$project_folder."\\logs\\");
//define('GANTT_DATA_FILE',$folder."\\gantt");
//define('ARCHIVE_FOLDER',$folder."\\archive");

// Create Project structure from
require_once('cparams.php');
require_once($project_folder.'\\settings.php');
require_once('pod.php');
require_once('jirarest.php');
require_once('jira.php');
require_once('filter.php');
require_once('tj.php');
require_once('jsgantt.php');
require_once('history.php');
require_once('plan.php');

//require_once('structure.php');
//require_once('filter.php');
//require_once('project.php');
//require_once('gan.php');
//require_once('jsgantt.php');
//require_once('graph.php');
//require_once('project_settings.php');

//ERRORS
define('ERROR','error');
define('WARN','warn');
//define("WEBLINK",$JIRA_URL.'/browse/');
//define('JIRA_URL',$JIRA_URL);
//define('QUERY',$QUERY);
define('DEPENDENCY_BLOCKS',1);
define('DEPENDENCY_DEPENDS',0);



date_default_timezone_set('Asia/Karachi');

class Obj{
}

function dlog($log)
{
	$traces = debug_backtrace();
	
	$trace = $traces[0];
	$line  = $trace['line'];
	
	$trace = $traces[1];
	//print_r($trace);
	echo basename($trace['file'])."-->";
	echo $trace['class'].'::';
	echo $trace['function'].'()';
	//echo '(';
	//$del = '';
	//foreach($trace['args'] as $arg)
	//{
	//	echo $del;$del=',';
	//	echo $arg;
	//}
	//echo ")";
	
	echo "  #".$line." ".$log.EOL;
}

function trace($log,$type='LOG')
{
	if($type == 'ERROR')
	{
		if(isset(debug_backtrace()[1]['class']))
			echo "ERROR::".debug_backtrace()[1]['class']."::".debug_backtrace()[1]['function']."::".$log.EOL;
		else
			echo "ERROR::"."::".$log."\n";
	}
	else if($type == 'WARN')
	{
		echo "WARN::".debug_backtrace()[1]['class']."::".debug_backtrace()[1]['function']."::".$log.EOL;
	}
	else if($type == 'MSG')
	{
		echo $log.EOL;
	}
	else if($type == 'LOG')
		echo 'LOG '.$log.EOL;
	else
		echo $type."::".$log.EOL;
}
function HtmlHeader($title)
{
	echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>'. $title.'</title></head><body>';
}
function HtmlFooter()
{
	echo '</body></html>';
}
function GetToday($format)
{
	//return "2017-08-12";
	return Date($format);
}
?>
