<!DOCTYPE html>
<html lang="en" class="no-js">
	<!-- Head -->
	<head>
		<!-- Meta data -->
		<meta charset="utf-8">
		<title>jsGantt Improved</title>
		<meta name="description" content="FREE javascript gantt - jsGantt Improved HTML, CSS and AJAX only">
		<meta name="keywords" content="jsgantt-improved free javascript gantt-chart html css ajax">
		<meta name="viewport" content="width=device-width,initial-scale=1">
	
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" rel="stylesheet"  crossorigin="anonymous" />
		<!-- Font Awesome -->
		<!-- Google's Code Prettify -->
		<!-- Google Fonts -->
		<!-- Internal resources -->
		<!-- jsGanttImproved App -->
		<link href="jsgantt.css" rel="stylesheet" type="text/css"/>
		<script src="jsgantt.js" type="text/javascript"></script>
	</head>
	<style>
		.center {
			margin: auto;
			width: 100%;
			border: 3px solid green;
			padding: 10px;
		}
	</style>
	<body >
        <div class="center" id="external-Gantt1"></div>
		<script type="text/javascript">
			var g = new JSGantt.GanttChart(document.getElementById('external-Gantt1'), 'day');
			if (g.getDivId() != null) 
			{
				g.setShowRes(1);
				g.setCaptionType('Caption');  // Set to Show Caption (None,Caption,Resource,Duration,Complete)
				g.setShowTaskInfoLink(1); // Show link in tool tip (0/1)
				g.setDayMajorDateDisplayFormat('dd mon');
				g.setDateTaskDisplayFormat('yyyy-mm-dd');
				// Use the XML file parser
				JSGantt.parseXML('../projects/project1/jsgantt.xml?v=1', g)
				//JSGantt.parseXML('project.xml?v=1', g)
				g.Draw();
			} 
			else 
			{
				alert("Error, unable to create Gantt Chart");
			}
		</script>
		<!-- Footer -->
		<div style="font-size:10px;" class="footer text-center">
			<p>© Copyright 2013-2017 jsGanttImproved<br />
			Designed with <a href="https://v4-alpha.getbootstrap.com" target="_blank">Bootstrap</a> and <a href="http://fontawesome.io" target="_blank">Font Awesome</a></p>
		</div>
	</body>
</html>
