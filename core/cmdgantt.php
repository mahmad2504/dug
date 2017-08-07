<?php
require_once('common.php');
class Gantt
{
	function __construct($filename)
	{
		$jsgantt = new JsGantt($filename);
		echo $filename.EOL;
		
		
		
	}
}

?>