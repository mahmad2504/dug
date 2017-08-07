<?php
require_once('core/pparse.php');
$project_name = 'none';
if(isset($path))
{
	if(count($path) != 4)
	{
		echo "URL Errors";
		return;
	}
	$cmd = $path[2];
	$project_name = $path[3];
}

if(!isset($cached))
	$cached = 0;
else
	$cached = 1;

if(!isset($rebuild))
	$rebuild = 0;
else
	$rebuild = 1;


if(!isset($date))
	$date = date('Y-M-d');




?>
