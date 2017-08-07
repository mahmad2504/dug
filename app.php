<?php

require_once('core\\cparams.php');
$filename = 'core\\cmd'.strtolower($cmd).".php";

if(file_exists($filename))
	include $filename;
else
{
	echo "Command '".$cmd."' is not supported";
	exit();
}
switch(strtolower($cmd))
{
	case 'sync':
		HtmlHeader('Sync with Jira');
		new Sync($rebuild);
		HtmlFooter();
		break;
	case 'gantt':
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header('Location: index.php?project='.$project_name);
		break;
}

?>